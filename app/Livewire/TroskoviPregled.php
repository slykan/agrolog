<?php

namespace App\Livewire;

use App\Livewire\Concerns\Razdoblje;
use App\Support\Troskovi;
use Livewire\Component;

/** Troškovi: nabava iz skladišta po mjesecima i vrsti te utrošak (prskanje, gnojidba) po parceli i kulturi. */
class TroskoviPregled extends Component
{
    use Razdoblje;

    /** Trošak po: parcela | kultura */
    public string $grupa = 'parcela';

    /** Opći troškovi se upisuju u zasebnoj komponenti na istoj stranici. */
    #[\Livewire\Attributes\On('troskovi-promijenjeni')]
    public function osvjezi(): void
    {
    }

    public function grupiraj(string $g): void
    {
        $this->grupa = in_array($g, ['parcela', 'kultura'], true) ? $g : 'parcela';
    }

    /** Redovi troška zbrojeni po parceli (€/ha na površinu parcele) ili po kulturi sa svih parcela (€/ha na hektare kulture). */
    /**
     * Prihod i dobit po kulturi: kulture s utroškom u razdoblju, sa žetvom u razdoblju ili s upisanim prinosom/troškovima
     * u godinama razdoblja. Trošak = utrošak iz evidencije + sjeme + ostali troškovi; prihod = prinos × ha × cijena + potpore.
     */
    private function dobitPoKulturi($redovi, $od, $do)
    {
        $utrosak = $redovi->keyBy('kultura_id');
        $berbe = \App\Support\Berbe::uRazdoblju(auth()->user(), $od, $do);
        $saBerbama = \App\Models\Berba::where('user_id', auth()->id())->distinct()->pluck('kultura_id')->flip();
        $nasadi = \App\Models\NasadGodina::whereBetween('godina', [$od->year, $do->year])->pluck('kultura_id');
        $kulture = \App\Models\Kultura::with('parcela')->where('user_id', auth()->id())
            ->where(function ($q) use ($utrosak, $od, $do, $berbe, $nasadi) {
                $q->whereIn('id', $utrosak->keys())
                  ->orWhereIn('id', $berbe->keys())
                  ->orWhereIn('id', $nasadi)
                  ->orWhereBetween('zetva_datum', [$od->toDateString(), $do->toDateString()])
                  ->orWhere(fn ($q) => $q->whereNull('zetva_datum')->whereBetween('godina', [$od->year, $do->year])
                      ->where(fn ($q) => $q->whereNotNull('prinos_t_ha')->orWhereNotNull('trosak_sjeme')->orWhereNotNull('trosak_ostalo')->orWhereNotNull('potpora_ha')));
            })->get();

        return $kulture->map(function ($k) use ($utrosak, $berbe, $saBerbama, $od, $do) {
            $u = $utrosak[$k->id] ?? null;
            $b = $berbe[$k->id] ?? null;
            $pr = \App\Support\Berbe::prihodKulture($k, $b, isset($saBerbama[$k->id]), $od, $do);
            $trosak = (float) ($u['ukupno'] ?? 0) + $pr['ostalo'];

            return [
                'kultura_id' => $k->id, 'parcela_id' => $k->parcela_id, 'parcela_ha' => (float) ($k->parcela?->povrsina_ha ?? 0),
                'arkod' => $k->parcela?->arkod_broj, 'kultura' => $k->naziv, 'godina' => $k->godina, 'ha' => (float) $k->posadjena_povrsina_ha,
                'trosak' => round($trosak, 2), 'prihod' => $pr['prihod'], 'berbe_bez_cijene' => $b['bez_cijene'] ?? 0,
            ];
        });
    }

