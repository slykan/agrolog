<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parcela extends Model
{
    protected $table = 'parcele';
    protected $fillable = [
        'user_id', 'arkod_broj', 'povrsina_ha',
        'arkod_poligon', 'lat', 'lng', 'arkod_povrsina_ha', 'arkod_naziv', 'arkod_vrsta_uporabe', 'arkod_provjereno_at',
    ];
    protected $casts = ['arkod_poligon' => 'array', 'arkod_provjereno_at' => 'datetime'];

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

    public function user() { return $this->belongsTo(User::class); }
    public function kulture() { return $this->hasMany(Kultura::class); }
}
