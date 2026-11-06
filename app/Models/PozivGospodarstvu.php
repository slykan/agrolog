<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Savjetnik poziva poljoprivrednika e-mailom. Sam poziv ne daje pristup: nastaje tek kad poljoprivrednik prihvati i odabere prava. */
class PozivGospodarstvu extends Model
{
    protected $table = 'pozivi_gospodarstvima';
    protected $fillable = ['savjetnik_id', 'email', 'token', 'status', 'broj_slanja', 'poslano_at', 'odgovoreno_at'];
    protected $casts = ['poslano_at' => 'datetime', 'odgovoreno_at' => 'datetime'];

    /** Najviše slanja istog poziva, da se primatelja ne može zatrpavati. */
    public const MAX_SLANJA = 3;

    public static function noviToken(): string
    {
        return Str::random(48);
    }

    public function savjetnik() { return $this->belongsTo(User::class, 'savjetnik_id'); }

    public function smijePonovno(): bool
    {
        return $this->status === 'poslano' && $this->broj_slanja < self::MAX_SLANJA
            && (! $this->poslano_at || $this->poslano_at->lte(now()->subDay()));
    }
}
