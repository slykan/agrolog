<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Berba ili žetva. Količina i cijena su u bazi po kg; `jedinica` (kg ili t) je ono što je korisnik upisao. */
class Berba extends Model
{
    protected $table = 'berbe';
    protected $fillable = ['user_id', 'kultura_id', 'datum', 'kolicina_kg', 'jedinica', 'cijena_kg', 'povrsina_ha', 'sorta', 'klasa', 'kupac', 'napomena'];
    protected $casts = ['datum' => 'date'];

    public function kultura() { return $this->belongsTo(Kultura::class); }

    private function faktor(): int
    {
        return $this->jedinica === 't' ? 1000 : 1;
    }

    /** Količina u jedinici u kojoj je upisana. */
    public function getKolicinaAttribute(): float
    {
        return round((float) $this->kolicina_kg / $this->faktor(), 3);
    }

    /** Cijena po upisanoj jedinici (€/kg ili €/t). */
    public function getCijenaAttribute(): ?float
    {
        return $this->cijena_kg === null ? null : round((float) $this->cijena_kg * $this->faktor(), 2);
    }

    public function getIznosAttribute(): ?float
    {
        return $this->cijena_kg === null ? null : round((float) $this->kolicina_kg * (float) $this->cijena_kg, 2);
    }
}
