<?php

namespace App\Http\Controllers;

use App\Jobs\PosaljiObavijestPreporuke;
use App\Models\Kultura;
use App\Models\Preporuka;
use App\Models\Pristup;
use App\Models\SzbSredstvo;
use App\Models\User;
use App\Support\Preporuke;
use App\Support\ProvjeraSzb;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Preporuke: agronom šalje jednom ili više gospodarstava, gospodarstvo odgovara, a evidenciju upisuje tek kad obavi posao. */
class PreporukaController extends Controller
{
    private const DNEVNI_LIMIT = 30;

    /** Pravi prijavljeni korisnik, i dok savjetnik radi u klijentovom gospodarstvu. */
    private function ja(Request $request): User
    {
        return $request->attributes->get('savjetnik') ?? $request->user();
    }

    /** Gospodarstva koja su ovom savjetniku dala pristup: id => naziv. */
    private function klijenti(User $ja): Collection
    {
        return Pristup::aktivni()->with('vlasnik')->where('savjetnik_id', $ja->id)->get()->filter(fn ($p) => $p->vlasnik)
            ->mapWithKeys(fn ($p) => [$p->vlasnik_id => $p->vlasnik->naziv_gospodarstva ?: $p->vlasnik->name])->sort();
    }

    private function aktivneKulture(Collection $vlasnici)
    {
        return Kultura::with('parcela')->whereIn('kulture.user_id', $vlasnici->all())->uGodini(date('Y'))->whereNull('kulture.zavrsena_at')->get();
    }

    // ---- agronom ----

    public function savjetnik(Request $request): View
    {
        $ja = $this->ja($request);
        $klijenti = $this->klijenti($ja);

        $ciljevi = $this->aktivneKulture($klijenti->keys())->map(fn ($k) => [
            'id' => $k->id, 'vlasnik_id' => $k->user_id, 'gospodarstvo' => $klijenti[$k->user_id], 'kultura' => $k->naziv,
            'parcela' => Preporuke::oznakaParcele($k->parcela), 'ha' => (float) $k->posadjena_povrsina_ha,
        ])->sortBy(['gospodarstvo', 'parcela'])->values();

        $poslane = Preporuka::with('vlasnik')->where('savjetnik_id', $ja->id)->latest('id')->limit(200)->get();

        // otvaranjem stranice odgovori su pročitani
        Preporuka::where('savjetnik_id', $ja->id)->whereNotNull('odgovoreno_at')->whereNull('savjetnik_vidio_at')->update(['savjetnik_vidio_at' => now()]);

        return view('preporuke.savjetnik', [
            'klijenti' => $klijenti, 'ciljevi' => $ciljevi, 'paketi' => $poslane->groupBy('paket'),
            'sredstva' => SzbSredstvo::where('aktivno', true)->orderBy('naziv')->pluck('naziv'),
        ]);
    }

