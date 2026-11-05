<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Skladište sredstava i gnojiva (korisnik ih može imati više). Stanje: App\Support\Skladiste. */
class Skladiste extends Model
{
    protected $table = 'skladista';
    protected $fillable = ['user_id', 'naziv', 'lokacija', 'zadano'];
    protected $casts = ['zadano' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }
    public function ulazi() { return $this->hasMany(SkladisteUlaz::class); }
}
