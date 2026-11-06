<?php

namespace App\Support;

use App\Models\Oglas;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Oglasi: najviše jedan banner po stranici. Ako ih je više aktivno, pri svakom učitavanju bira se nasumično jedan.
 * Rezervni oglas ("Oglašavajte se") vidi se samo kad na tom mjestu nema drugih. Brojanje je zbirno, bez podataka o osobi.
 */
class Oglasi
{
    /** Mjesto na stranici => stupac s kvačicom. Vodiči i novosti dijele kvačicu, a u statistici su odvojeni. */
    public const MJESTA = ['naslovna' => 'na_naslovnoj', 'dashboard' => 'na_dashboardu', 'vodici' => 'na_clancima', 'novosti' => 'na_clancima'];
    public const NAZIVI = ['naslovna' => 'Naslovna', 'dashboard' => 'Dashboard', 'vodici' => 'Vodiči', 'novosti' => 'Novosti'];
    public const DIR = 'images/oglasi/u';

    public static function za(string $mjesto): ?Oglas
    {
        if (! isset(self::MJESTA[$mjesto])) {
            return null;
        }
        $svi = Oglas::vrijedi()->where(self::MJESTA[$mjesto], true)->get();
        $placeni = $svi->where('rezervni', false);

        return $placeni->isNotEmpty() ? $placeni->random() : ($svi->isNotEmpty() ? $svi->random() : null);
    }

    /** Pretraživači i alati ne ulaze u brojke koje vidi oglašivač. */
    public static function jeBot(?string $ua): bool
    {
        return blank($ua) || preg_match('/bot|crawl|spider|slurp|mediapartners|facebookexternalhit|preview|headless|lighthouse|curl|wget|python/i', $ua) === 1;
    }

    /** @param 'prikazi'|'klikovi' $stupac */
    public static function zabiljezi(int $oglasId, string $mjesto, string $stupac): void
    {
        DB::table('oglas_statistika')->upsert(
            [['oglas_id' => $oglasId, 'mjesto' => $mjesto, 'datum' => now()->toDateString(), 'prikazi' => $stupac === 'prikazi' ? 1 : 0, 'klikovi' => $stupac === 'klikovi' ? 1 : 0]],
            ['oglas_id', 'mjesto', 'datum'],
            [$stupac => DB::raw($stupac . ' + 1')],
        );
    }

    /** Sprema sliku pod nasumičnim imenom. @return array{0: string, 1: int, 2: int} put, širina, visina */
    public static function spremiSliku(UploadedFile $slika): array
    {
        [$w, $h] = getimagesize($slika->getRealPath());
        $ime = Str::random(24) . '.' . ($slika->guessExtension() ?: 'jpg');
        if (! is_dir(public_path(self::DIR))) {
            mkdir(public_path(self::DIR), 0755, true);
        }
        $slika->move(public_path(self::DIR), $ime);

        return [self::DIR . '/' . $ime, (int) $w, (int) $h];
    }

    /** Briše samo prenesene slike, nikad one koje su dio aplikacije. */
    public static function obrisiSliku(?string $put): void
    {
        if ($put && str_starts_with($put, self::DIR . '/') && is_file(public_path($put))) {
            @unlink(public_path($put));
        }
    }
}
