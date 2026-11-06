<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Zapisnik: kome je i kada poslana ponuda za oglašavanje te tko je rekao da ne želi poruke. */
class PonudaOglasivacu extends Model
{
    public $timestamps = false;
    protected $table = 'ponude_oglasivacima';
    protected $fillable = ['tvrtka', 'email', 'proizvodi', 'predmet', 'poslano_at', 'ne_zeli_at'];
    protected $casts = ['poslano_at' => 'datetime', 'ne_zeli_at' => 'datetime'];
}
