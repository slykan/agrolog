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
            ['slug' => 'pregled-prskalice-svake-tri-godine', 'title' => 'Pregled prskalice: svake tri godine u ovlaštenu ispitnu stanicu', 'excerpt' => 'Koji strojevi moraju na pregled, rokovi za nove i rabljene prskalice, izuzeća za leđne prskalice, kako pripremiti stroj i što se provjerava prema HRN EN ISO 16122.', 'date' => '2026-10-26', 'read' => '5 min'],
            ['slug' => 'kalibracija-prskalice-korak-po-korak', 'title' => 'Kalibracija prskalice korak po korak: koliko litara po hektaru', 'excerpt' => 'Izmjeri brzinu i protok sapnica i izračunaj stvarni utrošak vode po hektaru. Formula s primjerima za ratarske prskalice i atomizere u voćnjaku i vinogradu.', 'date' => '2026-10-26', 'read' => '6 min'],
            ['slug' => 'izobrazba-za-pesticide-obnova-znanja', 'title' => 'Izobrazba za primjenu pesticida: ispit, potvrda i obnova svakih 5 godina', 'excerpt' => 'Tko mora imati izobrazbu, kako izgledaju osnovni i dopunski modul i ispit, što je potvrda u FIS-u i kako na vrijeme obnoviti znanje.', 'date' => '2026-10-26', 'read' => '5 min'],
            ['slug' => 'zastita-pcela-pri-prskanju', 'title' => 'Zaštita pčela pri prskanju: cvatnja, obavijest pčelarima i dobra praksa', 'excerpt' => 'Zabrana sredstava opasnih za pčele u cvatnji, obavještavanje pčelara, košnja cvatućeg pokrova u nasadima i praktični savjeti kako izbjeći trovanje pčela.', 'date' => '2026-10-26', 'read' => '5 min'],
            ['slug' => 'nitrati-ranjiva-podrucja-zabrana-gnojidbe', 'title' => 'Nitrati i ranjiva područja: kada je gnojidba zabranjena i koliko dušika smije', 'excerpt' => 'Razdoblja zabrane za gnojnicu i gnojovku, udaljenosti od voda, granica od 170 kg N/ha iz stajskog gnoja, najveće količine dušika po kulturi i skladištenje gnoja (NN 95/25).', 'date' => '2026-10-26', 'read' => '7 min'],
            ['slug' => 'evidencija-gnojidbe-sto-upisati', 'title' => 'Evidencija gnojidbe: što upisati i kako je voditi', 'excerpt' => 'Podaci koje evidencija gnojidbe mora sadržavati prema Akcijskom programu, primjer izračuna kilograma dušika po hektaru i savjeti za urednu evidenciju.', 'date' => '2026-10-26', 'read' => '5 min'],
            ['slug' => 'analiza-tla-uzimanje-uzorka', 'title' => 'Analiza tla: kako pravilno uzeti uzorak i čitati nalaz', 'excerpt' => 'Kada i na kojoj dubini uzorkovati, koliko poduzoraka uzeti, kako označiti uzorak i što znače pH, humus, fosfor i kalij u nalazu.', 'date' => '2026-10-26', 'read' => '6 min'],
            ['slug' => 'skladistenje-pesticida-i-ambalaza', 'title' => 'Skladištenje sredstava za zaštitu bilja, ostaci škropiva i prazna ambalaža', 'excerpt' => 'Kako čuvati pesticide na gospodarstvu, gdje pripremati škropivo, zaštitni pojas uz vode, što s ostacima u prskalici i kako zbrinuti praznu ambalažu.', 'date' => '2026-10-26', 'read' => '6 min'],
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
