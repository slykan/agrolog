<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oglas extends Model
{
    protected $table = 'oglasi';
    protected $fillable = ['naziv', 'slika', 'slika_w', 'slika_h', 'slika_mobitel', 'mobitel_w', 'mobitel_h', 'url', 'od', 'do',
        'na_naslovnoj', 'na_dashboardu', 'na_clancima', 'aktivan', 'rezervni'];
    protected $casts = ['od' => 'date', 'do' => 'date', 'na_naslovnoj' => 'boolean', 'na_dashboardu' => 'boolean',
        'na_clancima' => 'boolean', 'aktivan' => 'boolean', 'rezervni' => 'boolean'];

    /** Aktivan i unutar datuma (oba datuma se računaju). */
    public function scopeVrijedi($q)
    {
        $danas = now()->toDateString();

        return $q->where('aktivan', true)
            ->where(fn ($q) => $q->whereNull('od')->orWhere('od', '<', now()->addDay()->toDateString()))
            ->where(fn ($q) => $q->whereNull('do')->orWhere('do', '>=', $danas));
    }

    /** ugašen | zakazan | istekao | prikazuje se */
    public function getStanjeAttribute(): string
    {
        if (! $this->aktivan) {
            return 'ugašen';
        }
        if ($this->od && $this->od->gt(now()->startOfDay())) {
            return 'zakazan';
        }

        return $this->do && $this->do->lt(now()->startOfDay()) ? 'istekao' : 'prikazuje se';
    }
}
