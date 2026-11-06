<?php

namespace App\Http\Controllers;

use App\Mail\PonudaOglasivacuMail;
use App\Models\PonudaOglasivacu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Admin: ponuda za oglašavanje jednoj po jednoj tvrtki, s probom i zapisnikom poslanih. */
class PonudaController extends Controller
{
    public const PREDMET = 'Budite prvi oglašivač na AgroLogu';

    public const TEKST = "Poštovani,\n\n"
        . "AgroLog (agro-log.app) nova je hrvatska aplikacija u kojoj poljoprivrednici besplatno vode evidenciju prskanja, gnojidbe i berbe, uz parcele na ARKOD karti, skladište, mehanizaciju i troškove.\n\n"
        . "Novi smo na tržištu i tražimo prve oglašivače. Mislim da bi {tvrtka} bio dobar izbor, jer su {proizvodi} upravo ono o čemu naši korisnici razmišljaju dok planiraju radove.\n\n"
        . "Kao jedan od prvih oglašivača možete dobiti dobre uvjete, a uz banner je moguć i sponzorirani članak. Uz banner dobivate i izvještaj o broju prikaza i klikova.\n\n"
        . "Ako vas zanima, odgovorite na ovaj mail pa ću vam poslati ponudu.\n\n"
        . "Lijep pozdrav,\nAlan Fadljević\nAgroLog, agro-log.app\n+385 98 987 66 97";

    public function index(): View
    {
        return view('admin.ponude', [
            'poslane' => PonudaOglasivacu::orderByDesc('poslano_at')->orderByDesc('id')->get(),
            'predmet' => self::PREDMET, 'tekst' => self::TEKST,
        ]);
    }

    public function posalji(Request $request): RedirectResponse
    {
        $d = $request->validate([
            'tvrtka' => 'required|string|max:150',
            'email' => 'required|email|max:190',
            'proizvodi' => 'required|string|max:150',
            'predmet' => 'required|string|max:150',
            'tekst' => 'required|string|min:20|max:5000',
        ], [], ['tvrtka' => 'naziv tvrtke', 'email' => 'e-mail adresa', 'proizvodi' => 'što prodaju', 'predmet' => 'naslov maila', 'tekst' => 'tekst maila']);

        $tekst = str_replace(['{tvrtka}', '{proizvodi}'], [trim($d['tvrtka']), trim($d['proizvodi'])], $d['tekst']);
        $mail = new PonudaOglasivacuMail(trim($d['predmet']), $tekst);

        if ($request->input('akcija') === 'proba') {
            return $this->slanje($request, $request->user()->email, $mail, 'Probni mail poslan je na ' . $request->user()->email . '. Pogledajte ga pa pošaljite tvrtki.', true);
        }

        $email = mb_strtolower(trim($d['email']));
        $ranije = PonudaOglasivacu::where('email', $email)->orderByDesc('poslano_at')->get();

        if ($ranije->whereNotNull('ne_zeli_at')->isNotEmpty()) {
            throw ValidationException::withMessages(['email' => 'Ova adresa je javila da ne želi poruke. Slanje nije moguće.']);
        }
        if ($ranije->isNotEmpty() && ! $request->boolean('ipak')) {
            return back()->withInput()->with('vec_poslano', $ranije->first()->poslano_at->timezone('Europe/Zagreb')->format('d.m.Y. H:i'));
        }

        $odgovor = $this->slanje($request, $email, $mail, 'Ponuda je poslana tvrtki ' . trim($d['tvrtka']) . '.');
        if (! session('poruka_greska')) {
            PonudaOglasivacu::create(['tvrtka' => trim($d['tvrtka']), 'email' => $email, 'proizvodi' => trim($d['proizvodi']), 'predmet' => trim($d['predmet']), 'poslano_at' => now()]);
        }

        return $odgovor;
    }

    private function slanje(Request $request, string $na, PonudaOglasivacuMail $mail, string $poruka, bool $zadrziUnos = false): RedirectResponse
    {
        try {
            Mail::to($na)->send($mail);
        } catch (\Throwable $e) {
            report($e);
            session()->flash('poruka_greska', 'Mail nije poslan. Pokušajte ponovno.');

            return back()->withInput();
        }
        $natrag = redirect()->route('admin.ponude')->with('poruka', $poruka);

        return $zadrziUnos ? $natrag->withInput() : $natrag;
    }

    /** Tvrtka je javila da ne želi poruke (ili je to bila greška). Vrijedi za sve zapise te adrese. */
    public function neZeli(PonudaOglasivacu $ponuda): RedirectResponse
    {
        $vrijednost = $ponuda->ne_zeli_at ? null : now();
        PonudaOglasivacu::where('email', $ponuda->email)->update(['ne_zeli_at' => $vrijednost]);

        return redirect()->route('admin.ponude')->with('poruka', $vrijednost ? $ponuda->email . ' više ne može dobiti ponudu.' : $ponuda->email . ' ponovno može dobiti ponudu.');
    }
}
