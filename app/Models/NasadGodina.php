<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Potpore i ostali troškovi trajnog nasada za jednu godinu. */
class NasadGodina extends Model
{
    protected $table = 'nasad_godine';
    protected $fillable = ['kultura_id', 'godina', 'potpora_ha', 'trosak_ostalo'];
}
