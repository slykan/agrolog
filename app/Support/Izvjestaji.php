<?php

namespace App\Support;

use App\Models\Gnojidba;
use App\Models\Prskanje;
use App\Models\SkladisteUlaz;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/** Podaci za izvještaje i izvoz za razdoblje (od–do). */
class Izvjestaji
{
    /** Razdoblje iz upita (?od=Y-m-d&do=Y-m-d); zadano tekuća godina. */
    public static function razdoblje(Request $request): array
    {
        try {
            $od = $request->filled('od') ? Carbon::parse($request->query('od'))->startOfDay() : now()->startOfYear();
            $do = $request->filled('do') ? Carbon::parse($request->query('do'))->endOfDay() : now()->endOfYear();
        } catch (\Throwable) {
            [$od, $do] = [now()->startOfYear(), now()->endOfYear()];
        }

        return $do->lt($od) ? [$do->copy()->startOfDay(), $od->copy()->endOfDay()] : [$od, $do];
    }

    public static function prskanja(User $user, Carbon $od, Carbon $do): Collection
    {
        return Prskanje::where('prskanja.user_id', $user->id)
            ->where('prskanja.datum_tretiranja', '>=', $od->toDateString())
            ->where('prskanja.datum_tretiranja', '<', $do->copy()->addDay()->toDateString())
            ->join('kulture', 'kulture.id', '=', 'prskanja.kultura_id')
            ->join('parcele', 'parcele.id', '=', 'kulture.parcela_id')
            ->orderBy('prskanja.datum_tretiranja')->orderBy('prskanja.id')
            ->select('prskanja.*', 'kulture.naziv as kultura_naziv', 'kulture.eppo_naziv', 'kulture.eppo_kod', 'kulture.posadjena_povrsina_ha', 'parcele.arkod_broj', 'parcele.rucno_ucrtana')
            ->get()
            ->each(function ($p) {
                $p->povrsina = (float) ($p->tretirana_povrsina_ha ?? $p->posadjena_povrsina_ha);
                $p->ukupno = $p->kolicina_sredstva_l_ha !== null ? round((float) $p->kolicina_sredstva_l_ha * $p->povrsina, 3) : null;
            });
    }

    public static function gnojidbe(User $user, Carbon $od, Carbon $do): Collection
    {
        return Gnojidba::where('gnojidbe.user_id', $user->id)
            ->where('gnojidbe.datum', '>=', $od->toDateString())
            ->where('gnojidbe.datum', '<', $do->copy()->addDay()->toDateString())
            ->join('kulture', 'kulture.id', '=', 'gnojidbe.kultura_id')
            ->join('parcele', 'parcele.id', '=', 'kulture.parcela_id')
            ->orderBy('gnojidbe.datum')->orderBy('gnojidbe.id')
            ->select('gnojidbe.*', 'kulture.naziv as kultura_naziv', 'kulture.posadjena_povrsina_ha', 'parcele.arkod_broj', 'parcele.rucno_ucrtana')
            ->get()
            ->each(function ($g) {
                $g->povrsina = (float) $g->posadjena_povrsina_ha;
                $g->ukupno = round((float) $g->kolicina_kg_ha * $g->povrsina, 2);
                $g->kg_n = $g->kg_n_ha !== null ? round((float) $g->kg_n_ha * $g->povrsina, 2) : null;
            });
    }

    public static function berbe(User $user, Carbon $od, Carbon $do): Collection
    {
        return \App\Models\Berba::with('kultura.parcela')->where('user_id', $user->id)
            ->where('datum', '>=', $od->toDateString())->where('datum', '<', $do->copy()->addDay()->toDateString())
            ->orderBy('datum')->orderBy('id')->get();
    }

    /**
     * Promet skladišta u razdoblju: ulazi (nabava, inventura, prijenos) i potrošnja iz evidencije, s iznosom u €.
     *
     * @return Collection<int, array>
     */
    public static function prometSkladista(User $user, Carbon $od, Carbon $do): Collection
    {
        $odS = $od->toDateString();
        $doS = $do->copy()->addDay()->toDateString();
        $nazivi = $user->skladista()->pluck('naziv', 'id');
        $redovi = collect();

        foreach (SkladisteUlaz::where('user_id', $user->id)->whereNotNull('skladiste_id')->orderBy('datum')->orderBy('id')->get() as $u) {
            $d = $u->datum->toDateString();
            if ($d < $odS || $d >= $doS) {
                continue;
            }
            $redovi->push([
                'datum' => $u->datum, 'skladiste' => $nazivi[$u->skladiste_id] ?? '',
                'promet' => $u->prijenos_par ? 'Prijenos' : ($u->korekcija ? 'Inventura' : 'Nabava'),
                'artikl' => $u->naziv, 'kolicina' => (float) $u->kolicina, 'jedinica' => $u->jedinica,
                'iznos' => $u->cijena !== null ? (float) $u->cijena : null, 'parcela' => '', 'kultura' => '',
                'napomena' => trim(($u->dobavljac ? $u->dobavljac . ' ' : '') . ($u->napomena ?? '')),
            ]);
        }

        foreach (Skladiste::stanje($user, true) as $a) {
            $cijena = $a['placena_kolicina'] > 0 ? $a['placeno'] / $a['placena_kolicina'] : null;
            foreach ($a['potrosnja'] as $p) {
                $d = $p['datum']->toDateString();
                if ($d < $odS || $d >= $doS) {
                    continue;
                }
                $redovi->push([
                    'datum' => $p['datum'], 'skladiste' => $a['skladiste'], 'promet' => $a['vrsta'] === 'szb' ? 'Potrošnja (prskanje)' : 'Potrošnja (gnojidba)',
                    'artikl' => $a['naziv'], 'kolicina' => -$p['kolicina'], 'jedinica' => $a['jedinica'],
                    'iznos' => $cijena !== null ? -round($p['kolicina'] * $cijena, 2) : null, 'parcela' => $p['arkod'], 'kultura' => $p['kultura'],
                    'napomena' => Skladiste::broj($p['doza'], 3) . ' ' . $a['jedinica'] . '/ha × ' . Skladiste::broj($p['povrsina']) . ' ha',
                ]);
            }
        }

        return $redovi->sortBy(fn ($r) => $r['datum']->format('Y-m-d'))->values();
    }

    /** Broj za CSV s decimalnim zarezom (Excel u hrvatskim postavkama). */
    public static function csvBroj($v, int $dec = 3): string
    {
        if ($v === null || $v === '') {
            return '';
        }
        $s = number_format((float) $v, $dec, ',', '');

        return str_contains($s, ',') ? rtrim(rtrim($s, '0'), ',') : $s;
    }
}
