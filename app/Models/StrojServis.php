<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StrojServis extends Model
{
    protected $table = 'stroj_servisi';
    protected $fillable = ['user_id', 'stroj_id', 'datum', 'opis', 'radni_sati', 'trosak', 'serviser'];
    protected $casts = ['datum' => 'date'];

    public function stroj() { return $this->belongsTo(Stroj::class); }
}
