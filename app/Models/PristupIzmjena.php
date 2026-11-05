<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Jedna izmjena koju je savjetnik napravio u gospodarstvu (zahtjev s upisom u bazu). */
class PristupIzmjena extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'pristup_izmjene';
    protected $fillable = ['pristup_id', 'vlasnik_id', 'savjetnik_id', 'savjetnik_ime', 'opis', 'tablice'];
    protected $casts = ['tablice' => 'array', 'created_at' => 'datetime'];

    public function savjetnik() { return $this->belongsTo(User::class, 'savjetnik_id'); }
}
