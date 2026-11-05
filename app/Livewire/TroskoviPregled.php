<?php

namespace App\Livewire;

use App\Livewire\Concerns\Razdoblje;
use App\Support\Troskovi;
use Livewire\Component;

/** Troškovi: nabava iz skladišta po mjesecima i vrsti te utrošak (prskanje, gnojidba) po parceli i kulturi. */
class TroskoviPregled extends Component
{
    use Razdoblje;

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

        $utroseno = (float) $t['redovi']->sum('ukupno');
        $ha = (float) $t['redovi']->sum('ha');

        return view('livewire.troskovi-pregled', $t + [
            'odDatum' => $od, 'doDatum' => $do, 'oznaka' => $this->oznakaRazdoblja(),
            'mjeseci' => array_values($mjeseci), 'vrh' => $vrh, 'korak' => $korak,
            'poVrsti' => $poVrsti, 'nabavaUkupno' => array_sum($poVrsti),
            'utroseno' => $utroseno, 'poHa' => $ha > 0 ? $utroseno / $ha : null,
        ]);
    }
}
