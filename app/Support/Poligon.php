<?php

namespace App\Support;

/** Ručno ucrtana parcela: provjera točaka [lat, lng] i površina. */
class Poligon
{
    public const MAX_TOCAKA = 500;

    /** Lista točaka s klijenta u čistom obliku, ili null ako nije valjan poligon. */
    public static function ocisti(mixed $tocke): ?array
    {
        if (! is_array($tocke) || ! array_is_list($tocke) || count($tocke) < 3 || count($tocke) > self::MAX_TOCAKA) {
            return null;
        }

        $cisto = [];
        foreach ($tocke as $t) {
            if (! is_array($t) || ! is_numeric($t[0] ?? null) || ! is_numeric($t[1] ?? null)
                || abs((float) $t[0]) > 90 || abs((float) $t[1]) > 180) {
                return null;
            }
            $cisto[] = [round((float) $t[0], 6), round((float) $t[1], 6)];
        }

        return $cisto;
    }

    /** Površina u hektarima (Gaussova formula u lokalnim metrima; dovoljno točno za parcelu). */
    public static function ha(array $tocke): float
    {
        $f = deg2rad(array_sum(array_column($tocke, 0)) / count($tocke));
        $poLat = 111132.92 - 559.82 * cos(2 * $f) + 1.175 * cos(4 * $f);
        $poLng = 111412.84 * cos($f) - 93.5 * cos(3 * $f) + 0.118 * cos(5 * $f);

        $zbroj = 0.0;
        foreach ($tocke as $i => $a) {
            $b = $tocke[($i + 1) % count($tocke)];
            $zbroj += ($a[1] * $poLng) * ($b[0] * $poLat) - ($b[1] * $poLng) * ($a[0] * $poLat);
        }

        return abs($zbroj) / 2 / 10000;
    }

    public static function sredina(array $tocke): array
    {
        return [
            round(array_sum(array_column($tocke, 0)) / count($tocke), 6),
            round(array_sum(array_column($tocke, 1)) / count($tocke), 6),
        ];
    }

    /** Križaju li se rubovi: takav oblik nema smislenu površinu. */
    public static function kriziSe(array $tocke): bool
    {
        $n = count($tocke);
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 2; $j < $n; $j++) {
                if (($j + 1) % $n === $i) {
                    continue;   // susjedni rubovi dijele točku
                }
                if (self::sijekuSe($tocke[$i], $tocke[($i + 1) % $n], $tocke[$j], $tocke[($j + 1) % $n])) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function sijekuSe(array $a, array $b, array $c, array $d): bool
    {
        $strana = fn ($p, $q, $r) => ($q[0] - $p[0]) * ($r[1] - $p[1]) - ($q[1] - $p[1]) * ($r[0] - $p[0]);

        return $strana($a, $b, $c) * $strana($a, $b, $d) < 0 && $strana($c, $d, $a) * $strana($c, $d, $b) < 0;
    }
}
