<?php

namespace App\Http\Controllers;

use App\Mail\PreporukaMail;
use App\Models\Prezentacija;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Javni popis gospodarstava (/gospodarstva). Vlasnik uređuje prezentaciju u profilu; javna je tek kad je uključi
 * i admin odobri. Telefon nije u HTML-u stranice, nego se dohvaća klikom (otežava skupljanje brojeva).
 */
class PrezentacijaController extends Controller
{
    /** Spremanje iz profila. Bez kvačice se prezentacija samo skriva, podaci ostaju. */
    public function spremi(Request $request): RedirectResponse
    {
        $user = $request->user();
        $p = Prezentacija::where('user_id', $user->id)->first();

        if (! $request->boolean('vidljivo')) {
            $p?->update(['vidljivo' => false]);

            return redirect()->to(route('profile.edit') . '#prezentacija')->with('status', 'prezentacija-spremljena');
        }

        $url = ['nullable', 'string', 'max:200', 'regex:#^(https?://)?[^\s/$.?\#].[^\s]*$#i'];
        $v = $request->validateWithBag('prezentacija', [
            'naziv' => 'required|string|min:3|max:120',
            'mjesto' => 'required|string|min:2|max:80',
            'zupanija' => ['required', Rule::in(Prezentacija::ZUPANIJE)],
            'opis' => 'required|string|min:50|max:3000',
            'oznake' => 'required|array|min:1|max:8',
            'oznake.*' => [Rule::in(Prezentacija::OZNAKE)],
            'telefon' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9 \/\-()]{6,}$/'],
            'email' => 'nullable|email|max:150',
            'adresa' => 'required|string|min:5|max:200',
            // pin koji je vlasnik postavio na karti (Hrvatska i malo oko nje)
            'lat' => 'required|numeric|between:42,47',
            'lng' => 'required|numeric|between:13,20',
            'radno_vrijeme' => 'nullable|string|max:150',
            'web' => $url, 'facebook' => $url, 'instagram' => $url,
            'slika' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
            'ukloni_sliku' => 'nullable|boolean',
        ], [
            'opis.min' => 'Opišite gospodarstvo s barem 50 znakova: što proizvodite, gdje i kako se može kupiti.',
            'oznake.required' => 'Odaberite barem jednu oznaku: što proizvodite ili nudite.',
            'telefon.regex' => 'Upišite ispravan broj telefona, npr. 091 234 5678.',
            'lat.required' => 'Označite na karti gdje se gospodarstvo nalazi: potražite adresu pa kliknite na svoje dvorište.',
            'lng.required' => 'Označite na karti gdje se gospodarstvo nalazi.',
            'lat.between' => 'Lokacija mora biti u Hrvatskoj.', 'lng.between' => 'Lokacija mora biti u Hrvatskoj.',
        ], [
            'naziv' => 'naziv', 'mjesto' => 'mjesto', 'zupanija' => 'županija', 'opis' => 'opis', 'oznake' => 'oznake', 'telefon' => 'telefon',
            'web' => 'web stranica', 'radno_vrijeme' => 'radno vrijeme', 'slika' => 'slika', 'adresa' => 'adresa', 'lat' => 'lokacija', 'lng' => 'lokacija',
        ]);

        $poveznica = fn ($u) => blank($u) ? null : (preg_match('#^https?://#i', $u) ? trim($u) : 'https://' . trim($u));
        $podaci = [
            'vidljivo' => true,
            'naziv' => trim($v['naziv']), 'mjesto' => trim($v['mjesto']), 'zupanija' => $v['zupanija'], 'opis' => trim($v['opis']),
            'oznake' => array_values(array_intersect(Prezentacija::OZNAKE, $v['oznake'])),
            'telefon' => trim($v['telefon']), 'whatsapp' => $request->boolean('whatsapp'), 'viber' => $request->boolean('viber'),
            'email' => $v['email'] ?? null, 'adresa' => trim($v['adresa']),
            'kucni_prag' => $request->boolean('kucni_prag'),
            'radno_vrijeme' => $request->boolean('kucni_prag') && filled($v['radno_vrijeme'] ?? null) ? trim($v['radno_vrijeme']) : null,
            'web' => $poveznica($v['web'] ?? null), 'facebook' => $poveznica($v['facebook'] ?? null), 'instagram' => $poveznica($v['instagram'] ?? null),
            'lat' => round((float) $v['lat'], 7), 'lng' => round((float) $v['lng'], 7),
        ];
        if ($request->hasFile('slika')) {
            Prezentacija::obrisiSliku($p?->slika);
            $podaci['slika'] = Prezentacija::spremiSliku($request->file('slika'));
        } elseif ($request->boolean('ukloni_sliku')) {
            Prezentacija::obrisiSliku($p?->slika);
            $podaci['slika'] = null;
        }