    public function posalji(Request $request)
    {
        $ja = $this->ja($request);
        $klijenti = $this->klijenti($ja);
        abort_if($klijenti->isEmpty(), 403);

        $d = $request->validate([
            'vrsta' => 'required|in:' . implode(',', array_keys(Preporuka::VRSTE)),
            'kultura_ids' => 'array|required_unless:vrsta,poruka',
            'kultura_ids.*' => 'integer',
            'vlasnik_ids' => 'array|required_if:vrsta,poruka',
            'vlasnik_ids.*' => 'integer',
            'sredstvo' => 'nullable|required_unless:vrsta,poruka|string|max:200',
            'doza' => 'nullable|numeric|min:0|max:99999',
            'jedinica' => 'nullable|in:' . implode(',', Preporuka::JEDINICE),
            'rok' => 'nullable|date|after_or_equal:today',
            'napomena' => 'nullable|required_if:vrsta,poruka|string|max:1000',
        ], [
            'kultura_ids.required_unless' => 'Odaberite barem jednu parcelu.',
            'vlasnik_ids.required_if' => 'Odaberite barem jedno gospodarstvo.',
            'sredstvo.required_unless' => 'Upišite sredstvo ili gnojivo.',
            'napomena.required_if' => 'Upišite poruku.',
        ], ['rok' => 'rok', 'doza' => 'doza', 'napomena' => 'napomena']);

        $redovi = $this->pripremi($d, $klijenti);
        if ($redovi->isEmpty()) {
            return back()->withInput()->withErrors(['kultura_ids' => 'Nijedna odabrana parcela nije dostupna. Provjerite odabir.']);
        }

        // prvi korak je uvijek pregled s provjerom: slanje tek nakon potvrde
        if (! $request->boolean('potvrda')) {
            return view('preporuke.pregled', ['d' => $d, 'redovi' => $redovi, 'klijenti' => $klijenti]);
        }

        $kljuc = 'preporuke:' . $ja->id;
        if (RateLimiter::tooManyAttempts($kljuc, self::DNEVNI_LIMIT)) {
            return redirect()->route('klijenti.preporuke')->with('status', 'Danas ste poslali najviše preporuka što je moguće (' . self::DNEVNI_LIMIT . ' slanja). Nastavite sutra.');
        }

        $paket = (string) Str::uuid();
        $spremljene = DB::transaction(fn () => $redovi->map(fn ($r) => Preporuka::create($r + [
            'paket' => $paket, 'savjetnik_id' => $ja->id, 'savjetnik_ime' => $ja->name, 'vrsta' => $d['vrsta'],
            'sredstvo' => $d['vrsta'] === 'poruka' ? null : trim($d['sredstvo']),
            'doza' => $d['vrsta'] === 'poruka' ? null : ($d['doza'] ?? null),
            'jedinica' => $d['vrsta'] !== 'poruka' && isset($d['doza']) ? ($d['jedinica'] ?? null) : null,
            'rok' => $d['rok'] ?? null, 'napomena' => $d['napomena'] ?? null,
        ])));
        RateLimiter::hit($kljuc, 86400);

        // jedna obavijest po gospodarstvu, koliko god parcela preporuka obuhvaćala
        foreach ($spremljene->groupBy('vlasnik_id') as $vlasnikId => $svoje) {
            $prva = $svoje->first();
            $vrsta = Preporuka::VRSTE[$d['vrsta']];
            PosaljiObavijestPreporuke::dispatch(
                (int) $vlasnikId,
                $d['vrsta'] === 'poruka' ? 'Poruka od savjetnika ' . Str::limit($ja->name, 60) : 'Preporuka: ' . mb_strtolower($vrsta) . ' (' . Str::limit($ja->name, 60) . ')',
                $d['vrsta'] === 'poruka' ? 'Nova poruka od savjetnika' : 'Nova preporuka: ' . mb_strtolower($vrsta),
                Str::limit($ja->name, 60) . ' vam šalje ' . ($d['vrsta'] === 'poruka' ? 'poruku' : 'preporuku') . '. Sami odlučujete što ćete napraviti.',
                $d['vrsta'] === 'poruka'
                    ? [Str::limit((string) $prva->napomena, 300)]
                    : $svoje->take(5)->map(fn ($p) => $p->sazetak())->values()->all(),
                route('preporuke.index'),
                'Pogledaj i odgovori',
                $ja->email,
            );
        }

        $gospodarstava = $spremljene->pluck('vlasnik_id')->unique()->count();

        return redirect()->route('klijenti.preporuke')->with('status', 'Poslano gospodarstava: ' . $gospodarstava . '.');
    }

