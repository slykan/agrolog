<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parcela extends Model
{
    protected $table = 'parcele';
    protected $fillable = [
        'user_id', 'arkod_broj', 'povrsina_ha',
        'arkod_poligon', 'lat', 'lng', 'arkod_povrsina_ha', 'arkod_naziv', 'arkod_vrsta_uporabe', 'arkod_provjereno_at', 'rucno_ucrtana',
    ];
    protected $casts = ['arkod_poligon' => 'array', 'arkod_provjereno_at' => 'datetime', 'rucno_ucrtana' => 'boolean'];

    /** Podaci iz ARKOD-a (ArkodServis::dohvati) za spremanje uz parcelu. */
    public static function arkodPolja(?array $arkod): array
    {
        if (! $arkod) {
            return ['arkod_poligon' => null, 'lat' => null, 'lng' => null, 'arkod_povrsina_ha' => null,
                    'arkod_naziv' => null, 'arkod_vrsta_uporabe' => null, 'arkod_provjereno_at' => null];
        }

        return [
            'arkod_poligon'       => $arkod['poligon'],
            'lat'                 => $arkod['centar'][0],
            'lng'                 => $arkod['centar'][1],
            'arkod_povrsina_ha'   => $arkod['povrsina_ha'],
            'arkod_naziv'         => $arkod['naziv'],
            'arkod_vrsta_uporabe' => $arkod['vrsta_uporabe'],
            'arkod_provjereno_at' => now(),
        ];
    }

    /** Polja za ručno ucrtanu parcelu; službeni ARKOD podaci ostaju prazni. */
    public static function ucrtanaPolja(array $poligon): array
    {
        [$lat, $lng] = \App\Support\Poligon::sredina($poligon);

        return array_merge(self::arkodPolja(null), ['rucno_ucrtana' => true, 'arkod_poligon' => $poligon, 'lat' => $lat, 'lng' => $lng]);
    }

    /** Oznaka za ispise i izvoze, da se ručno ucrtana parcela ne može zamijeniti sa službenom. */
    public static function oznaka(?string $broj, $rucno): string
    {
        return $rucno ? 'Ručno ucrtana: ' . $broj : (string) $broj;
    }

    public function user() { return $this->belongsTo(User::class); }
    public function kulture() { return $this->hasMany(Kultura::class); }
}
