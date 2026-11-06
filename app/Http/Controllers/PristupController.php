<?php

namespace App\Http\Controllers;

use App\Mail\PozivSavjetnikuMail;
use App\Mail\SavjetnikPrihvatioMail;
use App\Models\Gnojidba;
use App\Models\Parcela;
use App\Models\Prskanje;
use App\Models\Pristup;
use App\Models\User;
use App\Support\Upozorenja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/** Pristup savjetnika: gospodarstvo poziva (profil), savjetnik prihvaća poveznicom iz maila i radi u "Klijenti". */
class PristupController extends Controller
{
    /** Pravi prijavljeni korisnik (i dok savjetnik radi u klijentovom gospodarstvu). */
    private function ja(Request $request): User
    {
        return $request->attributes->get('savjetnik') ?? $request->user();
    }

    private function moj(Request $request, Pristup $pristup): Pristup
    {
        abort_unless($pristup->vlasnik_id === $request->user()->id, 404);

        return $pristup;
    }

    // ---- gospodarstvo (profil) ----

    public function posalji(Request $request): RedirectResponse
    {
        $podaci = $request->validateWithBag('savjetnik', [
            'email' => 'required|email|max:190',
            'prava' => 'required|in:pregled,unos',
        ], [], ['email' => 'e-mail savjetnika']);
        $email = mb_strtolower(trim($podaci['email']));
        $ja = $request->user();
        if ($email === mb_strtolower($ja->email)) {
            return back()->withErrors(['email' => 'To je vaš e-mail — pozovite savjetnika njegovim e-mailom.'], 'savjetnik');
        }

        $pristup = Pristup::where('vlasnik_id', $ja->id)->where('email', $email)->where('status', '!=', 'opozvano')->first();
        if ($pristup && $pristup->status === 'prihvaceno') {
            $pristup->update(['prava' => $podaci['prava']]);
            return redirect()->route('profile.edit')->with('status', 'savjetnik-prava')->withFragment('savjetnici');
        }
        $pristup ??= new Pristup(['vlasnik_id' => $ja->id, 'email' => $email]);
        $pristup->fill(['prava' => $podaci['prava'], 'token' => Pristup::noviToken(), 'status' => 'poslano'])->save();
        Mail::to($email)->send(new PozivSavjetnikuMail($pristup->load('vlasnik')));

        return redirect()->route('profile.edit')->with('status', 'savjetnik-poslano')->withFragment('savjetnici');
    }

    public function prava(Request $request, Pristup $pristup): RedirectResponse
    {
        $this->moj($request, $pristup)->update($request->validate(['prava' => 'required|in:pregled,unos']));

        return redirect()->route('profile.edit')->with('status', 'savjetnik-prava')->withFragment('savjetnici');
    }

    public function ponovno(Request $request, Pristup $pristup): RedirectResponse
    {
        $p = $this->moj($request, $pristup);
        abort_unless($p->status === 'poslano', 404);
        Mail::to($p->email)->send(new PozivSavjetnikuMail($p->load('vlasnik')));

        return redirect()->route('profile.edit')->with('status', 'savjetnik-poslano')->withFragment('savjetnici');
    }

    public function ukini(Request $request, Pristup $pristup): RedirectResponse
    {
        $this->moj($request, $pristup)->update(['status' => 'opozvano', 'token' => Pristup::noviToken()]);

        return redirect()->route('profile.edit')->with('status', 'savjetnik-ukinut')->withFragment('savjetnici');
    }

    // ---- savjetnik ----

    /** Poveznica iz maila: neprijavljeni vide prijavu/registraciju, prijavljeni potvrđuju poziv. */
    public function poziv(Request $request, string $token): View
    {
        $pristup = Pristup::with('vlasnik')->where('token', $token)->first();
        if ($pristup && ! $request->user()) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        return view('savjetnik.poziv', ['pristup' => $pristup, 'ja' => $request->user() ? $this->ja($request) : null]);
    }

