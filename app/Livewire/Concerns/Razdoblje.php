<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Carbon;

/** Izbor razdoblja (ova godina, prošla, zadnjih 12 mjeseci, od–do) za preglede i grafove. */
trait Razdoblje
{
    /** godina | prosla | 12mj | oddo */
    public string $razdoblje = 'godina';
    public string $od = '';
    public string $do = '';

    public function mountRazdoblje(): void
    {
        $this->od = now()->startOfYear()->toDateString();
        $this->do = now()->toDateString();
    }

    public function postavi(string $r): void
    {
        $this->razdoblje = in_array($r, ['godina', 'prosla', '12mj', 'oddo'], true) ? $r : 'godina';
    }

    /** [od, do] izabranog razdoblja (od–do najviše 3 godine). */
    protected function granice(): array
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

    protected function oznakaRazdoblja(): string
    {
        return match ($this->razdoblje) {
            'prosla' => 'prošle godine',
            '12mj' => 'zadnjih 12 mjeseci',
            'oddo' => 'u razdoblju',
            default => 'ove godine',
        };
    }

    /** Prazni mjeseci razdoblja: ['Y-m' => ['mjesec' => Carbon, ...$nule]]. */
    protected function mjeseciRazdoblja(Carbon $od, Carbon $do, array $nule): array
    {
        $mjeseci = [];
        for ($m = $od->copy()->startOfMonth(); $m->lte($do); $m->addMonth()) {
            $mjeseci[$m->format('Y-m')] = ['mjesec' => $m->copy()] + $nule;
        }

        return $mjeseci;
    }

    /** Lijepa ljestvica za os y (najviše 4 crte): [vrh, korak]. */
    protected function ljestvica(float $max): array
    {
        $max = max($max, 1);
        $red = 10 ** floor(log10($max));   // npr. 4040 → 1000
        foreach ([$red / 10, $red] as $baza) {
            foreach ([1, 2, 2.5, 5] as $k) {
                $korak = $k * $baza;
                if ($korak < 1 || floor($korak) != $korak) {
                    continue;   // samo cijeli koraci
                }
                if (ceil($max / $korak) <= 4) {
                    return [(int) (ceil($max / $korak) * $korak), (int) $korak];
                }
            }
        }

        return [(int) (ceil($max / (10 * $red)) * 10 * $red), (int) (10 * $red)];
    }
}
