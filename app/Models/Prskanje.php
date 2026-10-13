<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prskanje extends Model
{
    protected $table = 'prskanja';
    protected $fillable = [
        'user_id', 'kultura_id', 'datum_tretiranja', 'tretirana_povrsina_ha',
        'trgovacki_naziv_sredstva', 'registracijski_broj', 'kolicina_sredstva_l_ha',
        'vrijeme_od', 'vrijeme_do', 'kolicina_vode_l_ha'
    ];
    protected $casts = ['datum_tretiranja' => 'date'];

    public function user() { return $this->belongsTo(User::class); }
    public function kultura() { return $this->belongsTo(Kultura::class); }
}
