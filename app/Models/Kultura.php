<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kultura extends Model
{
    protected $table = 'kulture';
    protected $fillable = ['user_id', 'parcela_id', 'naziv', 'eppo_naziv', 'eppo_kod', 'posadjena_povrsina_ha', 'godina', 'zavrsena_at',
        'zetva_datum', 'prinos_t_ha', 'cijena_t', 'potpora_ha', 'trosak_sjeme', 'trosak_ostalo', 'trajni', 'sorta', 'godina_sadnje', 'broj_stabala'];

    /** Nazivi koji se predlažu kao trajni nasad; korisnik može promijeniti. */
    public const TRAJNI_NAZIVI = ['vinova loza', 'vinograd', 'voćnjak', 'maslinik', 'jabuka', 'kruška', 'šljiva', 'trešnja', 'višnja', 'breskva', 'nektarina', 'marelica', 'dunja',
        'orah', 'lijeska', 'badem', 'kesten', 'maslina', 'smokva', 'mandarina', 'naranča', 'limun', 'kivi', 'šipak', 'aronija', 'borovnica', 'malina', 'kupina', 'ribiz', 'lavanda'];
    protected $casts = ['zavrsena_at' => 'date', 'zetva_datum' => 'date', 'trajni' => 'boolean'];

    public static function jeTrajniNaziv(?string $naziv): bool
    {
        $n = mb_strtolower(trim((string) $naziv));
        foreach (self::TRAJNI_NAZIVI as $t) {
            if ($n === $t || str_starts_with($n, $t . ' ')) {
                return true;
            }
        }

        return false;
    }

    /** Prihod kulture: prinos × površina × cijena + potpore × površina; null ako ništa nije upisano. */
    public function prihod(): ?float
    {
        $ha = (float) $this->posadjena_povrsina_ha;
        $prodaja = $this->prinos_t_ha !== null && $this->cijena_t !== null ? (float) $this->prinos_t_ha * $ha * (float) $this->cijena_t : null;
        $potpora = $this->potpora_ha !== null ? (float) $this->potpora_ha * $ha : null;

        return $prodaja === null && $potpora === null ? null : round(($prodaja ?? 0) + ($potpora ?? 0), 2);
    }

    /** Troškovi koji nisu u evidenciji (sjeme, usluge, zakup...). */
    public function ostaliTroskovi(): float
    {
        return round((float) $this->trosak_sjeme + (float) $this->trosak_ostalo, 2);
    }

    public function user() { return $this->belongsTo(User::class); }
    public function parcela() { return $this->belongsTo(Parcela::class); }
    public function gnojidbe() { return $this->hasMany(Gnojidba::class); }
    public function prskanja() { return $this->hasMany(Prskanje::class); }
    public function berbe() { return $this->hasMany(Berba::class); }
    public function radovi() { return $this->hasMany(Rad::class); }

    /** Kulture koje su još na parceli (nisu požnjevene ni zamijenjene). */
    public function scopeAktivne($q) { return $q->whereNull('kulture.zavrsena_at'); }

    /** Kulture na parceli u godini: jednogodišnje te godine, trajni nasad od godine unosa dok nije iskrčen. */
    public function scopeUGodini($q, $godina)
    {
        $g = (int) $godina;

        return $q->where(fn ($q) => $q->where('kulture.godina', $g)->orWhere(fn ($q) => $q->where('kulture.trajni', true)->where('kulture.godina', '<=', $g)
            ->where(fn ($q) => $q->whereNull('kulture.zavrsena_at')->orWhere('kulture.zavrsena_at', '>=', $g . '-01-01'))));
    }

    public function imaZapise(): bool
    {
        return $this->gnojidbe()->exists() || $this->prskanja()->exists() || $this->berbe()->exists() || $this->radovi()->exists();
    }
}
