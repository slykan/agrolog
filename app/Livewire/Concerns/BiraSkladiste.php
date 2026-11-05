<?php

namespace App\Livewire\Concerns;

use App\Models\Skladiste as SkladisteModel;
use App\Support\Skladiste;

/** Izbor skladišta iz kojeg zapis prskanja ili gnojidbe troši (samo kad korisnik ima skladišta). */
trait BiraSkladiste
{
    public function getSkladistaProperty()
    {
        return Skladiste::skladista(auth()->user());
    }

    /** Skladište za spremanje: izabrano (ako je korisnikovo), inače zadano; null ako korisnik nema skladište. */
    private function skladisteZaSpremanje($id): ?int
    {
        if (filled($id) && SkladisteModel::where('user_id', auth()->id())->whereKey((int) $id)->exists()) {
            return (int) $id;
        }

        return Skladiste::zadanoId(auth()->user());
    }
}
