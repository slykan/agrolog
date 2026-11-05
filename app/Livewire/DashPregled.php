<?php

namespace App\Livewire;

use App\Models\Gnojidba;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\Prskanje;
use App\Livewire\Concerns\Razdoblje;
use Illuminate\Support\Carbon;
use Livewire\Component;

/** Pregled gospodarstva na početnoj: aktivnosti (prskanje, gnojidba) po mjesecima za izabrano razdoblje i usporedba s godinom ranije. */
class DashPregled extends Component
{
    use Razdoblje;

    private function aktivnosti(Carbon $od, Carbon $do): array
    {
        $uid = auth()->id();

        return [
            'prskanje' => Prskanje::where('user_id', $uid)->where('datum_tretiranja', '>=', $od->toDateString())->where('datum_tretiranja', '<', $do->copy()->addDay()->toDateString())->pluck('datum_tretiranja'),
            'gnojidba' => Gnojidba::where('user_id', $uid)->where('datum', '>=', $od->toDateString())->where('datum', '<', $do->copy()->addDay()->toDateString())->pluck('datum'),
        ];
    }

    public function render()
    {
        [$od, $do] = $this->granice();
        $uid = auth()->id();
        $sad = $this->aktivnosti($od, $do);
        $prije = $this->aktivnosti($od->copy()->subYear(), $do->copy()->subYear());

        // mjeseci u razdoblju
        $mjeseci = $this->mjeseciRazdoblja($od, $do, ['prskanje' => 0, 'gnojidba' => 0]);
        foreach ($sad as $vrsta => $datumi) {
            foreach ($datumi as $d) {
                $k = Carbon::parse($d)->format('Y-m');
                if (isset($mjeseci[$k])) {
                    $mjeseci[$k][$vrsta]++;
                }
            }
        }

        $ukupno = $sad['prskanje']->count() + $sad['gnojidba']->count();
        $ukupnoPrije = $prije['prskanje']->count() + $prije['gnojidba']->count();
        $promjena = $ukupnoPrije > 0 ? round(($ukupno - $ukupnoPrije) / $ukupnoPrije * 100) : null;

        [$vrh, $korak] = $this->ljestvica(collect($mjeseci)->max(fn ($m) => $m['prskanje'] + $m['gnojidba']));

        // zapisi za dopuniti (evidencija za 2027.): prskanja bez reg. broja ili fenofaze, aktivne kulture bez EPPO koda
        $bezPodataka = Prskanje::where('user_id', $uid)->where('datum_tretiranja', '>=', $od->toDateString())->where('datum_tretiranja', '<', $do->copy()->addDay()->toDateString())
            ->where(fn ($q) => $q->whereNull('registracijski_broj')->orWhere('registracijski_broj', '')->orWhereNull('bbch')->orWhere('bbch', ''))->count();
        $bezEppo = Kultura::where('user_id', $uid)->whereNull('zavrsena_at')->where(fn ($q) => $q->whereNull('eppo_kod')->orWhere('eppo_kod', ''))->count();

        $oznaka = $this->oznakaRazdoblja();

        return view('livewire.dash-pregled', [
            'odDatum' => $od, 'doDatum' => $do, 'oznaka' => $oznaka,
            'povrsina' => (float) Parcela::where('user_id', $uid)->sum('povrsina_ha'),
            'brojParcela' => Parcela::where('user_id', $uid)->count(),
            'brojKultura' => Kultura::where('user_id', $uid)->whereNull('zavrsena_at')->count(),
            'ukupno' => $ukupno, 'prskanja' => $sad['prskanje']->count(), 'gnojidbe' => $sad['gnojidba']->count(),
            'promjena' => $promjena, 'ukupnoPrije' => $ukupnoPrije,
            'mjeseci' => array_values($mjeseci), 'vrh' => $vrh, 'korak' => $korak,
            'bezPodataka' => $bezPodataka, 'bezEppo' => $bezEppo,
        ]);
    }
}
