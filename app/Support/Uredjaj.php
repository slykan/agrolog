<?php

namespace App\Support;

use Illuminate\Http\Request;

/** Vrsta uređaja iz User-Agenta: na mobitelu i u Android aplikaciji ispis se zamjenjuje preuzimanjem PDF-a. */
class Uredjaj
{
    public static function jeMobilni(?Request $request = null): bool
    {
        $ua = (string) ($request ?? request())->userAgent();

        return str_contains($ua, 'AgroLogNative/') || (bool) preg_match('/Android|iPhone|iPad|iPod|Mobile/i', $ua);
    }
}
