<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Dohvat ARKOD parcele po ARKOD ID-u iz javnog WMS-a Agencije za plaćanja (servisi.apprrr.hr/NIPP).
 * WFS GetFeature nije javno dostupan, ali WMS GetMap u GeoRSS formatu s CQL filtrom vraća
 * poligon i atribute parcele bez obzira na mjerilo karte.
 */
class ArkodServis
{
    private const WMS = 'https://servisi.apprrr.hr/NIPP/wms';

    /** Vrste uporabe zemljišta (land_use_id), nazivi iz SLD stila sloja hr.land_parcels. */
    public const VRSTE_UPORABE = [
        200 => 'Oranica', 210 => 'Staklenik / plastenik', 310 => 'Livada', 320 => 'Pašnjak',
        321 => 'Krški pašnjak', 410 => 'Vinograd', 411 => 'Iskrčeni vinograd', 421 => 'Maslinik',
        422 => 'Voćnjak', 430 => 'Kulture kratke ophodnje', 450 => 'Rasadnik',
        490 => 'Mješoviti višegodišnji nasadi', 900 => 'Ostalo', 910 => 'Privremeno neodržavana parcela',
    ];

    /**
     * @return array{id: string, povrsina_ha: float, naziv: ?string, vrsta_uporabe: ?string, vodozastita: ?string,
     *               natura2000: bool, poligon: list<array{0: float, 1: float}>, centar: array{0: float, 1: float}}|null
     */
    public static function dohvati(string $arkod): ?array
    {
        $arkod = preg_replace('/\D/', '', $arkod);
        if ($arkod === '' || strlen($arkod) > 12) {
            return null;
        }

        return Cache::remember("arkod:$arkod", now()->addDay(), function () use ($arkod) {
            $odgovor = Http::timeout(8)->get(self::WMS, [
                'service' => 'WMS', 'version' => '1.1.1', 'request' => 'GetMap',
                'layers' => 'NIPP:hr.land_parcels', 'styles' => '', 'srs' => 'EPSG:4326',
                'bbox' => '13.4,42.3,19.5,46.6', 'width' => 1024, 'height' => 720,
                'format' => 'application/rss+xml', 'CQL_FILTER' => "id=$arkod",
            ]);

            return $odgovor->successful() ? self::procitaj($odgovor->body(), $arkod) : null;
        });
    }

    /** Parsira GeoRSS odgovor (javno zbog testova). */
    public static function procitaj(string $rss, string $arkod): ?array
    {
        if (! preg_match('#<item>.*?</item>#s', $rss, $item)) {
            return null;
        }
        $item = $item[0];

        $atr = [];
        if (preg_match_all('#atr-name">([^<]+)</span>:</strong>\s*<span class="atr-value">([^<]*)</span>#', $item, $m, PREG_SET_ORDER)) {
            foreach ($m as [, $k, $v]) {
                $atr[trim($k)] = html_entity_decode(trim($v));
            }
        }

        if (! preg_match('#<georss:polygon>([^<]+)</georss:polygon>#', $item, $pm)) {
            return null;
        }
        $brojevi = array_map('floatval', preg_split('/\s+/', trim($pm[1])));
        $poligon = [];
        for ($i = 0; $i + 1 < count($brojevi); $i += 2) {
            $poligon[] = [round($brojevi[$i], 6), round($brojevi[$i + 1], 6)];   // [lat, lng]
        }
        if (count($poligon) < 3) {
            return null;
        }

        $lats = array_column($poligon, 0);
        $lngs = array_column($poligon, 1);
        $vrsta = isset($atr['land_use_id']) ? (int) $atr['land_use_id'] : null;

        return [
            'id'            => $arkod,
            'povrsina_ha'   => isset($atr['area']) ? round(((float) $atr['area']) / 10000, 2) : 0.0,
            'naziv'         => ($atr['home_name'] ?? '') !== '' ? $atr['home_name'] : null,
            'vrsta_uporabe' => $vrsta ? (self::VRSTE_UPORABE[$vrsta] ?? "šifra $vrsta") : null,
            'vodozastita'   => isset($atr['water_protect_zone']) && ! in_array($atr['water_protect_zone'], ['', 'Vz0'], true) ? $atr['water_protect_zone'] : null,
            'natura2000'    => isset($atr['natura2000']) && (float) $atr['natura2000'] > 0,
            'poligon'       => $poligon,
            'centar'        => [round((min($lats) + max($lats)) / 2, 6), round((min($lngs) + max($lngs)) / 2, 6)],
        ];
    }
}
