<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Javna prezentacija gospodarstva na /gospodarstva. Javna je samo kad je `vidljivo` (vlasnik) i `odobreno_at` (admin).
 * Lokacija je pin koji vlasnik sam postavi na karti (pretraga adrese samo približi kartu, rezultat se ne sprema).
 */
class Prezentacija extends Model
{
    protected $table = 'prezentacije';
    protected $fillable = ['user_id', 'vidljivo', 'odobreno_at', 'odbijeno_razlog', 'slug', 'naziv', 'mjesto', 'zupanija', 'opis', 'oznake',
        'telefon', 'whatsapp', 'viber', 'email', 'adresa', 'kucni_prag', 'radno_vrijeme', 'web', 'facebook', 'instagram', 'slika', 'lat', 'lng'];
    protected $casts = ['vidljivo' => 'boolean', 'whatsapp' => 'boolean', 'viber' => 'boolean', 'kucni_prag' => 'boolean',
        'oznake' => 'array', 'odobreno_at' => 'datetime'];

    public const DIR = 'images/gospodarstva';

    public const OZNAKE = ['Povrće', 'Voće', 'Jabuke', 'Grožđe', 'Vino', 'Rakija i likeri', 'Maslinovo ulje', 'Med', 'Mlijeko i sir', 'Jaja', 'Meso i suhomesnato',
        'Žitarice', 'Krmno bilje', 'Sadnice', 'Cvijeće', 'Ljekovito bilje', 'Džemovi i sokovi', 'Orašasti plodovi', 'Ekološka proizvodnja',
        'Usluge mehanizacije', 'Agroturizam', 'Ostalo'];

    public const ZUPANIJE = ['Bjelovarsko-bilogorska', 'Brodsko-posavska', 'Dubrovačko-neretvanska', 'Grad Zagreb', 'Istarska', 'Karlovačka',
        'Koprivničko-križevačka', 'Krapinsko-zagorska', 'Ličko-senjska', 'Međimurska', 'Osječko-baranjska', 'Požeško-slavonska',
        'Primorsko-goranska', 'Sisačko-moslavačka', 'Splitsko-dalmatinska', 'Šibensko-kninska', 'Varaždinska', 'Virovitičko-podravska',
        'Vukovarsko-srijemska', 'Zadarska', 'Zagrebačka'];

    public function user() { return $this->belongsTo(User::class); }

    public function scopeJavne($q)
    {
        return $q->where('vidljivo', true)->whereNotNull('odobreno_at');
    }

    public function jeJavna(): bool
    {
        return $this->vidljivo && $this->odobreno_at !== null;
    }

    public function status(): string
    {
        return match (true) {
            ! $this->vidljivo => 'skriveno',
            $this->odobreno_at !== null => 'javno',
            $this->odbijeno_razlog !== null => 'odbijeno',
            default => 'na_pregledu',
        };
    }

    public function slikaUrl(): ?string
    {
        return $this->slika ? asset($this->slika) : null;
    }

    /** Broj za wa.me / viber: samo znamenke, hrvatski broj bez 0 dobiva 385. */
    public function medunarodniBroj(): string
    {
        $b = preg_replace('/\D+/', '', $this->telefon);
        if (str_starts_with($b, '00')) {
            $b = substr($b, 2);
        } elseif (str_starts_with($b, '0')) {
            $b = '385' . substr($b, 1);
        }

        return $b;
    }

    public static function noviSlug(string $naziv, string $mjesto, ?int $osim = null): string
    {
        $baza = Str::limit(Str::slug($naziv . ' ' . $mjesto), 100, '') ?: 'gospodarstvo';
        $slug = $baza;
        for ($i = 2; static::where('slug', $slug)->when($osim, fn ($q) => $q->where('id', '!=', $osim))->exists(); $i++) {
            $slug = $baza . '-' . $i;
        }

        return $slug;
    }

    /** Sprema sliku smanjenu na najviše 1600 px (JPEG); bez GD-a sprema original. */
    public static function spremiSliku(UploadedFile $datoteka): string
    {
        if (! is_dir(public_path(self::DIR))) {
            mkdir(public_path(self::DIR), 0755, true);
        }
        $ime = Str::random(24) . '.jpg';
        $cilj = public_path(self::DIR . '/' . $ime);
        $izvor = function_exists('imagecreatefromstring') ? @imagecreatefromstring((string) file_get_contents($datoteka->getRealPath())) : false;
        if ($izvor === false) {
            $ime = Str::random(24) . '.' . ($datoteka->guessExtension() ?: 'jpg');
            $datoteka->move(public_path(self::DIR), $ime);

            return self::DIR . '/' . $ime;
        }
        $w = imagesx($izvor);
        $h = imagesy($izvor);
        $k = min(1, 1600 / max($w, $h));
        $nova = imagecreatetruecolor(max(1, (int) round($w * $k)), max(1, (int) round($h * $k)));
        imagefill($nova, 0, 0, imagecolorallocate($nova, 255, 255, 255));   // prozirni PNG na bijelo
        imagecopyresampled($nova, $izvor, 0, 0, 0, 0, imagesx($nova), imagesy($nova), $w, $h);
        imagejpeg($nova, $cilj, 82);
        imagedestroy($izvor);
        imagedestroy($nova);

        return self::DIR . '/' . $ime;
    }

    public static function obrisiSliku(?string $put): void
    {
        if ($put && str_starts_with($put, self::DIR . '/') && is_file(public_path($put))) {
            @unlink(public_path($put));
        }
    }

    /** Sredina parcela korisnika: odakle se karta otvara dok pin nije postavljen. */
    public static function priblizno(int $userId): array
    {
        $p = Parcela::where('user_id', $userId)->whereNotNull('lat')->whereNotNull('lng')->get(['lat', 'lng']);

        return $p->isEmpty() ? [null, null] : [round((float) $p->avg('lat'), 2), round((float) $p->avg('lng'), 2)];
    }

    /** Vodi li gospodarstvo evidenciju u AgroLogu (zapis prskanja, gnojidbe ili berbe u zadnjih godinu dana). */
    public function vodiEvidenciju(): bool
    {
        $od = now()->subYear()->toDateString();

        return Prskanje::where('user_id', $this->user_id)->where('datum_tretiranja', '>=', $od)->exists()
            || Gnojidba::where('user_id', $this->user_id)->where('datum', '>=', $od)->exists()
            || Berba::where('user_id', $this->user_id)->where('datum', '>=', $od)->exists();
    }
}
