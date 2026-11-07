<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Ostali rad na kulturi (oranje, rezidba, košnja...). Trošak = gorivo + rad, ili usluga (ukupno ili po ha). */
class Rad extends Model
{
    protected $table = 'radovi';
    protected $fillable = ['user_id', 'kultura_id', 'datum', 'vrsta', 'izvedba', 'stroj_id', 'povrsina_ha', 'sati', 'gorivo_l', 'cijena_goriva',
        'radni_sati', 'cijena_sata', 'usluga_cijena', 'usluga_po', 'izvodac', 'trosak', 'napomena'];
    protected $casts = ['datum' => 'date'];

    public function kultura() { return $this->belongsTo(Kultura::class); }
    public function stroj() { return $this->belongsTo(Stroj::class); }

    /**
     * Trošak iz upisanih podataka; null kad ništa nije upisano.
     * Usluga: cijena ukupno ili cijena × ha (obrađena površina, inače površina kulture). Vlastito: gorivo × €/L + radni sati × €/h.
     */
    public static function izracunajTrosak(array $p, ?float $haKulture): ?float
    {
        $n = fn ($k) => isset($p[$k]) && $p[$k] !== '' && $p[$k] !== null ? (float) $p[$k] : null;
        if (($p['izvedba'] ?? 'vlastito') === 'usluga') {
            $c = $n('usluga_cijena');
            if ($c === null) {
                return null;
            }
            $ha = $n('povrsina_ha') ?? $haKulture;

            return round(($p['usluga_po'] ?? 'ukupno') === 'ha' ? $c * (float) $ha : $c, 2);
        }
        $gorivo = $n('gorivo_l') !== null && $n('cijena_goriva') !== null ? $n('gorivo_l') * $n('cijena_goriva') : null;
        $rad = $n('radni_sati') !== null && $n('cijena_sata') !== null ? $n('radni_sati') * $n('cijena_sata') : null;

        return $gorivo === null && $rad === null ? null : round(($gorivo ?? 0) + ($rad ?? 0), 2);
    }
}
