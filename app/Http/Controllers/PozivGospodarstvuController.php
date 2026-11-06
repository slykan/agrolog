<?php

namespace App\Http\Controllers;

use App\Mail\PozivGospodarstvuMail;
use App\Mail\PozivPrihvacenMail;
use App\Models\PozivGospodarstvu;
use App\Models\Pristup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/** Savjetnik poziva poljoprivrednike (Klijenti). Pristup nastaje tek kad poljoprivrednik prihvati i sam odabere prava. */
class PozivGospodarstvuController extends Controller
{
    private const DNEVNI_LIMIT = 20;

    /** Pravi prijavljeni korisnik, i dok savjetnik radi u klijentovom gospodarstvu. */
    private function ja(Request $request): ?User
    {
        return $request->attributes->get('savjetnik') ?? $request->user();
    }

    private function moj(Request $request, PozivGospodarstvu $poziv): PozivGospodarstvu
    {
        abort_unless($poziv->savjetnik_id === $this->ja($request)->id, 404);

        return $poziv;
    }

    private function greska(string $poruka): RedirectResponse
    {
        return redirect()->route('klijenti')->withInput()->withErrors(['email' => $poruka], 'pozivGospodarstvu');
    }

    /** Sprema poziv i šalje mail zajedno: ako mail ne prođe, poziv se ne bilježi kao poslan. */
    private function isporuci(PozivGospodarstvu $poziv, User $ja): bool
    {
        try {
            DB::transaction(function () use ($poziv) {
                $poziv->save();
                Mail::to($poziv->email)->send(new PozivGospodarstvuMail($poziv->load('savjetnik')));
            });
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
        RateLimiter::hit('pozivi-gospodarstvima:' . $ja->id, 86400);

        return true;
    }

    // ---- savjetnik ----

    public function posalji(Request $request): RedirectResponse
    {
        $ja = $this->ja($request);
        abort_unless($ja->je_savjetnik, 403);

        $podaci = $request->validateWithBag('pozivGospodarstvu', ['email' => 'required|email|max:190'], [], ['email' => 'e-mail poljoprivrednika']);
        $email = mb_strtolower(trim($podaci['email']));

        if ($email === mb_strtolower($ja->email)) {
            return $this->greska('To je vaš e-mail. Upišite e-mail poljoprivrednika.');
        }
        $vecKlijent = Pristup::aktivni()->where('savjetnik_id', $ja->id)
            ->whereHas('vlasnik', fn ($q) => $q->whereRaw('LOWER(email) = ?', [$email]))->exists();
        if ($vecKlijent) {
            return $this->greska('To gospodarstvo je već među vašim klijentima.');
        }

        $poziv = PozivGospodarstvu::where('savjetnik_id', $ja->id)->where('email', $email)->first();
        if ($poziv?->status === 'odbijeno') {
            return $this->greska('Ova osoba je odbila vaš poziv, pa joj ne možemo poslati novi.');
        }
        if ($poziv?->status === 'poslano') {
            return $this->greska('Poziv na tu adresu je već poslan i čeka odgovor.');
        }
        if (RateLimiter::tooManyAttempts('pozivi-gospodarstvima:' . $ja->id, self::DNEVNI_LIMIT)) {
            return $this->greska('Danas ste poslali najviše poziva što je moguće (' . self::DNEVNI_LIMIT . '). Nastavite sutra.');
        }

        $poziv ??= new PozivGospodarstvu(['savjetnik_id' => $ja->id, 'email' => $email]);
        $poziv->fill(['token' => PozivGospodarstvu::noviToken(), 'status' => 'poslano', 'broj_slanja' => 1, 'poslano_at' => now(), 'odgovoreno_at' => null]);

        if (! $this->isporuci($poziv, $ja)) {
            return $this->greska('Mail se trenutno ne može poslati. Pokušajte ponovno malo kasnije.');
        }

        return redirect()->route('klijenti')->with('status', 'Poziv je poslan na ' . $email . '. Čim ga prihvati, gospodarstvo se pojavljuje na vašem popisu.');
    }

    public function ponovno(Request $request, PozivGospodarstvu $poziv): RedirectResponse
    {
        $ja = $this->ja($request);
        $this->moj($request, $poziv);
        if (! $poziv->smijePonovno()) {
            return redirect()->route('klijenti')->with('status', 'Ovaj poziv se sada ne može ponovno poslati. Moguće je jednom dnevno, najviše ' . PozivGospodarstvu::MAX_SLANJA . ' puta.');
        }
        if (RateLimiter::tooManyAttempts('pozivi-gospodarstvima:' . $ja->id, self::DNEVNI_LIMIT)) {
            return redirect()->route('klijenti')->with('status', 'Danas ste poslali najviše poziva što je moguće (' . self::DNEVNI_LIMIT . '). Nastavite sutra.');
        }

        $poziv->fill(['broj_slanja' => $poziv->broj_slanja + 1, 'poslano_at' => now()]);
        if (! $this->isporuci($poziv, $ja)) {
            return redirect()->route('klijenti')->with('status', 'Mail se trenutno ne može poslati. Pokušajte ponovno malo kasnije.');
        }

        return redirect()->route('klijenti')->with('status', 'Poziv je ponovno poslan na ' . $poziv->email . '.');
    }

    public function povuci(Request $request, PozivGospodarstvu $poziv): RedirectResponse
    {
        $this->moj($request, $poziv);
        abort_unless($poziv->status === 'poslano', 404);
        $poziv->update(['status' => 'povuceno', 'token' => PozivGospodarstvu::noviToken()]);

        return redirect()->route('klijenti')->with('status', 'Poziv na ' . $poziv->email . ' je povučen.');
    }

    // ---- poljoprivrednik ----

    /** Poveznica iz maila: neprijavljeni vide prijavu ili registraciju i mogu odbiti, prijavljeni biraju prava i prihvaćaju. */
    public function poziv(Request $request, string $token): View
    {
        $poziv = PozivGospodarstvu::with('savjetnik')->where('token', $token)->first();
        if ($poziv?->status === 'poslano' && ! $request->user()) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        return view('savjetnik.poziv-gospodarstvu', [
            'poziv' => $poziv,
            'ja' => $this->ja($request),
            'uKlijentu' => $request->attributes->has('savjetnik'),
        ]);
    }

    public function prihvati(Request $request, string $token): RedirectResponse
    {
        $ja = $this->ja($request);
        $poziv = PozivGospodarstvu::with('savjetnik')->where('token', $token)->where('status', 'poslano')->firstOrFail();
        abort_if($poziv->savjetnik_id === $ja->id, 403, 'Ne možete prihvatiti vlastiti poziv.');
        $prava = $request->validate(['prava' => 'required|in:pregled,unos'], ['prava.required' => 'Odaberite što savjetnik smije raditi.'])['prava'];
        $savjetnik = $poziv->savjetnik;
        $emailSavjetnika = mb_strtolower($savjetnik->email);

        $pristup = DB::transaction(function () use ($ja, $poziv, $prava, $savjetnik, $emailSavjetnika) {
            $pristup = Pristup::where('vlasnik_id', $ja->id)->where('status', '!=', 'opozvano')
                ->where(fn ($q) => $q->where('savjetnik_id', $savjetnik->id)->orWhere('email', $emailSavjetnika))->first()
                ?? new Pristup(['vlasnik_id' => $ja->id, 'token' => Pristup::noviToken()]);
            $pristup->fill(['email' => $emailSavjetnika, 'savjetnik_id' => $savjetnik->id, 'prava' => $prava, 'status' => 'prihvaceno', 'prihvaceno_at' => now()])->save();
            $poziv->update(['status' => 'prihvaceno', 'odgovoreno_at' => now()]);

            return $pristup;
        });

        try {
            Mail::to($savjetnik)->send(new PozivPrihvacenMail($pristup->load('vlasnik', 'savjetnik')));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('dashboard')->with('status', $savjetnik->name . ' sada ima pristup vašem gospodarstvu (' . mb_strtolower(Pristup::PRAVA[$prava]) . '). Prava možete promijeniti ili pristup ukinuti u Profilu, pod Pristup savjetniku.');
    }

    /** Odbijanje radi i bez računa: tko ne želi suradnju, ne mora se registrirati. */
    public function odbij(string $token): RedirectResponse
    {
        PozivGospodarstvu::where('token', $token)->where('status', 'poslano')->update(['status' => 'odbijeno', 'odgovoreno_at' => now()]);

        return redirect()->route('poziv.gospodarstvo', $token);
    }
}
