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

    public function grupiraj(string $g): void
    {
        $this->grupa = in_array($g, ['parcela', 'kultura'], true) ? $g : 'parcela';
    }

    /** Redovi troška zbrojeni po parceli (€/ha na površinu parcele) ili po kulturi sa svih parcela (€/ha na hektare kulture). */
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
            'grupe' => $grupe, 'odDatum' => $od, 'doDatum' => $do, 'oznaka' => $this->oznakaRazdoblja(),
            'mjeseci' => array_values($mjeseci), 'vrh' => $vrh, 'korak' => $korak,
            'poVrsti' => $poVrsti, 'nabavaUkupno' => array_sum($poVrsti),
            'utroseno' => $utroseno, 'poHa' => $ha > 0 ? $utroseno / $ha : null,
            'haGrupe' => $haGrupe, 'poHaGrupe' => $haGrupe > 0 ? $utroseno / $haGrupe : null,
        ]);
    }
}
