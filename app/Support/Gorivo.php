<?php

namespace App\Support;

use App\Models\Gnojidba;
use App\Models\GorivoDan;
use App\Models\Parcela;
use App\Models\Prskanje;
use App\Models\Rad;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Gorivo po danu. Korisnik upiše koliko je natočio za taj dan, a trošak se dijeli na poslove tog dana po hektarima.
 * Posao = vrsta + kultura (više sredstava u istom prskanju iste kulture je jedan prolaz). Raspodjela se ne sprema:
 * posao upisan kasnije sam ulazi u raspodjelu, a izuzeti poslovi (kvačica) pamte se u gorivo_dani.izuzeto.
 * Ostali radovi ulaze samo ako su vlastiti i bez vlastitog upisa goriva (inače bi se gorivo brojalo dvaput).
 */
class Gorivo
{
    public const BOJA = '#c2410c';

    private const NAZIVI = ['prskanje' => 'Prskanje', 'gnojidba' => 'Gnojidba', 'rad' => 'Rad'];

    /**
     * Poslovi u razdoblju (datumi uključivo), grupirani po datumu (Y-m-d).
     * Svaki posao: kljuc, vrsta, naziv, kultura_id, kultura, parcela, ha, detalj.
     *
     * @return Collection<string, Collection>
     */
    public static function poslovi(int $uid, string $od, string $do): Collection
    {
        $doIskl = \Illuminate\Support\Carbon::parse($do)->addDay()->toDateString();   // datumi mogu biti spremljeni s vremenom (sqlite)
        $u = fn (string $col) => fn ($q) => $q->where($col, '>=', $od)->where($col, '<', $doIskl);
        $sa = ['kultura:id,parcela_id,naziv,posadjena_povrsina_ha', 'kultura.parcela:id,arkod_broj,rucno_ucrtana'];
        $redovi = collect();

        foreach (Prskanje::with($sa)->where('user_id', $uid)->where($u('datum_tretiranja'))->get() as $p) {
            $redovi->push(['datum' => $p->datum_tretiranja->format('Y-m-d'), 'vrsta' => 'prskanje', 'zapis' => $p,
                'ha' => $p->tretirana_povrsina_ha, 'detalj' => $p->trgovacki_naziv_sredstva]);
        }
        foreach (Gnojidba::with($sa)->where('user_id', $uid)->where($u('datum'))->get() as $g) {
            $redovi->push(['datum' => $g->datum->format('Y-m-d'), 'vrsta' => 'gnojidba', 'zapis' => $g, 'ha' => null, 'detalj' => $g->tip_gnojiva]);
        }
        foreach (Rad::with($sa)->where('user_id', $uid)->where('izvedba', 'vlastito')->whereNull('gorivo_l')->where($u('datum'))->get() as $r) {
            $redovi->push(['datum' => $r->datum->format('Y-m-d'), 'vrsta' => 'rad', 'zapis' => $r, 'ha' => $r->povrsina_ha, 'detalj' => $r->vrsta]);
        }

        return $redovi->filter(fn ($r) => $r['zapis']->kultura)
            ->groupBy('datum')
            ->map(fn ($dan) => $dan->groupBy(fn ($r) => $r['vrsta'] . ':' . $r['zapis']->kultura_id)
                ->map(function ($g, $kljuc) {
                    $prvi = $g->first();
                    $k = $prvi['zapis']->kultura;
                    $ha = (float) ($g->max(fn ($r) => (float) $r['ha']) ?: $k->posadjena_povrsina_ha);

                    return [
                        'kljuc' => $kljuc, 'vrsta' => $prvi['vrsta'], 'naziv' => self::NAZIVI[$prvi['vrsta']],
                        'kultura_id' => $k->id, 'kultura' => $k->naziv,
                        'parcela' => $k->parcela ? Parcela::oznaka($k->parcela->arkod_broj, $k->parcela->rucno_ucrtana) : null,
                        'ha' => round($ha, 2),
                        'detalj' => $g->pluck('detalj')->filter()->unique()->implode(', '),
                    ];
                })->sortBy(fn ($p) => array_search($p['vrsta'], array_keys(self::NAZIVI)) . ' ' . $p['parcela'])->values());
    }

    /** Poslovi jednog dana. */
    public static function posloviDana(int $uid, string $datum): Collection
    {
        return self::poslovi($uid, $datum, $datum)->get($datum, collect());
    }

    /**
     * Iznos podijeljen na uključene poslove po hektarima (bez hektara: jednako); zadnji dobiva ostatak da zbroj štima.
     *
     * @return array<string, float> ključ posla => €
     */
    public static function rasporedi(float $iznos, Collection $poslovi, array $izuzeto = []): array
    {
        $ukljuceni = $poslovi->reject(fn ($p) => in_array($p['kljuc'], $izuzeto, true))->values();
        if ($ukljuceni->isEmpty()) {
            return [];
        }
        $ukupnoHa = (float) $ukljuceni->sum('ha');
        $out = [];
        $ostatak = round($iznos, 2);
        foreach ($ukljuceni as $i => $p) {
            $dio = $i === $ukljuceni->count() - 1 ? $ostatak
                : round($iznos * ($ukupnoHa > 0 ? $p['ha'] / $ukupnoHa : 1 / $ukljuceni->count()), 2);
            $out[$p['kljuc']] = $dio;
            $ostatak = round($ostatak - $dio, 2);
        }

        return $out;
    }

    /**
     * Gorivo u razdoblju [od, do) po kulturi; dani bez ijednog uključenog posla idu u "nevezano" (ulaze samo u ukupni trošak).
     *
     * @return array{po_kulturi: array<int, float>, nevezano: float, ukupno: float}
     */
    public static function raspodjela(User $user, string $odS, string $doS): array
    {
        $dani = GorivoDan::where('user_id', $user->id)->where('datum', '>=', $odS)->where('datum', '<', $doS)->get();
        $rez = ['po_kulturi' => [], 'nevezano' => 0.0, 'ukupno' => round((float) $dani->sum('iznos'), 2)];
        if ($dani->isEmpty()) {
            return $rez;
        }

        $poslovi = self::poslovi($user->id, $dani->min('datum')->format('Y-m-d'), $dani->max('datum')->format('Y-m-d'));
        foreach ($dani as $dan) {
            $dio = self::rasporedi((float) $dan->iznos, $poslovi->get($dan->datum->format('Y-m-d'), collect()), $dan->izuzeto ?? []);
            if (! $dio) {
                $rez['nevezano'] += (float) $dan->iznos;
                continue;
            }
            foreach ($dio as $kljuc => $eur) {
                $kid = (int) explode(':', $kljuc)[1];
                $rez['po_kulturi'][$kid] = ($rez['po_kulturi'][$kid] ?? 0) + $eur;
            }
        }

        return $rez;
    }

    /** Zadnja upisana cijena goriva (€/L), iz goriva po danu ili iz ostalih radova. */
    public static function zadnjaCijena(int $uid): ?float
    {
        $dan = GorivoDan::where('user_id', $uid)->whereNotNull('cijena_l')->latest('datum')->latest('id')->first(['datum', 'cijena_l']);
        $rad = Rad::where('user_id', $uid)->whereNotNull('cijena_goriva')->latest('datum')->latest('id')->first(['datum', 'cijena_goriva']);
        if ($dan && (! $rad || $dan->datum->gte($rad->datum))) {
            return (float) $dan->cijena_l;
        }

        return $rad ? (float) $rad->cijena_goriva : null;
    }
}
