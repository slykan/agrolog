<?php

namespace App\Livewire;

use App\Livewire\Concerns\Razdoblje;
use App\Support\Izvjestaji;
use Livewire\Component;

/** Izvještaji i izvoz za razdoblje: evidencija prskanja i gnojidbe (ispis/PDF, Excel, CSV) i promet skladišta. */
class IzvjestajiPregled extends Component
{
    use Razdoblje;

    public function render()
    {
        [$od, $do] = $this->granice();
        $user = auth()->user();
        $prskanja = Izvjestaji::prskanja($user, $od, $do);
        $gnojidbe = Izvjestaji::gnojidbe($user, $od, $do);
        $imaSkladiste = $user->skladista()->exists();

        return view('livewire.izvjestaji-pregled', [
            'odDatum' => $od, 'doDatum' => $do,
            'upit' => ['od' => $od->toDateString(), 'do' => $do->toDateString()],
            'brojPrskanja' => $prskanja->count(), 'brojGnojidbi' => $gnojidbe->count(),
            'sredstva' => (float) $prskanja->sum('ukupno'),
            'gnojiva' => (float) $gnojidbe->sum('ukupno'),
            'dusik' => (float) $gnojidbe->sum('kg_n'),
            'brojBerbi' => Izvjestaji::berbe($user, $od, $do)->count(),
            'imaSkladiste' => $imaSkladiste,
            'brojPrometa' => $imaSkladiste ? Izvjestaji::prometSkladista($user, $od, $do)->count() : 0,
        ]);
    }
}
