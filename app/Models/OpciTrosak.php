<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Opći trošak gospodarstva (vožnja, zakup, osiguranje...). Kultura i klijent (za savjetnika) su neobavezni. */
class OpciTrosak extends Model
{
    protected $table = 'opci_troskovi';
    protected $fillable = ['user_id', 'datum', 'vrsta', 'opis', 'km', 'povratno', 'cijena_km', 'iznos', 'kultura_id', 'klijent_id'];
    protected $casts = ['datum' => 'date', 'povratno' => 'boolean'];

    public function kultura() { return $this->belongsTo(Kultura::class); }
    public function klijent() { return $this->belongsTo(User::class, 'klijent_id'); }

    /** Prijeđeni kilometri (s povratkom dvostruko); null ako nije vožnja. */
    public function getUkupnoKmAttribute(): ?float
    {
        return $this->km === null ? null : (float) $this->km * ($this->povratno ? 2 : 1);
    }
}
