<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Proizvod na Tržnici. Javan je samo kad je dostupan i kad je prezentacija gospodarstva javna (uključena i odobrena). */
class Proizvod extends Model
{
    protected $table = 'proizvodi';
    protected $fillable = ['prezentacija_id', 'naziv', 'vrsta', 'cijena', 'jedinica', 'opis', 'slika', 'dostupno'];
    protected $casts = ['dostupno' => 'boolean'];

    public const JEDINICE = ['kg' => 'kg', 'L' => 'L', 'kom' => 'kom', 'staklenka' => 'staklenka', 'boca' => 'boca', 'pakiranje' => 'pakiranje',
        'vezica' => 'vezica', 'gajba' => 'gajba', 't' => 't', 'ha' => 'ha', 'sat' => 'sat'];

    /** Najviše proizvoda po gospodarstvu. */
    public const NAJVISE = 40;

    public function prezentacija() { return $this->belongsTo(Prezentacija::class); }

    public function scopeJavni($q)
    {
        return $q->where('dostupno', true)->whereHas('prezentacija', fn ($p) => $p->javne());
    }

    public function cijenaTekst(): string
    {
        return number_format((float) $this->cijena, 2, ',', '.') . ' € / ' . $this->jedinica;
    }

    /** Izgled kartice bez slike: [ikona, CSS gradijent] prema vrsti. */
    public function izgled(): array
    {
        return match ($this->vrsta) {
            'Med' => ['🍯', '#fde68a, #f59e0b'],
            'Jabuke' => ['🍎', '#fecaca, #ef4444'],
            'Voće' => ['🍑', '#fed7aa, #fb923c'],
            'Grožđe', 'Vino' => [$this->vrsta === 'Vino' ? '🍷' : '🍇', '#e9d5ff, #9333ea'],
            'Rakija i likeri' => ['🥃', '#fde68a, #b45309'],
            'Maslinovo ulje' => ['🍶', '#ecfccb, #65a30d'],
            'Mlijeko i sir' => ['🧀', '#fef9c3, #facc15'],
            'Jaja' => ['🥚', '#fef3c7, #d6b98c'],
            'Meso i suhomesnato' => ['🥓', '#fecdd3, #be123c'],
            'Povrće' => ['🥕', '#d9f99d, #16a34a'],
            'Žitarice', 'Krmno bilje' => ['🌾', '#fef08a, #ca8a04'],
            'Džemovi i sokovi' => ['🍓', '#fbcfe8, #db2777'],
            'Orašasti plodovi' => ['🌰', '#e7d3b8, #92400e'],
            'Ljekovito bilje' => ['🌿', '#bbf7d0, #15803d'],
            'Cvijeće' => ['🌷', '#fbcfe8, #ec4899'],
            'Sadnice' => ['🌱', '#bbf7d0, #16a34a'],
            default => ['🧺', '#dcfce7, #4ade80'],
        };
    }

    public function slikaUrl(): ?string
    {
        return $this->slika ? asset($this->slika) : null;
    }
}
