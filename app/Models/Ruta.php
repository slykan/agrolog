<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Spremljena ruta za brzi upis vožnje. */
class Ruta extends Model
{
    protected $table = 'rute';
    protected $fillable = ['user_id', 'naziv', 'km', 'povratno'];
    protected $casts = ['povratno' => 'boolean'];
}
