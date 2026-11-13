<?php

namespace App\Livewire\Concerns;

use App\Models\GorivoDan;
use Livewire\Attributes\On;

/**
 * Prskanje i gnojidba: nakon novog zapisa javi komponenti goriva (popup za taj dan),
 * a u redovima prikaži gumb ⛽ s upisanim gorivom za taj dan (klik otvara ispravak).
 */
trait JaviGorivo
{
    protected function javiGorivo(?string $datum): void
    {
        if ($datum) {
            $this->dispatch('posao-spremljen', datum: $datum);
        }
    }

    /** Y-m-d => kratki opis upisanog goriva ("95 L" ili "135 €"). */
    protected ?array $gorivoDaniCache = null;

    public function gorivoDani(): array
    {
        return $this->gorivoDaniCache ??= GorivoDan::where('user_id', auth()->id())->get(['datum', 'litara', 'iznos'])
            ->mapWithKeys(fn ($d) => [$d->datum->format('Y-m-d') => $d->litara !== null
                ? rtrim(rtrim(number_format((float) $d->litara, 1, ',', '.'), '0'), ',') . ' L'
                : number_format((float) $d->iznos, 0, ',', '.') . ' €'])
            ->all();
    }

    public function pratiGorivo(): bool
    {
        return (bool) auth()->user()->prati_gorivo;
    }

    /** Gorivo spremljeno u popupu: osvježi gumbe ⛽. */
    #[On('troskovi-promijenjeni')]
    public function gorivoPromijenjeno(): void
    {
        $this->gorivoDaniCache = null;
    }
}
