<?php

namespace App\Support;

use App\Models\Berba;
use App\Models\Kultura;
use App\Models\NasadGodina;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/** Zbrojevi berbi i prihod kulture za razdoblje. */
class Berbe
{
    /** Masa za prikaz: do tone u kg, od tone u t. */
    public static function masa(float $kg): string
    {
        return $kg >= 1000
            ? number_format($kg / 1000, 2, ',', '.') . ' t'
            : number_format($kg, $kg == floor($kg) ? 0 : 2, ',', '.') . ' kg';
    }

    /** Po kulturi: kg, iznos (samo berbe s cijenom), broj berbi i broj berbi bez cijene u razdoblju. */
    public static function uRazdoblju(User $user, Carbon $od, Carbon $do): Collection
    {
        return Berba::where('user_id', $user->id)
            ->where('datum', '>=', $od->toDateString())->where('datum', '<', $do->copy()->addDay()->toDateString())
            ->get()->groupBy('kultura_id')
            ->map(fn (Collection $g) => [
                'kg' => (float) $g->sum('kolicina_kg'),
                'iznos' => round((float) $g->sum(fn ($b) => $b->iznos ?? 0), 2),
                'broj' => $g->count(),
                'bez_cijene' => $g->whereNull('cijena_kg')->count(),
            ]);
    }

    /**
     * Prihod i dodatni troškovi kulture u razdoblju.
     * Prodaja: ako kultura ima berbe, računa se iz berbi u razdoblju; inače iz ručno upisanog prinosa.
     * Trajni nasad: potpore i ostali troškovi uzimaju se po godinama, a ručni podaci kulture samo za godinu unosa.
     *
     * @return array{prihod: ?float, ostalo: float}
     */
    public static function prihodKulture(Kultura $k, ?array $berbe, bool $imaBerbe, Carbon $od, Carbon $do): array
    {
        $ha = (float) $k->posadjena_povrsina_ha;
        $uGodini = $k->godina >= $od->year && $k->godina <= $do->year;

        $prodaja = null;
        if ($imaBerbe) {
            $prodaja = $berbe && $berbe['broj'] > $berbe['bez_cijene'] ? $berbe['iznos'] : null;
        } elseif ($k->prinos_t_ha !== null && $k->cijena_t !== null && (! $k->trajni || $uGodini)) {
            $prodaja = (float) $k->prinos_t_ha * $ha * (float) $k->cijena_t;
        }

        $potporaHa = null;
        $ostalo = 0.0;
        if (! $k->trajni || $uGodini) {
            $potporaHa = $k->potpora_ha !== null ? (float) $k->potpora_ha : null;
            $ostalo = $k->ostaliTroskovi();
        }
        if ($k->trajni) {
            foreach (NasadGodina::where('kultura_id', $k->id)->whereBetween('godina', [$od->year, $do->year])->get() as $g) {
                if ($g->potpora_ha !== null) {
                    $potporaHa = (float) $potporaHa + (float) $g->potpora_ha;
                }
                $ostalo += (float) $g->trosak_ostalo;
            }
        }

        $potpora = $potporaHa !== null ? $potporaHa * $ha : null;

        return [
            'prihod' => $prodaja === null && $potpora === null ? null : round(($prodaja ?? 0) + ($potpora ?? 0), 2),
            'ostalo' => round($ostalo, 2),
        ];
    }
}
