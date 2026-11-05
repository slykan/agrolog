<?php

namespace App\Support;

use App\Models\Kultura;
use App\Models\SkladisteUlaz;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Troškovi iz skladišta: nabava (ulazi s cijenom) i utrošak (potrošnja iz prskanja i gnojidbe × prosječna cijena po jedinici).
 * Prijenosi između skladišta i inventure nisu nabava. Utrošak po kulturi = zbroj potrošnje svih zapisa te kulture u razdoblju.
 */
class Troskovi
{
    public static function izracun(User $user, Carbon $od, Carbon $do): array
    {
        $odS = $od->toDateString();
        $doS = $do->copy()->addDay()->toDateString();

        $kupljeno = SkladisteUlaz::where('user_id', $user->id)->where('korekcija', false)->whereNull('prijenos_par')->whereNotNull('skladiste_id')->get();

        // nabava u razdoblju (samo ulazi s cijenom)
        $nabava = $kupljeno->filter(fn ($u) => $u->cijena !== null && $u->datum->toDateString() >= $odS && $u->datum->toDateString() < $doS)->values();

        // rezervna cijena po artiklu (svi ulazi s cijenom, sva skladišta) kad skladište zapisa nema cijenu
        $rezervna = $kupljeno->filter(fn ($u) => $u->cijena !== null && (float) $u->kolicina > 0)
            ->groupBy(fn ($u) => $u->vrsta . '|' . Skladiste::kljuc($u->naziv))
            ->map(fn ($g) => $g->sum('cijena') / max($g->sum(fn ($u) => (float) $u->kolicina), 0.001));

        // utrošak po kulturi
        $poKulturi = [];
        $poArtiklu = [];
        $bezCijene = 0;
        foreach (Skladiste::stanje($user, true) as $a) {
            $cijena = $a['placena_kolicina'] > 0 ? $a['placeno'] / $a['placena_kolicina']   // nezaokružena prosječna cijena
                : ($rezervna[$a['vrsta'] . '|' . Skladiste::kljuc($a['naziv'])] ?? null);
            foreach ($a['potrosnja'] as $p) {
                $d = $p['datum']->toDateString();
                if ($d < $odS || $d >= $doS) {
                    continue;
                }
                if ($cijena === null) {
                    $bezCijene++;
                    continue;
                }
                $iznos = $p['kolicina'] * $cijena;
                $k = $p['kultura_id'];
                $poKulturi[$k] ??= ['szb' => 0.0, 'gnojivo' => 0.0];
                $poKulturi[$k][$a['vrsta']] += $iznos;
                $art = $a['vrsta'] . '|' . Skladiste::kljuc($a['naziv']);
                $poArtiklu[$art] ??= ['naziv' => $a['naziv'], 'vrsta' => $a['vrsta'], 'jedinica' => $a['jedinica'], 'kolicina' => 0.0, 'iznos' => 0.0];
                $poArtiklu[$art]['kolicina'] += $p['kolicina'];
                $poArtiklu[$art]['iznos'] += $iznos;
            }
        }

        $kulture = Kultura::with('parcela')->whereIn('id', array_keys($poKulturi))->get()->keyBy('id');
        $redovi = collect($poKulturi)->map(function ($t, $id) use ($kulture) {
            $k = $kulture[$id] ?? null;
            $ha = (float) ($k?->posadjena_povrsina_ha ?? 0);
            $ukupno = $t['szb'] + $t['gnojivo'];

            return [
                'kultura_id' => $id, 'arkod' => $k?->parcela?->arkod_broj, 'kultura' => $k?->naziv, 'godina' => $k?->godina, 'ha' => $ha,
                'szb' => round($t['szb'], 2), 'gnojivo' => round($t['gnojivo'], 2), 'ukupno' => round($ukupno, 2),
                'po_ha' => $ha > 0 ? round($ukupno / $ha, 2) : null,
            ];
        })->sortByDesc('ukupno')->values();

        return [
            'nabava' => $nabava,
            'redovi' => $redovi,
            'artikli' => collect($poArtiklu)->sortByDesc('iznos')->values(),
            'bez_cijene' => $bezCijene,
            'ima_skladiste' => $user->skladista()->exists(),
            'ima_cijena' => $kupljeno->whereNotNull('cijena')->isNotEmpty(),
        ];
    }
}
