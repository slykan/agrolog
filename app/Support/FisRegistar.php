<?php

namespace App\Support;

use App\Models\SzbSredstvo;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Registar sredstava za zaštitu bilja iz javne tražilice FIS-a (fis.mps.hr/trazilicaszb).
 * Jedan POST vraća sva sredstva (~900) s registracijskim brojem i statusom registracije.
 */
class FisRegistar
{
    private const URL = 'https://fis.mps.hr/fis-public-bff/api/v1/public-search/plant-protection-product/search';

    /** Preuzme cijeli registar i osvježi tablicu szb_sredstva. Vraća broj sredstava. */
    public static function sinkroniziraj(): int
    {
        $odgovor = Http::timeout(60)->acceptJson()->post(self::URL . '?page=0&size=5000', (object) []);
        $sredstva = $odgovor->successful() ? ($odgovor->json('content') ?? []) : [];
        if (! $sredstva) {
            throw new RuntimeException('FIS registar nije vratio sredstva (HTTP ' . $odgovor->status() . ').');
        }

        $sada = now();
        $redovi = array_map(fn ($s) => [
            'fis_id'              => (int) $s['pppId'],
            'naziv'               => trim(preg_replace('/\s+/u', ' ', trim((string) $s['productName'], " \"\t\n"))),
            'registracijski_broj' => ($s['productRegistrationNumber'] ?? null) !== null ? trim($s['productRegistrationNumber']) : null,
            'nositelj'            => $s['registrationOwner'] ?? null,
            'stari_naziv'         => $s['oldProductName'] ?? null,
            'aktivno'             => (bool) ($s['active'] ?? true),
            'created_at'          => $sada,
            'updated_at'          => $sada,
        ], array_filter($sredstva, fn ($s) => isset($s['pppId'], $s['productName'])));

        foreach (array_chunk($redovi, 200) as $dio) {
            SzbSredstvo::upsert($dio, ['fis_id'], ['naziv', 'registracijski_broj', 'nositelj', 'stari_naziv', 'aktivno', 'updated_at']);
        }

        return count($redovi);
    }
}