    /** Pripremi retke za slanje. Primatelji se ponovno provjeravaju jer odabir stiže iz preglednika. */
    private function pripremi(array $d, Collection $klijenti): Collection
    {
        if ($d['vrsta'] === 'poruka') {
            return collect($d['vlasnik_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->filter(fn ($id) => $klijenti->has($id))
                ->map(fn ($id) => ['vlasnik_id' => $id, 'gospodarstvo' => $klijenti[$id]])->values();
        }

        $ids = collect($d['kultura_ids'] ?? [])->map(fn ($id) => (int) $id);
        $korisnici = User::whereIn('id', $klijenti->keys())->get()->keyBy('id');

        return $this->aktivneKulture($klijenti->keys())->filter(fn ($k) => $ids->contains($k->id))
            ->sortBy(fn ($k) => $klijenti[$k->user_id])->values()
            ->map(function ($k) use ($d, $klijenti, $korisnici) {
                $provjera = null;
                if ($d['vrsta'] === 'prskanje') {
                    $provjera = SzbSredstvo::poNazivu($d['sredstvo'])
                        ? (ProvjeraSzb::provjeri($korisnici[$k->user_id], $k->id, $d['sredstvo'], $d['rok'] ?? null)['stavke'] ?? null)
                        : [['razina' => 'upozorenje', 'tekst' => 'Sredstvo „' . $d['sredstvo'] . '” nije pronađeno u registru FIS-a.']];
                }

                return ['vlasnik_id' => $k->user_id, 'gospodarstvo' => $klijenti[$k->user_id], 'kultura_id' => $k->id, 'kultura_naziv' => $k->naziv,
                    'parcela_oznaka' => Preporuke::oznakaParcele($k->parcela), 'provjera' => $provjera];
            });
    }

    /** Agronom odgovara na pitanje; preporuka se vraća gospodarstvu na odluku. */
    public function odgovori(Request $request, Preporuka $preporuka): RedirectResponse
    {
        $ja = $this->ja($request);
        abort_unless($preporuka->savjetnik_id === $ja->id && $preporuka->status === 'pitanje', 404);
        $tekst = $request->validate(['odgovor' => 'required|string|max:1000'], [], ['odgovor' => 'odgovor'])['odgovor'];

        $preporuka->update(['odgovor_savjetnika' => $tekst, 'status' => 'poslano']);
        PosaljiObavijestPreporuke::dispatch(
            $preporuka->vlasnik_id, 'Odgovor savjetnika ' . Str::limit($ja->name, 60), 'Savjetnik je odgovorio na vaše pitanje',
            Str::limit($ja->name, 60) . ' vam je odgovorio. Sada možete odlučiti o preporuci.', ['„' . Str::limit($tekst, 300) . '”'],
            route('preporuke.index'), 'Pogledaj i odgovori', $ja->email,
        );

        return back()->with('status', 'Odgovor je poslan.');
    }

    public function povuci(Request $request, Preporuka $preporuka): RedirectResponse
    {
        abort_unless($preporuka->savjetnik_id === $this->ja($request)->id && in_array($preporuka->status, Preporuka::OTVORENO, true), 404);
        $preporuka->update(['status' => 'povuceno']);

        return back()->with('status', 'Preporuka je povučena.');
    }

    // ---- gospodarstvo ----

    public function index(Request $request): View
    {
        return view('preporuke.index', [
            'preporuke' => Preporuka::where('vlasnik_id', $request->user()->id)
                ->orderByRaw("CASE WHEN status IN ('poslano', 'pitanje', 'prihvaceno') THEN 0 ELSE 1 END")->latest('id')->limit(200)->get(),
        ]);
    }

    public function odgovor(Request $request, Preporuka $preporuka): RedirectResponse
    {
        abort_unless($preporuka->vlasnik_id === $request->user()->id, 404);
        $d = $request->validate([
            'radnja' => 'required|in:prihvati,odbij,pitanje,napravljeno,nije',
            'tekst' => 'nullable|required_if:radnja,pitanje|string|max:1000',
        ], ['tekst.required_if' => 'Upišite pitanje.']);

        $prijelazi = [
            'prihvati' => [['poslano', 'pitanje'], 'prihvaceno', 'prihvaćeno'],
            'odbij' => [['poslano', 'pitanje', 'prihvaceno'], 'odbijeno', 'odbijeno'],
            'pitanje' => [['poslano', 'prihvaceno'], 'pitanje', 'ima pitanje'],
            'napravljeno' => [['poslano', 'prihvaceno', 'pitanje'], 'napravljeno', 'napravljeno'],
            'nije' => [['prihvaceno'], 'nije_napravljeno', 'nije napravljeno'],
        ];
        [$iz, $u, $opis] = $prijelazi[$d['radnja']];
        abort_unless(in_array($preporuka->status, $iz, true), 404);

        $tekst = isset($d['tekst']) ? trim($d['tekst']) : null;
        $preporuka->update(array_filter(['status' => $u, 'pitanje' => $d['radnja'] === 'pitanje' ? $tekst : null]) + ['odgovoreno_at' => now(), 'savjetnik_vidio_at' => null]);
        Preporuke::javiSavjetniku($preporuka, $opis, $tekst);

        return back()->with('status', 'Odgovor je spremljen i savjetnik će ga vidjeti.');
    }
}
