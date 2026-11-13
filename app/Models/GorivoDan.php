<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Gorivo natočeno za jedan dan rada; dijeli se na poslove tog dana po hektarima (App\Support\Gorivo). */
class GorivoDan extends Model
{
    protected $table = 'gorivo_dani';

    protected $fillable = ['user_id', 'datum', 'litara', 'cijena_l', 'iznos', 'izuzeto', 'napomena'];

    protected $casts = [
        'datum' => 'date',
        'izuzeto' => 'array',
    ];
}
