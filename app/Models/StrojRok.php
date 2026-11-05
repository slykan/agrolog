<?php

namespace App\Models;

use App\Support\Mehanizacija;
use Illuminate\Database\Eloquent\Model;

class StrojRok extends Model
{
    protected $table = 'stroj_rokovi';
    protected $fillable = ['user_id', 'stroj_id', 'vrsta', 'naziv', 'datum_isteka', 'podsjetnik_dana', 'iznos', 'napomena',
                           'podsjetnik_poslan_at', 'istek_poslan_at', 'obnovljen_at'];
    protected $casts = ['datum_isteka' => 'date', 'podsjetnik_poslan_at' => 'datetime', 'istek_poslan_at' => 'datetime', 'obnovljen_at' => 'datetime'];

    public function stroj() { return $this->belongsTo(Stroj::class); }

    public function getOpisAttribute(): string
    {
        return $this->vrsta === 'ostalo' && $this->naziv ? $this->naziv : (Mehanizacija::ROKOVI[$this->vrsta][0] ?? $this->vrsta);
    }

    /** Dana do isteka (negativno = isteklo). */
    public function getDanaAttribute(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->datum_isteka->copy()->startOfDay(), false);
    }

    /** istekao | uskoro (unutar razdoblja podsjetnika) | ok */
    public function getStatusAttribute(): string
    {
        return $this->dana < 0 ? 'istekao' : ($this->dana <= $this->podsjetnik_dana ? 'uskoro' : 'ok');
    }
}
