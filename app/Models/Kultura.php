<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kultura extends Model
{
    protected $table = 'kulture';
    protected $fillable = ['user_id', 'parcela_id', 'naziv', 'posadjena_povrsina_ha', 'godina', 'zavrsena_at'];
    protected $casts = ['zavrsena_at' => 'date'];

    public function user() { return $this->belongsTo(User::class); }
    public function parcela() { return $this->belongsTo(Parcela::class); }
    public function gnojidbe() { return $this->hasMany(Gnojidba::class); }
    public function prskanja() { return $this->hasMany(Prskanje::class); }

    /** Kulture koje su još na parceli (nisu požnjevene ni zamijenjene). */
    public function scopeAktivne($q) { return $q->whereNull('kulture.zavrsena_at'); }

    public function imaZapise(): bool
    {
        return $this->gnojidbe()->exists() || $this->prskanja()->exists();
    }
}
