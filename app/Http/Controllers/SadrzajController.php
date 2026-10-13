<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Javni sadržaj AgroLoga — besplatni alati i vodiči (bez prijave).
 */
class SadrzajController extends Controller
{
    public const ALATI = [
        'kalkulator-skropiva' => ['naziv' => 'Kalkulator škropiva', 'ico' => '💧', 'opis' => 'Koliko sredstva i vode ide u spremnik prskalice i koliko punjenja treba za tvoju površinu.'],
        'kalkulator-gnojiva' => ['naziv' => 'Kalkulator gnojiva', 'ico' => '🌱', 'opis' => 'Iz željenih kg dušika, fosfora ili kalija po hektaru izračunaj koliko KAN-a, UREE ili NPK gnojiva treba.'],
        'kalkulator-karence' => ['naziv' => 'Kalkulator karence', 'ico' => '📅', 'opis' => 'Upiši datum tretiranja i karencu sredstva — dobiješ najraniji dopušteni datum berbe.'],
        'pretvornik-povrsina' => ['naziv' => 'Pretvornik površina', 'ico' => '📐', 'opis' => 'Hektari, ari, m², katastarska jutra i četvorni hvati — preračunaj u svim smjerovima.'],
    ];

    public static function vodici(): array
    {
        return [
            ['slug' => 'elektronicka-evidencija-sredstava-za-zastitu-bilja', 'title' => 'Elektronička evidencija sredstava za zaštitu bilja: što se mijenja 2026. i 2027.', 'excerpt' => 'Profesionalni korisnici sredstava za zaštitu bilja evidenciju moraju voditi elektronički i u strojno čitljivom obliku. Što mora pisati u evidenciji i do kada se treba prilagoditi.', 'date' => '2026-10-13', 'read' => '6 min'],
            ['slug' => 'kako-izracunati-skropivo-za-prskalicu', 'title' => 'Kako izračunati sredstvo i vodu za prskalicu', 'excerpt' => 'Doza po hektaru, utrošak vode i volumen spremnika — formula korak po korak, s primjerima za ratare, voćare i vinogradare.', 'date' => '2026-10-13', 'read' => '5 min'],
            ['slug' => 'karenca-sto-je-i-kako-je-racunati', 'title' => 'Karenca: što je, kako se računa i zašto je važna', 'excerpt' => 'Karenca je najkraće vrijeme od zadnjeg tretiranja do berbe. Gdje je pronaći, kako je izračunati i što ako se tretira više puta.', 'date' => '2026-10-13', 'read' => '4 min'],
            ['slug' => 'gnojidba-dusikom-kan-urea-npk-izracun', 'title' => 'Gnojidba dušikom: kako iz kg hraniva izračunati KAN, UREU ili NPK', 'excerpt' => 'Preporuke gnojidbe izražene su u kg čistog hraniva po hektaru. Kako ih pretvoriti u vreće gnojiva i na što paziti u ranjivim područjima.', 'date' => '2026-10-13', 'read' => '6 min'],
        ];
    }

    public function alati(): View
    {
        return view('alati.index', ['alati' => self::ALATI]);
    }

    public function alat(string $slug): View
    {
        abort_unless(isset(self::ALATI[$slug]) && view()->exists('alati.'.$slug), 404);

        return view('alati.'.$slug, ['alat' => self::ALATI[$slug], 'ostali' => array_diff_key(self::ALATI, [$slug => true])]);
    }

    public function vodiciIndex(): View
    {
        return view('vodici.index', ['vodici' => self::vodici()]);
    }

    public function vodic(string $slug): View
    {
        $v = collect(self::vodici())->firstWhere('slug', $slug);
        abort_unless($v && view()->exists('vodici.posts.'.$slug), 404);

        return view('vodici.show', ['v' => $v, 'ostali' => collect(self::vodici())->where('slug', '!=', $slug)->values()->all()]);
    }
}
