<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Traktor, priključak ili vozilo. Vidi App\Support\Mehanizacija. */
class Stroj extends Model
{
    protected $table = 'strojevi';
    protected $fillable = ['user_id', 'vrsta', 'naziv', 'registracija', 'godina', 'serijski_broj', 'napomena'];

    public function user() { return $this->belongsTo(User::class); }
    public function rokovi() { return $this->hasMany(StrojRok::class); }
    public function servisi() { return $this->hasMany(StrojServis::class); }
}
