<?php

namespace App\Support;

use App\Models\Parcela;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Dnevna prognoza (Open-Meteo) za gospodarstvo: točka je prosjek parcela s koordinatama.
 * Ocjena dana za prskanje ista je kao na Početnoj: vjetar < 15 km/h, kiša < 40 %, bez mraza, 5–30 °C.
 */
class Prognoza
{
    public const DANA = 7;

    /** Središte parcela [lat, lng] zaokruženo na 0,1° (dijeli se predmemorija među susjedima), ili null. */
    public static function tocka(User $user): ?array
    {
        $p = Parcela::where('user_id', $user->id)->whereNotNull('lat')->whereNotNull('lng')
            ->selectRaw('AVG(lat) AS lat, AVG(lng) AS lng')->first();

        return $p && $p->lat !== null ? [round((float) $p->lat, 1), round((float) $p->lng, 1)] : null;
    }

    /**
     * Dani prognoze po datumu: ['2026-11-12' => ['ikona', 'opis', 'min', 'max', 'kisa_mm', 'vjerojatnost', 'vjetar', 'prskanje' => bool, 'razlog']].
     * Prazno ako gospodarstvo nema parcela na karti ili prognoza nije dostupna.
     */
    public static function za(User $user): array
    {
        $t = self::tocka($user);
        if (! $t) {
            return [];
        }
        $kljuc = 'prognoza-' . $t[0] . '-' . $t[1];
        if (Cache::has($kljuc)) {
            return Cache::get($kljuc);
        }
        if (Cache::has('prognoza-nedostupna')) {
            return [];
        }

        try {
            $r = Http::timeout(6)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $t[0], 'longitude' => $t[1], 'timezone' => 'Europe/Zagreb', 'forecast_days' => self::DANA,
                'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_probability_max,wind_speed_10m_max',
            ]);
            $d = $r->successful() ? ($r->json('daily') ?? null) : null;
        } catch (\Throwable) {
            $d = null;
        }
        if (! $d || empty($d['time'])) {
            Cache::put('prognoza-nedostupna', true, now()->addMinutes(10));

            return [];
        }

        $dani = [];
        foreach ($d['time'] as $i => $datum) {
            [$ikona, $opis] = self::vrijeme((int) ($d['weather_code'][$i] ?? 0));
            $vjetar = (int) round((float) ($d['wind_speed_10m_max'][$i] ?? 0));
            $vjerojatnost = (int) ($d['precipitation_probability_max'][$i] ?? 0);
            $min = (float) ($d['temperature_2m_min'][$i] ?? 0);
            $max = (float) ($d['temperature_2m_max'][$i] ?? 0);
            $razlozi = self::razlozi($vjetar, $vjerojatnost, $min, $max);
            $dani[$datum] = [
                'ikona' => $ikona, 'opis' => $opis, 'min' => (int) round($min), 'max' => (int) round($max),
                'kisa_mm' => round((float) ($d['precipitation_sum'][$i] ?? 0), 1), 'vjerojatnost' => $vjerojatnost, 'vjetar' => $vjetar,
                'prskanje' => $razlozi === [], 'razlog' => implode(', ', $razlozi),
            ];
        }
        Cache::put($kljuc, $dani, now()->addHours(3));

        return $dani;
    }

    /** Zašto dan nije za prskanje (prazno = povoljno). */
    public static function razlozi(int $vjetar, int $kisa, float $min, float $max): array
    {
        $r = [];
        if ($vjetar >= 15) { $r[] = 'vjetar'; }
        if ($kisa >= 40) { $r[] = 'kiša'; }
        if ($min < 0) { $r[] = 'mraz'; } elseif ($max < 5) { $r[] = 'hladno'; }
        if ($max > 30) { $r[] = 'vruće'; }

        return $r;
    }

    /** WMO šifra vremena → [ikona, opis] (isto kao na Početnoj). */
    public static function vrijeme(int $kod): array
    {
        return match (true) {
            $kod === 0 => ['☀️', 'vedro'],
            $kod <= 2 => ['🌤️', 'pretežno sunčano'],
            $kod === 3 => ['☁️', 'oblačno'],
            $kod <= 48 => ['🌫️', 'magla'],
            $kod <= 57 => ['🌦️', 'rosulja'],
            $kod <= 67 => ['🌧️', 'kiša'],
            $kod <= 77 => ['🌨️', 'snijeg'],
            $kod <= 82 => ['🌧️', 'pljuskovi'],
            $kod <= 86 => ['🌨️', 'snježni pljuskovi'],
            default => ['⛈️', 'grmljavina'],
        };
    }
}
