<?php

namespace App\Http\Controllers;

use App\Mail\KontaktMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/**
 * Kontakt obrazac. Zaštita od spama bez vanjskih servisa: skriveno polje, minimalno vrijeme ispunjavanja,
 * ograničenje po IP adresi, po adresi za kopiju i po broju poveznica. Poruke se ne spremaju, samo se šalju mailom.
 */
class KontaktController extends Controller
{
    public const TEME = ['oglasavanje' => 'Oglašavanje', 'nadogradnja' => 'Nadogradnja aplikacije', 'opce' => 'Opća pitanja'];
    public const PRIMATELJ = 'info@agro-log.app';

    private const MIN_SEKUNDI = 3;
    private const MAX_POVEZNICA = 3;

    public function pokazi(Request $request): View
    {
        $tema = $request->query('tema');
        $korisnik = $request->attributes->get('pristup') ? null : $request->user();

        return view('contact', [
            'teme' => self::TEME,
            'odabranaTema' => old('tema', is_string($tema) && isset(self::TEME[$tema]) ? $tema : ''),
            'korisnik' => $korisnik,
            'obrazacOd' => Crypt::encryptString((string) time()),
        ]);
    }

    public function posalji(Request $request): RedirectResponse
    {
        $natrag = fn () => redirect()->to(route('contact') . '#poruka')->withInput();

        // robot je popunio skriveno polje: izgleda kao uspjeh, ali se ništa ne šalje
        if ($request->filled('web_stranica')) {
            return redirect()->to(route('contact') . '#poruka')->with('kontakt_poslano', true);
        }

        $d = $request->validate([
            'tema' => ['required', 'in:' . implode(',', array_keys(self::TEME))],
            'ime' => 'required|string|max:120',
            'tvrtka' => 'nullable|string|max:150',
            'oib' => ['nullable', 'string', function ($polje, $v, $greska) {
                if (! self::oibIspravan((string) $v)) {
                    $greska('OIB mora imati 11 znamenki i biti ispravan.');
                }
            }],
            'email' => 'required|email|max:190',
            'poruka' => ['required', 'string', 'min:10', 'max:5000', function ($polje, $v, $greska) {
                if (preg_match_all('#https?://|www\.#i', (string) $v) > self::MAX_POVEZNICA) {
                    $greska('Poruka ima previše poveznica.');
                }
            }],
        ], [
            'tema.required' => 'Odaberite temu.', 'tema.in' => 'Odaberite temu s popisa.',
            'ime.required' => 'Upišite ime i prezime.', 'ime.max' => 'Ime i prezime je predugo.',
            'tvrtka.max' => 'Naziv tvrtke je predug.',
            'email.required' => 'Upišite e-mail adresu.', 'email.email' => 'Upišite ispravnu e-mail adresu.', 'email.max' => 'E-mail adresa je preduga.',
            'poruka.required' => 'Upišite poruku.', 'poruka.min' => 'Poruka je prekratka, upišite barem 10 znakova.', 'poruka.max' => 'Poruka je preduga, najviše 5.000 znakova.',
        ]);

        if ($this->prebrzo((string) $request->input('obrazac_od'))) {
            return $natrag()->with('kontakt_greska', 'Poruka nije poslana. Pričekajte trenutak i pošaljite je ponovno.');
        }

        $kljucIp = 'kontakt:' . sha1((string) $request->ip());
        if (RateLimiter::tooManyAttempts($kljucIp, 5)) {
            return $natrag()->with('kontakt_greska', 'Poslali ste više poruka zaredom. Pokušajte ponovno za nekoliko minuta.');
        }
        RateLimiter::hit($kljucIp, 600);

        $podaci = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $d) + ['tema_naziv' => self::TEME[$d['tema']]];

        try {
            Mail::to(self::PRIMATELJ)->send(new KontaktMail($podaci, false));
        } catch (\Throwable $e) {
            report($e);

            return $natrag()->with('kontakt_greska', 'Poruku trenutno nije moguće poslati. Pokušajte ponovno ili nam pišite na ' . self::PRIMATELJ . '.');
        }

        // kopija ide na upisanu adresu: ograničeno, da se obrazac ne može koristiti za slanje tuđim adresama
        if ($request->boolean('kopija')) {
            $kljucKopija = 'kontakt-kopija:' . sha1(mb_strtolower($podaci['email']));
            if (! RateLimiter::tooManyAttempts($kljucKopija, 2)) {
                RateLimiter::hit($kljucKopija, 3600);
                try {
                    Mail::to($podaci['email'])->send(new KontaktMail($podaci, true));
                } catch (\Throwable $e) {
                    report($e);   // poruka je već stigla nama
                }
            }
        }

        return redirect()->to(route('contact') . '#poruka')->with('kontakt_poslano', true);
    }

    private function prebrzo(string $token): bool
    {
        try {
            return time() - (int) Crypt::decryptString($token) < self::MIN_SEKUNDI;
        } catch (\Throwable) {
            return true;   // bez valjanog tokena obrazac nije učitan s naše stranice
        }
    }

    /** OIB: 11 znamenki i kontrolna znamenka po ISO 7064, MOD 11,10. Prazno je dopušteno. */
    public static function oibIspravan(string $oib): bool
    {
        if (preg_match('/^\d{11}$/', $oib) !== 1) {
            return false;
        }
        $a = 10;
        for ($i = 0; $i < 10; $i++) {
            $a = ($a + (int) $oib[$i]) % 10;
            $a = ($a === 0 ? 10 : $a) * 2 % 11;
        }
        $k = 11 - $a;

        return ($k === 10 ? 0 : $k) === (int) $oib[10];
    }
}
