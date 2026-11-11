<?php

namespace App\Livewire\Concerns;

use App\Models\PlaniraniRad;

/**
 * Unos iz kalendara: ?datum=Y-m-d otvara formu s tim datumom, ?plan=ID još i popuni kulturu i opis iz plana.
 * Plan se zatvara tek kad se zapis spremi.
 */
trait IzKalendara
{
    public ?int $planId = null;
    public string $planOpis = '';

    /** Popuni formu; vraća plan ili null. True u $otvori kad je nešto preuzeto i forma se treba otvoriti. */
    protected function izKalendara(string $vrsta, string $poljeDatuma, ?bool &$otvori = null): ?PlaniraniRad
    {
        $otvori = false;
        $plan = ($id = request()->integer('plan'))
            ? PlaniraniRad::where('id', $id)->where('user_id', auth()->id())->where('vrsta', $vrsta)->otvoreni()->first()
            : null;

        // obavljeno se upisuje najkasnije s današnjim datumom
        $datum = $plan ? min($plan->datum->format('Y-m-d'), date('Y-m-d')) : request()->query('datum');
        if (is_string($datum) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $datum) && checkdate((int) substr($datum, 5, 2), (int) substr($datum, 8, 2), (int) substr($datum, 0, 4))) {
            $this->form[$poljeDatuma] = $datum;
            $otvori = true;
        }
        if ($plan) {
            $this->planId = $plan->id;
            $this->planOpis = $plan->naslov() . ($plan->napomena ? ' · ' . $plan->napomena : '');
            if ($plan->kultura_id) {
                $this->form['kultura_id'] = $plan->kultura_id;
            }
        }

        return $plan;
    }

    protected function zatvoriPlan(string $vrsta, int $zapisId): void
    {
        if ($this->planId) {
            PlaniraniRad::where('id', $this->planId)->where('user_id', auth()->id())->where('vrsta', $vrsta)->otvoreni()
                ->update(['obavljeno_at' => now(), 'zapis_vrsta' => $vrsta, 'zapis_id' => $zapisId]);
        }
        $this->planId = null;
        $this->planOpis = '';
    }
}
