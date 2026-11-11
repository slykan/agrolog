<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Planirani posao iz kalendara. Kad se obavi, upisuje se pravi zapis i plan se zatvara (obavljeno_at, zapis_vrsta, zapis_id). */
class PlaniraniRad extends Model
{
    protected $table = 'planirani_radovi';

    protected $fillable = ['user_id', 'kultura_id', 'datum', 'vrsta', 'opis', 'napomena', 'obavljeno_at', 'zapis_vrsta', 'zapis_id'];

    protected $casts = ['datum' => 'date', 'obavljeno_at' => 'datetime'];

    public const VRSTE = ['prskanje' => 'Prskanje', 'gnojidba' => 'Gnojidba', 'berba' => 'Berba / žetva', 'rad' => 'Ostali rad', 'ostalo' => 'Ostalo'];

    /** Vrste koje se zatvaraju upisom zapisa na svojoj stranici; "ostalo" se samo označi obavljenim. */
    public const RUTE = ['prskanje' => 'prskanje', 'gnojidba' => 'gnojidba', 'berba' => 'berba', 'rad' => 'radovi'];

    public function kultura() { return $this->belongsTo(Kultura::class); }

    public function scopeOtvoreni($q) { return $q->whereNull('obavljeno_at'); }

    public function naslov(): string
    {
        return trim((self::VRSTE[$this->vrsta] ?? $this->vrsta) . ($this->opis ? ': ' . $this->opis : ''));
    }

    /** Poveznica za upis obavljenog posla s popunjenim podacima iz plana; null za "ostalo". */
    public function linkZaUnos(): ?string
    {
        return isset(self::RUTE[$this->vrsta]) ? route(self::RUTE[$this->vrsta], ['plan' => $this->id]) : null;
    }
}
