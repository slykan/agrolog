<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Ulaz na skladište: nabava ili korekcija stanja (inventura). Vidi App\Support\Skladiste. */
class SkladisteUlaz extends Model
{
    protected $table = 'skladiste_ulazi';
    protected $fillable = ['user_id', 'vrsta', 'naziv', 'datum', 'kolicina', 'jedinica', 'cijena', 'dobavljac', 'napomena', 'korekcija'];
    protected $casts = ['datum' => 'date', 'korekcija' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }
}
