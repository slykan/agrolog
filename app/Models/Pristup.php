<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Pristup savjetnika gospodarstvu: vlasnik poziva e-mailom, savjetnik prihvaća. Prava: pregled | unos. */
class Pristup extends Model
{
    protected $table = 'pristupi';
    protected $fillable = ['vlasnik_id', 'email', 'savjetnik_id', 'prava', 'token', 'status', 'prihvaceno_at', 'zadnji_rad_at'];
    protected $casts = ['prihvaceno_at' => 'datetime', 'zadnji_rad_at' => 'datetime'];

    public const PRAVA = ['pregled' => 'Samo pregled', 'unos' => 'Pregled i unos'];

    public static function noviToken(): string
    {
        return Str::random(48);
    }

    public function vlasnik() { return $this->belongsTo(User::class, 'vlasnik_id'); }
    public function savjetnik() { return $this->belongsTo(User::class, 'savjetnik_id'); }
    public function izmjene() { return $this->hasMany(PristupIzmjena::class); }

    public function scopeAktivni($q) { return $q->where('status', 'prihvaceno'); }

    public function smijeUnos(): bool { return $this->prava === 'unos'; }
}
