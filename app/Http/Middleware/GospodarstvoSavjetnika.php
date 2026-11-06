<?php

namespace App\Http\Middleware;

use App\Models\Pristup;
use App\Models\PristupIzmjena;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Savjetnik radi u klijentovom gospodarstvu: za ovaj zahtjev aktivni korisnik postaje vlasnik gospodarstva,
 * pa svi ekrani rade s njegovim podacima. Pravi korisnik (savjetnik) je u $request->attributes('savjetnik').
 *
 * - Pristup se provjerava na svakom zahtjevu (prihvaćen, ne opozvan).
 * - "Samo pregled": svaki upis u podatke gospodarstva blokira se na razini baze.
 * - "Pregled i unos": svaki zahtjev s upisom bilježi se u povijest (pristup_izmjene).
 * - Profil, admin i upravljanje pristupima nisu dostupni u klijentovom gospodarstvu.
 */
class GospodarstvoSavjetnika
{
    /** Tablice koje nisu podaci gospodarstva (sesija, cache, red čekanja, evidencija pristupa). */
    private const SUSTAVNE = ['sessions', 'cache', 'cache_locks', 'jobs', 'failed_jobs', 'job_batches', 'pristupi', 'pristup_izmjene', 'pozivi_gospodarstvima', 'preporuke', 'users', 'szb_sredstva', 'oglas_statistika', 'oglasi'];

    /** Rute koje savjetnik ne smije otvoriti u klijentovom gospodarstvu. */
    private const ZABRANJENE = ['profile.*', 'admin.*', 'savjetnici.*', 'push.*', 'poziv.gospodarstvo.*', 'preporuke.*'];

    private const NAZIVI = [
        'prskanje-table' => 'Prskanje', 'gnojidba-table' => 'Gnojidba', 'berba-table' => 'Berba', 'parcela-manager' => 'Parcele i kulture',
        'skladiste-manager' => 'Skladište', 'mehanizacija-manager' => 'Mehanizacija',
    ];
    private const AKCIJE = [
        'saveNew' => 'novi zapis', 'saveMultiple' => 'više zapisa odjednom', 'saveEdit' => 'izmjena zapisa', 'deleteRow' => 'brisanje zapisa',
        'copyRow' => 'kopija zapisa', 'saveParcela' => 'spremljena parcela', 'deleteParcela' => 'brisanje parcele', 'saveKultura' => 'spremljena kultura',
        'deleteKultura' => 'brisanje kulture', 'zamijeniKulturu' => 'zamjena kulture', 'zavrsiKulturu' => 'kultura završena', 'vratiKulturu' => 'kultura vraćena',
        'spremiPrinos' => 'prinos i prihod', 'oznaciTrajni' => 'označen trajni nasad', 'spremi' => 'spremljeno', 'obrisi' => 'brisanje', 'spremiInventuru' => 'inventura', 'prenesi' => 'prijenos između skladišta',
        'spremiSkladiste' => 'skladište spremljeno', 'obrisiSkladiste' => 'skladište obrisano', 'spremiStroj' => 'stroj spremljen', 'obrisiStroj' => 'stroj obrisan',
        'spremiRok' => 'rok spremljen', 'obrisiRok' => 'rok obrisan', 'spremiServis' => 'servis upisan', 'obrisiServis' => 'servis obrisan',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $prijavljen = $request->user();
        $pristupId = $prijavljen ? $request->session()->get('klijent_pristup_id') : null;
        if (! $pristupId) {
            return $next($request);
        }

        // savjetnik zapamćen kod "Otvori"; prijavljeni korisnik mora biti on (ili, unutar istog procesa, već postavljeni vlasnik)
        $savjetnikId = (int) $request->session()->get('klijent_savjetnik_id');
        $pristup = Pristup::aktivni()->with('vlasnik')->where('savjetnik_id', $savjetnikId)->find($pristupId);
        if (! $pristup || ! $pristup->vlasnik || ! in_array($prijavljen->id, [$savjetnikId, $pristup->vlasnik_id], true)) {
            $request->session()->forget(['klijent_pristup_id', 'klijent_savjetnik_id']);   // pristup opozvan, ne postoji ili tuđa sesija: natrag na svoje
            return $next($request);
        }
        $savjetnik = $prijavljen->id === $savjetnikId ? $prijavljen : \App\Models\User::findOrFail($savjetnikId);

        if ($request->routeIs(...self::ZABRANJENE)) {
            return redirect()->route('dashboard')->with('status', 'Ovo nije dostupno dok radite u klijentovom gospodarstvu.');
        }

        // od ovdje: aktivni korisnik je vlasnik gospodarstva
        $request->attributes->set('savjetnik', $savjetnik);
        $request->attributes->set('pristup', $pristup);
        Auth::setUser($pristup->vlasnik);
        $request->setUserResolver(fn () => $pristup->vlasnik);

        $stanje = (object) ['aktivno' => true, 'tablice' => []];
        DB::beforeExecuting(function (string $sql) use ($stanje, $pristup) {
            if (! $stanje->aktivno || ! preg_match('/^\s*(insert\s+(?:or\s+\w+\s+)?into|update|delete\s+from|replace\s+into)\s+["`\[]?(\w+)/i', $sql, $m)) {
                return;
            }
            $tablica = strtolower($m[2]);
            if (in_array($tablica, self::SUSTAVNE, true)) {
                return;
            }
            if (! $pristup->smijeUnos()) {
                abort(403, 'Imate samo pregled ovog gospodarstva — izmjene nisu dozvoljene.');
            }
            $stanje->tablice[$tablica] = true;
        });

        try {
            $response = $next($request);
        } finally {
            $stanje->aktivno = false;
        }

        if ($stanje->tablice) {
            PristupIzmjena::create([
                'pristup_id' => $pristup->id, 'vlasnik_id' => $pristup->vlasnik_id, 'savjetnik_id' => $savjetnik->id,
                'savjetnik_ime' => $savjetnik->name, 'opis' => $this->opis($request), 'tablice' => array_keys($stanje->tablice),
            ]);
        }
        $pristup->forceFill(['zadnji_rad_at' => now()])->saveQuietly();

        return $response;
    }

    /** Opis izmjene iz Livewire poziva (npr. "Prskanje: novi zapis"), inače iz rute. */
    private function opis(Request $request): string
    {
        $dijelovi = [];
        foreach ((array) $request->input('components', []) as $c) {
            $snimka = json_decode($c['snapshot'] ?? '', true);
            $ime = $snimka['memo']['name'] ?? null;
            foreach ($c['calls'] ?? [] as $poziv) {
                $metoda = $poziv['method'] ?? '';
                if (str_starts_with($metoda, '$')) {
                    continue;
                }
                $dijelovi[] = (self::NAZIVI[$ime] ?? ucfirst((string) $ime)) . ': ' . (self::AKCIJE[$metoda] ?? $metoda);
            }
        }

        return $dijelovi ? implode(', ', array_unique($dijelovi)) : 'Izmjena (' . ($request->route()?->getName() ?? $request->path()) . ')';
    }
}