    /** Dobit zbrojena po parceli ili kulturi (isti izbor kao trošak). */
    private function grupeDobiti($redovi)
    {
        $po = $this->grupa === 'kultura'
            ? $redovi->groupBy(fn ($r) => mb_strtolower(trim((string) $r['kultura'])))
            : $redovi->groupBy('parcela_id');

        return $po->map(function ($g) {
            $prvi = $g->first();
            $ha = $this->grupa === 'kultura' ? (float) $g->sum('ha') : (float) $prvi['parcela_ha'];
            $imaPrihod = $g->contains(fn ($r) => $r['prihod'] !== null);
            $prihod = (float) $g->sum(fn ($r) => $r['prihod'] ?? 0);
            $trosak = (float) $g->sum('trosak');
            $dobit = $imaPrihod ? $prihod - $trosak : null;

            return [
                'naziv' => $this->grupa === 'kultura' ? $prvi['kultura'] : $prvi['arkod'],
                'opis' => $this->grupa === 'kultura'
                    ? $g->pluck('arkod')->unique()->implode(', ')
                    : $g->map(fn ($r) => $r['kultura'] . ' ' . $r['godina'] . '.')->unique()->implode(', '),
                'ha' => $ha, 'trosak' => round($trosak, 2), 'prihod' => $imaPrihod ? round($prihod, 2) : null,
                'dobit' => $dobit !== null ? round($dobit, 2) : null,
                'dobit_ha' => $dobit !== null && $ha > 0 ? round($dobit / $ha, 2) : null,
                'bez_prihoda' => $g->filter(fn ($r) => $r['prihod'] === null)->count(),
                'berbe_bez_cijene' => (int) $g->sum('berbe_bez_cijene'),
            ];
        })->sortByDesc(fn ($r) => $r['dobit'] ?? -INF)->values();
    }

    private function grupe($redovi)
    {
        $po = $this->grupa === 'kultura'
            ? $redovi->groupBy(fn ($r) => mb_strtolower(trim((string) $r['kultura'])))
            : $redovi->groupBy('parcela_id');

        return $po->map(function ($g) {
            $prvi = $g->first();
            $ha = $this->grupa === 'kultura' ? (float) $g->sum('ha') : (float) $prvi['parcela_ha'];
            $ukupno = (float) $g->sum('ukupno');

            return [
                'naziv' => $this->grupa === 'kultura' ? $prvi['kultura'] : $prvi['arkod'],
                'opis' => $this->grupa === 'kultura'
                    ? $g->pluck('arkod')->unique()->implode(', ')
                    : $g->map(fn ($r) => $r['kultura'] . ' ' . $r['godina'] . '.')->unique()->implode(', '),
                'ha' => $ha, 'szb' => round((float) $g->sum('szb'), 2), 'gnojivo' => round((float) $g->sum('gnojivo'), 2),
                'radovi' => round((float) $g->sum('radovi'), 2),
                'ukupno' => round($ukupno, 2), 'po_ha' => $ha > 0 ? round($ukupno / $ha, 2) : null,
            ];
        })->sortByDesc('ukupno')->values();
    }

    public function render()
    {
        [$od, $do] = $this->granice();
        $t = Troskovi::izracun(auth()->user(), $od, $do);

        $mjeseci = $this->mjeseciRazdoblja($od, $do, ['szb' => 0.0, 'gnojivo' => 0.0]);
        foreach ($t['nabava'] as $u) {
            $k = $u->datum->format('Y-m');
            if (isset($mjeseci[$k])) {
                $mjeseci[$k][$u->vrsta] += (float) $u->cijena;
            }
        }
        $poVrsti = ['szb' => (float) $t['nabava']->where('vrsta', 'szb')->sum('cijena'), 'gnojivo' => (float) $t['nabava']->where('vrsta', 'gnojivo')->sum('cijena')];
        [$vrh, $korak] = $this->ljestvica(collect($mjeseci)->max(fn ($m) => $m['szb'] + $m['gnojivo']));

        $grupe = $this->grupe($t['redovi']);
        $utroseno = (float) $t['redovi']->sum('ukupno');
        $ha = (float) $t['redovi']->sum('ha');        // za karticu: hektari kultura s troškom
        $haGrupe = (float) $grupe->sum('ha');

        return view('livewire.troskovi-pregled', $t + [
            'meh' => Troskovi::mehanizacija(auth()->user(), $od, $do),
            'dobit' => $this->grupeDobiti($this->dobitPoKulturi($t['redovi'], $od, $do)),
            'grupe' => $grupe, 'odDatum' => $od, 'doDatum' => $do, 'oznaka' => $this->oznakaRazdoblja(),
            'mjeseci' => array_values($mjeseci), 'vrh' => $vrh, 'korak' => $korak,
            'poVrsti' => $poVrsti, 'nabavaUkupno' => array_sum($poVrsti),
            'utroseno' => $utroseno, 'poHa' => $ha > 0 ? $utroseno / $ha : null,
            'haGrupe' => $haGrupe, 'poHaGrupe' => $haGrupe > 0 ? $utroseno / $haGrupe : null,
        ]);
    }
}
