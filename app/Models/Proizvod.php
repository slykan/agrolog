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

    public function slikaUrl(): ?string
    {
        return $this->slika ? asset($this->slika) : null;
    }
}
