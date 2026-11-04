<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gnojidba extends Model
{
    protected $table = 'gnojidbe';
    protected $fillable = ['user_id', 'kultura_id', 'datum', 'tip_gnojiva', 'kolicina_kg_ha', 'n_posto', 'p_posto', 'k_posto', 'organsko', 'kg_n_ha'];
    protected $casts = ['datum' => 'date', 'organsko' => 'boolean'];

    /**
     * Podaci zapisa s hranivima: prazni udjeli se popune iz prepoznatog gnojiva (App\Support\Gnojiva),
     * a kg N/ha izračuna iz količine i udjela dušika.
     */
    public static function sHranivima(array $data): array
    {
        $poznato = \App\Support\Gnojiva::prepoznaj($data['tip_gnojiva'] ?? null);
        foreach (['n_posto' => 'n', 'p_posto' => 'p', 'k_posto' => 'k'] as $polje => $kljuc) {
            if (! isset($data[$polje]) || $data[$polje] === '') {
                $data[$polje] = $poznato[$kljuc] ?? null;
            }
        }
        $data['organsko'] = (bool) ($data['organsko'] ?? ($poznato['organsko'] ?? false));
        $data['kg_n_ha'] = \App\Support\Gnojiva::kgHraniva($data['kolicina_kg_ha'] ?? null, $data['n_posto']);

        return $data;
    }

    public function user() { return $this->belongsTo(User::class); }
    public function kultura() { return $this->belongsTo(Kultura::class); }
}
