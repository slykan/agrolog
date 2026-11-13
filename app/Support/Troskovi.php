<?php

namespace App\Support;

use App\Models\Kultura;
use App\Models\OpciTrosak;
use App\Models\Rad;
use App\Models\SkladisteUlaz;
use App\Models\StrojRok;
use App\Models\StrojServis;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Troškovi iz skladišta: nabava (ulazi s cijenom) i utrošak (potrošnja iz prskanja i gnojidbe × prosječna cijena po jedinici).
 * Prijenosi između skladišta i inventure nisu nabava. Utrošak po kulturi = zbroj potrošnje svih zapisa te kulture u razdoblju,
 * plus ostali radovi i opći troškovi vezani uz kulturu. Opći troškovi bez kulture ulaze samo u ukupni trošak.
 */
class Troskovi
{
    /** Trošak kulture po vrsti: zaštita, gnojivo, ostali radovi, opći troškovi vezani uz kulturu. */
    private const PRAZNO = ['szb' => 0.0, 'gnojivo' => 0.0, 'radovi' => 0.0, 'ostalo' => 0.0, 'gorivo' => 0.0];

    /**
     * Troškovi mehanizacije u razdoblju: servisi (datum servisa) i iznosi uz rokove — registracija, osiguranje,
     * tehnički, pregled… (datum plaćanja = početak valjanosti, tj. datum isteka minus razdoblje roka).
     *
     * @return array{stavke: \Illuminate\Support\Collection, po_stroju: \Illuminate\Support\Collection, ukupno: float}
     */
    public static function mehanizacija(User $user, Carbon $od, Carbon $do): array
    {
        $odS = $od->toDateString();
        $doS = $do->copy()->addDay()->toDateString();
        $stavke = collect();

        foreach (StrojServis::with('stroj')->where('user_id', $user->id)->whereNotNull('trosak')->get() as $s) {
            $d = $s->datum->toDateString();
            if ($d >= $odS && $d < $doS) {
                $stavke->push(['datum' => $s->datum, 'stroj_id' => $s->stroj_id, 'stroj' => $s->stroj?->naziv, 'vrsta' => 'servis',
                    'opis' => 'Servis: ' . $s->opis, 'iznos' => (float) $s->trosak]);
            }
        }
        foreach (StrojRok::with('stroj')->where('user_id', $user->id)->whereNotNull('iznos')->get() as $r) {
            $placeno = $r->datum_isteka->copy()->subMonthsNoOverflow(Mehanizacija::ROKOVI[$r->vrsta][1] ?? 12);
            $d = $placeno->toDateString();
            if ($d >= $odS && $d < $doS) {
                $stavke->push(['datum' => $placeno, 'stroj_id' => $r->stroj_id, 'stroj' => $r->stroj?->naziv, 'vrsta' => 'rok',
                    'opis' => $r->opis . ' (vrijedi do ' . $r->datum_isteka->format('d.m.Y.') . ')', 'iznos' => (float) $r->iznos]);
            }
        }

        $stavke = $stavke->sortBy(fn ($s) => $s['datum']->format('Y-m-d'))->values();
        $poStroju = $stavke->groupBy('stroj_id')->map(fn ($g) => [
            'stroj' => $g->first()['stroj'],
            'servisi' => round($g->where('vrsta', 'servis')->sum('iznos'), 2),
            'rokovi' => round($g->where('vrsta', 'rok')->sum('iznos'), 2),
            'ukupno' => round($g->sum('iznos'), 2),
        ])->sortByDesc('ukupno')->values();

        return ['stavke' => $stavke, 'po_stroju' => $poStroju, 'ukupno' => round($stavke->sum('iznos'), 2)];
    }

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
                $poKulturi[$k] ??= self::PRAZNO;
                $poKulturi[$k][$a['vrsta']] += $iznos;
                $art = $a['vrsta'] . '|' . Skladiste::kljuc($a['naziv']);
                $poArtiklu[$art] ??= ['naziv' => $a['naziv'], 'vrsta' => $a['vrsta'], 'jedinica' => $a['jedinica'], 'kolicina' => 0.0, 'iznos' => 0.0];
                $poArtiklu[$art]['kolicina'] += $p['kolicina'];
                $poArtiklu[$art]['iznos'] += $iznos;
            }
        }

        // ostali radovi i opći troškovi vezani uz kulturu
        foreach (Rad::where('user_id', $user->id)->whereNotNull('trosak')->where('datum', '>=', $odS)->where('datum', '<', $doS)->get() as $r) {
            $poKulturi[$r->kultura_id] ??= self::PRAZNO;
            $poKulturi[$r->kultura_id]['radovi'] += (float) $r->trosak;
        }
        $opci = OpciTrosak::where('user_id', $user->id)->where('datum', '>=', $odS)->where('datum', '<', $doS)->get();
        foreach ($opci->whereNotNull('kultura_id') as $o) {
            $poKulturi[$o->kultura_id] ??= self::PRAZNO;
            $poKulturi[$o->kultura_id]['ostalo'] += (float) $o->iznos;
        }
        // gorivo po danu, podijeljeno na poslove tog dana po hektarima
        $gorivo = Gorivo::raspodjela($user, $odS, $doS);
        foreach ($gorivo['po_kulturi'] as $kid => $eur) {
            $poKulturi[$kid] ??= self::PRAZNO;
            $poKulturi[$kid]['gorivo'] += $eur;
        }

        $kulture = Kultura::with('parcela')->whereIn('id', array_keys($poKulturi))->get()->keyBy('id');
        $redovi = collect($poKulturi)->map(function ($t, $id) use ($kulture) {
            $k = $kulture[$id] ?? null;
            $ha = (float) ($k?->posadjena_povrsina_ha ?? 0);
            $ukupno = $t['szb'] + $t['gnojivo'] + $t['radovi'] + $t['ostalo'] + $t['gorivo'];

            return [
                'kultura_id' => $id, 'parcela_id' => $k?->parcela_id, 'parcela_ha' => (float) ($k?->parcela?->povrsina_ha ?? 0),
                'arkod' => $k?->parcela?->arkod_broj, 'kultura' => $k?->naziv, 'godina' => $k?->godina, 'ha' => $ha,
                'szb' => round($t['szb'], 2), 'gnojivo' => round($t['gnojivo'], 2), 'radovi' => round($t['radovi'] + $t['ostalo'], 2),
                'gorivo' => round($t['gorivo'], 2),
                'ukupno' => round($ukupno, 2),
                'po_ha' => $ha > 0 ? round($ukupno / $ha, 2) : null,
            ];
        })->sortByDesc('ukupno')->values();

        return [
            'nabava' => $nabava,
            'opci_ukupno' => round((float) $opci->sum('iznos'), 2),
            'opci_nevezano' => round((float) $opci->whereNull('kultura_id')->sum('iznos'), 2),
            'gorivo_ukupno' => $gorivo['ukupno'],
            'gorivo_nevezano' => round($gorivo['nevezano'], 2),
            'redovi' => $redovi,
            'artikli' => collect($poArtiklu)->sortByDesc('iznos')->values(),
            'bez_cijene' => $bezCijene,
            'ima_skladiste' => $user->skladista()->exists(),
            'ima_cijena' => $kupljeno->whereNotNull('cijena')->isNotEmpty(),
        ];
    }
}
