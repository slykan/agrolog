<?php

namespace App\Support;

/**
 * Uobičajena gnojiva i njihov sastav (% N, % P₂O₅, % K₂O) za izračun kg hraniva po hektaru.
 * Mineralna gnojiva: deklarirani sastav tipa. Stajski gnoj, gnojovka i gnojnica: prosječni udjeli
 * (kg N po toni) — za točan izračun upišite udjele iz analize ili deklaracije.
 */
class Gnojiva
{
    /** naziv => [N, P2O5, K2O, organsko (stajski gnoj — broji se u granicu 170 kg N/ha)] */
    public const POPIS = [
        'KAN 27% N'                    => [27, 0, 0, false],
        'UREA 46% N'                   => [46, 0, 0, false],
        'AN (amonijev nitrat) 33,5% N' => [33.5, 0, 0, false],
        'AS (amonijev sulfat) 21% N'   => [21, 0, 0, false],
        'UAN otopina 30% N'            => [30, 0, 0, false],
        'NPK 15-15-15'                 => [15, 15, 15, false],
        'NPK 7-20-30'                  => [7, 20, 30, false],
        'NPK 8-26-26'                  => [8, 26, 26, false],
        'NPK 10-20-30'                 => [10, 20, 30, false],
        'NPK 5-15-30'                  => [5, 15, 30, false],
        'NPK 20-10-10'                 => [20, 10, 10, false],
        'PK 0-20-30'                   => [0, 20, 30, false],
        'MAP 12-52-0'                  => [12, 52, 0, false],
        'DAP 18-46-0'                  => [18, 46, 0, false],
        'Trostruki superfosfat 0-46-0' => [0, 46, 0, false],
        'Kalijev klorid (KCl) 60% K2O' => [0, 0, 60, false],
        'Kalijev sulfat 50% K2O'       => [0, 0, 50, false],
        'Goveđi stajski gnoj'          => [0.5, 0.3, 0.6, true],
        'Svinjski stajski gnoj'        => [0.6, 0.5, 0.4, true],
        'Kokošji (peradarski) gnoj'    => [1.8, 1.5, 1.0, true],
        'Ovčji stajski gnoj'           => [0.8, 0.4, 0.8, true],
        'Goveđa gnojovka'              => [0.4, 0.2, 0.5, true],
        'Svinjska gnojovka'            => [0.5, 0.3, 0.3, true],
        'Gnojnica'                     => [0.3, 0.0, 0.6, true],
    ];

    /** Granica dušika iz stajskog gnoja (Akcijski program zaštite voda od nitrata, NN 95/25). */
    public const GRANICA_STAJSKI_N = 170;

    /**
     * Sastav gnojiva prepoznat iz upisanog naziva: točan naziv s popisa, formulacija "15-15-15",
     * ili poznata kratica (KAN, UREA, MAP, DAP…).
     *
     * @return array{naziv: string, n: float, p: float, k: float, organsko: bool}|null
     */
    public static function prepoznaj(?string $tekst): ?array
    {
        $t = trim((string) $tekst);
        if ($t === '') {
            return null;
        }
        $mali = mb_strtolower($t);

        foreach (self::POPIS as $naziv => [$n, $p, $k, $org]) {
            if (mb_strtolower($naziv) === $mali) {
                return ['naziv' => $naziv, 'n' => $n, 'p' => $p, 'k' => $k, 'organsko' => $org];
            }
        }

        // formulacija N-P-K, npr. "NPK 7-20-30", "7:20:30", "15-15-15+S"
        if (preg_match('/\b(\d{1,2}(?:[.,]\d)?)\s*[-:]\s*(\d{1,2}(?:[.,]\d)?)\s*[-:]\s*(\d{1,2}(?:[.,]\d)?)\b/u', $t, $m)) {
            $f = fn ($v) => (float) str_replace(',', '.', $v);
            return ['naziv' => $t, 'n' => $f($m[1]), 'p' => $f($m[2]), 'k' => $f($m[3]), 'organsko' => false];
        }

        $kljucne = [
            '/\bkan\b/u' => 'KAN 27% N', '/\burea\b|\bureja\b/u' => 'UREA 46% N', '/\bmap\b/u' => 'MAP 12-52-0',
            '/\bdap\b/u' => 'DAP 18-46-0', '/amonijev\s+nitrat|\ban\b/u' => 'AN (amonijev nitrat) 33,5% N',
            '/amonijev\s+sulfat|\bas\b/u' => 'AS (amonijev sulfat) 21% N', '/\buan\b/u' => 'UAN otopina 30% N',
            '/kcl|kalijev\s+klorid|kalijeva\s+so/u' => 'Kalijev klorid (KCl) 60% K2O',
            '/gnojovk/u' => 'Goveđa gnojovka', '/gnojnic/u' => 'Gnojnica', '/kokoš|perad|pileć/u' => 'Kokošji (peradarski) gnoj',
            '/stajsk|stajnjak/u' => 'Goveđi stajski gnoj',
        ];
        foreach ($kljucne as $uzorak => $naziv) {
            if (preg_match($uzorak, $mali)) {
                [$n, $p, $k, $org] = self::POPIS[$naziv];
                return ['naziv' => $naziv, 'n' => $n, 'p' => $p, 'k' => $k, 'organsko' => $org];
            }
        }

        return null;
    }

    /** Udio za prikaz bez suvišnih nula: 20.00 → 20, 33.50 → 33,5. */
    public static function postotak($v): string
    {
        return $v === null || $v === '' ? '0' : str_replace('.', ',', rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.'));
    }

    /** kg hraniva po hektaru iz količine gnojiva (kg/ha) i udjela (%). */
    public static function kgHraniva($kolicinaKgHa, $postotak): ?float
    {
        if ($kolicinaKgHa === null || $kolicinaKgHa === '' || $postotak === null || $postotak === '') {
            return null;
        }

        return round((float) $kolicinaKgHa * (float) $postotak / 100, 1);
    }
}