    public function prihvati(Request $request, string $token): RedirectResponse
    {
        $ja = $this->ja($request);
        $pristup = Pristup::with('vlasnik')->where('token', $token)->where('status', 'poslano')->firstOrFail();
        abort_if($pristup->vlasnik_id === $ja->id, 403, 'Ne možete biti savjetnik vlastitom gospodarstvu.');

        $pristup->update(['savjetnik_id' => $ja->id, 'status' => 'prihvaceno', 'prihvaceno_at' => now()]);
        $ja->forceFill(['je_savjetnik' => true])->save();
        Mail::to($pristup->vlasnik)->send(new SavjetnikPrihvatioMail($pristup->load('savjetnik')));

        return redirect()->route('klijenti')->with('status', 'Pristup gospodarstvu ' . ($pristup->vlasnik->naziv_gospodarstva ?: $pristup->vlasnik->name) . ' je prihvaćen.');
    }

    /** Pregled klijenata: tko kasni s evidencijom, rokovi, karence, skladište. */
    public function klijenti(Request $request): View
    {
        $ja = $this->ja($request);
        $klijenti = Pristup::aktivni()->with('vlasnik')->where('savjetnik_id', $ja->id)->get()
            ->filter(fn ($p) => $p->vlasnik)
            ->map(function (Pristup $p) {
                $v = $p->vlasnik;
                $upozorenja = Upozorenja::za($v)->reject(fn ($u) => str_starts_with($u['kljuc'], 'preporuka'));
                $vrsta = fn ($prefiks) => $upozorenja->filter(fn ($u) => str_starts_with($u['kljuc'], $prefiks));
                $zadnji = collect([
                    Prskanje::where('user_id', $v->id)->max('updated_at'),
                    Gnojidba::where('user_id', $v->id)->max('updated_at'),
                ])->filter()->max();

                return [
                    'pristup' => $p, 'gospodarstvo' => $v->naziv_gospodarstva ?: $v->name, 'vlasnik' => $v,
                    'ha' => (float) Parcela::where('user_id', $v->id)->sum('povrsina_ha'),
                    'parcela' => Parcela::where('user_id', $v->id)->count(),
                    'zadnji_unos' => $zadnji ? \Illuminate\Support\Carbon::parse($zadnji) : null,
                    'dopuniti' => $upozorenja->sum(fn ($u) => preg_match('/^(?:prskanja|eppo)-(\d+)$/', $u['kljuc'], $m) ? (int) $m[1] : 0),
                    'rokovi' => $vrsta('rok-'), 'zaliha' => $vrsta('zaliha-'), 'karence' => $vrsta('karenca-'),
                    'hitno' => $upozorenja->where('razina', 'crveno')->count(), 'ukupno' => $upozorenja->count(),
                ];
            })
            ->sortByDesc(fn ($k) => [$k['hitno'], $k['ukupno']])->values();

        return view('klijenti', [
            'klijenti' => $klijenti, 'ja' => $ja,
            'pozivi' => Pristup::with('vlasnik')->where('status', 'poslano')->where('email', mb_strtolower($ja->email))->get(),
            'poslaniPozivi' => \App\Models\PozivGospodarstvu::where('savjetnik_id', $ja->id)->whereIn('status', ['poslano', 'odbijeno'])->latest('poslano_at')->get(),
        ]);
    }

    public function otvori(Request $request, Pristup $pristup): RedirectResponse
    {
        abort_unless($pristup->status === 'prihvaceno' && $pristup->savjetnik_id === $this->ja($request)->id, 404);
        $request->session()->put(['klijent_pristup_id' => $pristup->id, 'klijent_savjetnik_id' => $this->ja($request)->id]);

        // prebacivanje iz trake: ostaje na istoj stranici (samo lokalna putanja, bez profila/admina)
        $natrag = (string) $request->input('natrag', '');
        if (preg_match('#^/(?!/)[\w\-/]*$#', $natrag) && ! preg_match('#^/(profile|admin|savjetnici|klijenti)#', $natrag) && $natrag !== '/') {
            return redirect($natrag);
        }

        return redirect()->route('dashboard');
    }

    public function zatvori(Request $request): RedirectResponse
    {
        $request->session()->forget(['klijent_pristup_id', 'klijent_savjetnik_id']);
        // vrati pravog korisnika i za ostatak ovog zahtjeva
        if ($ja = $request->attributes->get('savjetnik')) {
            \Illuminate\Support\Facades\Auth::setUser($ja);
        }

        return redirect()->route('klijenti');
    }
}