        $nova = ! $p || $p->odobreno_at === null;   // prvi put ili nakon odbijanja: ide na pregled
        if ($p) {
            if ($p->naziv !== $podaci['naziv'] || $p->mjesto !== $podaci['mjesto']) {
                $podaci['slug'] = Prezentacija::noviSlug($podaci['naziv'], $podaci['mjesto'], $p->id);
            }
            $p->update($podaci + ($nova ? ['odbijeno_razlog' => null] : []));
        } else {
            $p = Prezentacija::create($podaci + ['user_id' => $user->id, 'slug' => Prezentacija::noviSlug($podaci['naziv'], $podaci['mjesto'])]);
        }

        $this->javiAdminu($p, $nova);

        if ($request->input('natrag') === 'uvod') {
            return redirect()->route('dashboard')->with('status', 'Prezentacija je poslana na pregled. Javit ćemo vam mailom kad bude objavljena.');
        }

        return redirect()->to(route('profile.edit') . '#prezentacija')->with('status', $nova ? 'prezentacija-na-pregledu' : 'prezentacija-spremljena');
    }

    public function obrisi(Request $request): RedirectResponse
    {
        $p = Prezentacija::where('user_id', $request->user()->id)->first();
        if ($p) {
            Prezentacija::obrisiSliku($p->slika);
            $p->proizvodi()->pluck('slika')->each(fn ($s) => Prezentacija::obrisiSliku($s));
            $p->delete();   // proizvodi se brišu kaskadno
        }

        return redirect()->to(route('profile.edit') . '#prezentacija')->with('status', 'prezentacija-obrisana');
    }

    /** Obrazac odmah nakon registracije (kvačica "želim predstaviti gospodarstvo"); nudi se samo jednom. */
    public function uvod(Request $request): View
    {
        $user = $request->user();
        if ($user->prezentacija_ponudjena_at === null) {
            $user->forceFill(['prezentacija_ponudjena_at' => now()])->save();
        }

        return view('gospodarstva.uvod', ['user' => $user]);
    }

    /** "Ne želim" na podsjetniku: kvačica s registracije se gasi. */
    public function odustani(Request $request): RedirectResponse
    {
        $request->user()->forceFill(['zeli_prezentaciju' => false])->save();

        return back();
    }

    /** Admin: mail korisniku s pozivom da se predstavi na popisu (najviše jednom). */
    public function pozovi(\App\Models\User $user): RedirectResponse
    {
        if ($user->prezentacija_pozvan_at !== null || $user->prezentacija()->exists()) {
            return back()->with('status', 'Korisnik je već pozvan ili ima prezentaciju.');
        }
        Mail::to($user->email)->send(new PreporukaMail(
            predmet: 'Predstavite svoje gospodarstvo na AgroLogu',
            naslov: 'Predstavite gospodarstvo kupcima',
            uvod: 'Na AgroLogu postoji besplatan javni popis gospodarstava i Tržnica. Kupci vas pronađu po mjestu i onome što nudite te vam se javljaju izravno. Prezentaciju uključujete u profilu, a objavljujemo je kad je pregledamo.',
            stavke: ['Opis gospodarstva, slika i kontakt', 'Lokacija na karti i upute za dolazak', 'Proizvodi s cijenom na Tržnici'],
            link: route('profile.edit') . '#prezentacija', gumb: 'Uključi prezentaciju',
        ));
        $user->forceFill(['prezentacija_pozvan_at' => now(), 'zeli_prezentaciju' => true])->save();

        return back()->with('status', 'Poziv je poslan na ' . $user->email . '.');
    }

    private function javiAdminu(Prezentacija $p, bool $nova): void
    {
        // kao obavijest o registraciji: odmah (bez reda čekanja) na slynetwork@gmail.com, uz ostale admine
        $primatelji = array_unique(array_merge(['slynetwork@gmail.com'], config('app.admin_emails')));
        $vlasnik = $p->user;
        $mail = new PreporukaMail(
            predmet: ($nova ? 'Gospodarstvo na pregledu: ' : 'Izmijenjena prezentacija: ') . $p->naziv,
            naslov: $nova ? 'Gospodarstvo čeka odobrenje' : 'Prezentacija je izmijenjena',
            uvod: $nova ? 'Gospodarstvo je poslalo prezentaciju na pregled za javni popis. Pregledajte podatke i odobrite ili odbijte.'
                : 'Odobrena prezentacija je izmijenjena i već je javna. Pogledajte je li sve u redu.',
            stavke: array_filter([
                $p->naziv . ' · ' . $p->mjesto . ' (' . $p->zupanija . ')',
                $vlasnik ? 'Korisnik: ' . $vlasnik->name . ' · ' . $vlasnik->email : null,
                'Adresa: ' . $p->adresa . ' · tel. ' . $p->telefon,
                'Nudi: ' . implode(', ', $p->oznake ?? []),
                \Illuminate\Support\Str::limit($p->opis, 300),
            ]),
            link: route('admin.gospodarstva'), gumb: 'Otvori i odobri',
        );
        foreach ($primatelji as $email) {
            try {
                Mail::to($email)->send($mail);
            } catch (\Throwable $e) {
                report($e);   // spremanje prezentacije ne pada zbog maila
            }
        }
    }

    // ---------------- javno

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $oznaka = in_array($request->query('oznaka'), Prezentacija::OZNAKE, true) ? $request->query('oznaka') : null;
        $zupanija = in_array($request->query('zupanija'), Prezentacija::ZUPANIJE, true) ? $request->query('zupanija') : null;
        $prag = $request->boolean('kucni_prag');

        $upit = Prezentacija::javne()
            ->when($q !== '', fn ($u) => $u->where(fn ($w) => $w->where('naziv', 'like', "%{$q}%")->orWhere('mjesto', 'like', "%{$q}%")->orWhere('opis', 'like', "%{$q}%")))
            // oznake su JSON kakav sprema cast (unicode kao \uXXXX); traži se cijeli niz s navodnicima
            ->when($oznaka, fn ($u) => $u->where('oznake', 'like', '%' . json_encode($oznaka) . '%'))
            ->when($zupanija, fn ($u) => $u->where('zupanija', $zupanija))
            ->when($prag, fn ($u) => $u->where('kucni_prag', true));

        $karta = (clone $upit)->whereNotNull('lat')->get(['slug', 'naziv', 'mjesto', 'lat', 'lng'])
            ->map(fn ($p) => ['n' => $p->naziv, 'm' => $p->mjesto, 'u' => route('gospodarstvo', $p->slug), 'lat' => (float) $p->lat, 'lng' => (float) $p->lng])->values();

        return view('gospodarstva.index', [
            'gospodarstva' => $upit->orderByDesc('odobreno_at')->paginate(24)->withQueryString(),
            'karta' => $karta, 'q' => $q, 'oznaka' => $oznaka, 'zupanija' => $zupanija, 'prag' => $prag,
            'filtrirano' => $q !== '' || $oznaka || $zupanija || $prag,
        ]);
    }

    public function show(string $slug): View
    {
        $p = Prezentacija::javne()->where('slug', $slug)->firstOrFail();

        return view('gospodarstva.show', ['p' => $p, 'evidencija' => $p->vodiEvidenciju(),
            'proizvodi' => $p->proizvodi()->where('dostupno', true)->latest('updated_at')->get()]);
    }

    /** Broj telefona na klik "Prikaži broj". */
    public function telefon(string $slug): JsonResponse
    {
        $p = Prezentacija::javne()->where('slug', $slug)->firstOrFail();
        $b = $p->medunarodniBroj();

        return response()->json([
            'telefon' => $p->telefon, 'tel' => '+' . $b,
            'whatsapp' => $p->whatsapp ? 'https://wa.me/' . $b : null,
            'viber' => $p->viber ? 'viber://chat?number=%2B' . $b : null,
        ])->header('X-Robots-Tag', 'noindex');
    }

    // ---------------- admin

    public function admin(): View
    {
        $sve = Prezentacija::with('user:id,name,email')->where('vidljivo', true)->orderByRaw('odobreno_at IS NOT NULL')->latest('updated_at')->get();

        return view('admin.gospodarstva', ['sve' => $sve]);
    }

    public function odobri(Prezentacija $prezentacija): RedirectResponse
    {
        $prezentacija->update(['odobreno_at' => now(), 'odbijeno_razlog' => null]);
        $this->javiVlasniku($prezentacija, true);

        return back()->with('status', 'Odobreno: ' . $prezentacija->naziv);
    }

    public function odbij(Request $request, Prezentacija $prezentacija): RedirectResponse
    {
        $v = $request->validate(['razlog' => 'required|string|min:5|max:300']);
        $prezentacija->update(['odobreno_at' => null, 'odbijeno_razlog' => trim($v['razlog'])]);
        $this->javiVlasniku($prezentacija, false);

        return back()->with('status', 'Skriveno: ' . $prezentacija->naziv);
    }

    private function javiVlasniku(Prezentacija $p, bool $odobreno): void
    {
        $user = $p->user;
        if (! $user) {
            return;
        }
        Mail::to($user->email)->queue(new PreporukaMail(
            predmet: $odobreno ? 'Vaše gospodarstvo je na AgroLog popisu' : 'Prezentacija gospodarstva nije objavljena',
            naslov: $odobreno ? 'Vaša prezentacija je objavljena' : 'Prezentacija treba doradu',
            uvod: $odobreno ? 'Gospodarstvo ' . $p->naziv . ' sada je vidljivo na javnom popisu AgroLoga. Kupci vas mogu pronaći po mjestu i onome što nudite.'
                : 'Prezentaciju gospodarstva ' . $p->naziv . ' nismo objavili. Razlog je naveden ispod. Doradite je u profilu i spremite, pa ćemo je ponovno pregledati.',
            stavke: $odobreno ? [route('gospodarstvo', $p->slug)] : ['Razlog: ' . $p->odbijeno_razlog],
            link: $odobreno ? route('gospodarstvo', $p->slug) : route('profile.edit') . '#prezentacija',
            gumb: $odobreno ? 'Pogledaj stranicu' : 'Otvori profil',
        ));
    }
}
