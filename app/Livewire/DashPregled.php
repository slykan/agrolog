<?php

namespace App\Livewire;

use App\Models\Gnojidba;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\Prskanje;
use Illuminate\Support\Carbon;
use Livewire\Component;

/** Pregled gospodarstva na početnoj: aktivnosti (prskanje, gnojidba) po mjesecima za izabrano razdoblje i usporedba s godinom ranije. */
class DashPregled extends Component
{
    /** godina | prosla | 12mj | oddo */
    public string $razdoblje = 'godina';
    public string $od = '';
    public string $do = '';

    public function mount(): void
    {
        $this->od = now()->startOfYear()->toDateString();
        $this->do = now()->toDateString();
    }

    public function postavi(string $r): void
    {
        $this->razdoblje = in_array($r, ['godina', 'prosla', '12mj', 'oddo'], true) ? $r : 'godina';
    }

    /** [od, do] izabranog razdoblja (od-do najviše 3 godine). */
    private function granice(): array
    {
        return match ($this->razdoblje) {
            'prosla' => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
            '12mj' => [now()->subMonths(11)->startOfMonth(), now()->endOfMonth()],
            'oddo' => (function () {
                try {
                    $od = Carbon::parse($this->od)->startOfDay();
                    $do = Carbon::parse($this->do)->endOfDay();
                } catch (\Throwable) {
                    return [now()->startOfYear(), now()->endOfYear()];
                }
                if ($do->lt($od)) {
                    [$od, $do] = [$do->copy()->startOfDay(), $od->copy()->endOfDay()];
                }
                if ($od->diffInMonths($do) > 36) {
                    $od = $do->copy()->subMonths(35)->startOfMonth();
                }

                return [$od, $do];
            })(),
            default => [now()->startOfYear(), now()->endOfYear()],
        };
    }

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
        $mjeseci = [];
        for ($m = $od->copy()->startOfMonth(); $m->lte($do); $m->addMonth()) {
            $mjeseci[$m->format('Y-m')] = ['mjesec' => $m->copy(), 'prskanje' => 0, 'gnojidba' => 0];
        }
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

        // ljestvica: lijep korak (1, 2, 5 × 10^n) za najviše 4 crte
        $max = max(1, collect($mjeseci)->max(fn ($m) => $m['prskanje'] + $m['gnojidba']));
        $korak = 1;
        foreach ([1, 2, 5, 10, 20, 25, 50, 100, 200, 250, 500, 1000] as $k) {
            $korak = $k;
            if (ceil($max / $k) <= 4) {
                break;
            }
        }
        $vrh = (int) (ceil($max / $korak) * $korak);

        // zapisi za dopuniti (evidencija za 2027.): prskanja bez reg. broja ili fenofaze, aktivne kulture bez EPPO koda
        $bezPodataka = Prskanje::where('user_id', $uid)->where('datum_tretiranja', '>=', $od->toDateString())->where('datum_tretiranja', '<', $do->copy()->addDay()->toDateString())
            ->where(fn ($q) => $q->whereNull('registracijski_broj')->orWhere('registracijski_broj', '')->orWhereNull('bbch')->orWhere('bbch', ''))->count();
        $bezEppo = Kultura::where('user_id', $uid)->whereNull('zavrsena_at')->where(fn ($q) => $q->whereNull('eppo_kod')->orWhere('eppo_kod', ''))->count();

        $oznaka = match ($this->razdoblje) {
            'prosla' => 'prošle godine',
            '12mj' => 'zadnjih 12 mjeseci',
            'oddo' => 'u razdoblju',
            default => 'ove godine',
        };

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
