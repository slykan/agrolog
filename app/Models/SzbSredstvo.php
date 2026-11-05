<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Sredstvo iz registra FIS-a (javna tražilica SZB), vidi App\Support\FisRegistar. */
class SzbSredstvo extends Model
{
    protected $table = 'szb_sredstva';
    protected $fillable = ['fis_id', 'naziv', 'registracijski_broj', 'nositelj', 'stari_naziv', 'aktivno'];
    protected $casts = ['aktivno' => 'boolean', 'detalji' => 'array', 'detalji_at' => 'datetime'];

    /** Sredstvo po trgovačkom nazivu (bez obzira na velika/mala slova i razmake), ili po starom nazivu. */
    public static function poNazivu(?string $naziv): ?self
    {
        $naziv = mb_strtoupper(trim(preg_replace('/\s+/u', ' ', (string) $naziv)));
        if ($naziv === '') {
            return null;
        }

        return static::whereRaw('UPPER(naziv) = ?', [$naziv])->orderByDesc('aktivno')->first()
            ?? static::whereRaw('UPPER(stari_naziv) = ?', [$naziv])->orderByDesc('aktivno')->first();
    }
}
