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

    /** Novosti o AgroLogu i propisima, najnovija prva (prva se ističe na naslovnici). */
    public static function novosti(): array
    {
        return [
            ['slug' => 'berba-i-trajni-nasadi', 'title' => 'Berba, žetva i trajni nasadi: AgroLog i za voćnjake i vinograde', 'kratko' => 'Berba i trajni nasadi', 'opis' => 'Upisujte svaku berbu s količinom i cijenom, uz upozorenje ako karenca nije prošla. Voćnjak, vinograd i maslinik sada ostaju na parceli iz godine u godinu.', 'excerpt' => 'Nova aktivnost Berba i žetva s provjerom karence i prihodom u Troškovima te trajni nasadi sa sortom, godinom sadnje i troškovima po godinama.', 'date' => '2026-11-06', 'read' => '2 min'],
            ['slug' => 'savjetnik-pristup', 'title' => 'Pozovite savjetnika: zajednička evidencija s agronomom ili zadrugom', 'kratko' => 'Pristup savjetniku', 'opis' => 'Pozovite savjetnika e-mailom da vidi ili vodi vašu evidenciju. Svaka njegova izmjena bilježi se, a pristup ukidate jednim klikom.', 'excerpt' => 'Gospodarstvo sada može pozvati savjetnika, agronoma ili zadrugu: samo pregled ili pregled i unos, s poviješću svih izmjena. Savjetnik vidi sve svoje klijente na jednom mjestu.', 'date' => '2026-11-05', 'read' => '2 min'],
            ['slug' => 'troskovi-po-parceli', 'title' => 'Troškovi: koliko vas stoji hektar svake kulture', 'kratko' => 'Troškovi po parceli i hektaru', 'opis' => 'Iz cijena nabave na skladištu i evidencije prskanja i gnojidbe AgroLog izračuna trošak zaštite i gnojidbe po parceli i po hektaru, s nabavom po mjesecima.', 'excerpt' => 'Nova stranica Troškovi: nabava sredstava i gnojiva po mjesecima i vrsti te trošak zaštite bilja i gnojidbe po parceli, kulturi i hektaru, izračunat iz skladišta i evidencije.', 'date' => '2026-11-05', 'read' => '2 min'],
            ['slug' => 'mehanizacija-rokovi-i-podsjetnici', 'title' => 'Mehanizacija: registracije, osiguranja i servisi s podsjetnikom mailom', 'kratko' => 'Mehanizacija i podsjetnici', 'opis' => 'Upišite traktore, priključke i vozila te do kada vrijedi registracija, osiguranje, servis ili pregled prskalice. AgroLog vas podsjeti mailom prije isteka.', 'excerpt' => 'Nova Mehanizacija: rokovi za registraciju, tehnički pregled, osiguranje, servis i pregled prskalice, s podsjetnikom mailom prije isteka i servisnom knjigom. Usput: skladište sada može imati više lokacija.', 'date' => '2026-11-05', 'read' => '3 min'],
            ['slug' => 'skladiste-sredstava-i-gnojiva', 'title' => 'Skladište: AgroLog sam oduzima što ste poprskali i pognojili', 'kratko' => 'Skladište sredstava i gnojiva', 'opis' => 'Upišite nabavu sredstava i gnojiva, a potrošnju iz prskanja i gnojidbe AgroLog oduzima sam. Uz upozorenje kad je nečega malo. Neobavezno: počinje kad dodate prvo skladište.', 'excerpt' => 'Novo neobavezno skladište: unesete nabavu, a AgroLog iz evidencije prskanja i gnojidbe sam računa potrošnju (doza × površina) i pokazuje stanje, vrijednost zalihe i što je pri kraju. Može više skladišta.', 'date' => '2026-11-05', 'read' => '3 min'],
            ['slug' => 'prognoza-i-dani-za-prskanje', 'title' => 'Prognoza za vaše parcele i dani povoljni za prskanje', 'kratko' => 'Prognoza i dani za prskanje', 'opis' => 'Na početnoj stranici je prognoza za 5 dana za područje vaših parcela, s oznakom koji su dani povoljni za prskanje — vjetar, kiša, mraz i temperatura.', 'excerpt' => 'AgroLog sada na početnoj stranici prikazuje prognozu za pet dana točno za područje vaših parcela i za svaki dan označi je li povoljan za prskanje: bez jačeg vjetra, kiše i mraza, na temperaturi od 5 do 30 °C. Usput: brži unos kultura s popisom najčešćih kultura.', 'date' => '2026-11-04', 'read' => '3 min'],
            ['slug' => 'arkod-parcele-na-karti', 'title' => 'ARKOD parcele na karti: upišite broj, AgroLog pronađe parcelu i površinu', 'kratko' => 'Parcele na karti iz ARKOD-a', 'opis' => 'Upišite ARKOD broj i parcela se odmah pokaže na satelitskoj karti, sa službenom površinom iz ARKOD-a. Na početnoj stranici je karta svih vaših parcela.', 'excerpt' => 'Pri unosu nove parcele dovoljno je upisati ARKOD broj: AgroLog je pronađe u javnom ARKOD-u, pokaže na satelitskoj karti i preuzme službenu površinu. Uz svaku parcelu je ikona karte, a na početnoj stranici karta svih vaših parcela.', 'date' => '2026-11-04', 'read' => '3 min'],
            ['slug' => 'sredstva-iz-registra-fis', 'title' => 'Sredstva za zaštitu bilja iz registra FIS-a i automatski registracijski broj', 'kratko' => 'Sredstva iz registra FIS-a', 'opis' => 'Pri unosu prskanja AgroLog nudi sva registrirana sredstva iz FIS-a i sam upisuje registracijski broj.', 'excerpt' => 'U evidenciji prskanja AgroLog sada nudi sva sredstva registrirana u Hrvatskoj (registar FIS-a Ministarstva) i sam upisuje registracijski broj. Upozorava i kad registracija sredstva nije aktivna.', 'date' => '2026-11-04', 'read' => '3 min'],
            ['slug' => 'gnojidba-kg-dusika-i-granica-170', 'title' => 'Gnojidba: kilogrami dušika po hektaru i granica od 170 kg N/ha', 'kratko' => 'Izračun dušika u gnojidbi', 'opis' => 'Odaberite gnojivo s popisa i AgroLog izračuna kg dušika po hektaru te prati granicu od 170 kg N/ha iz stajskog gnoja.', 'excerpt' => 'Evidencija gnojidbe sada zna sastav uobičajenih gnojiva (KAN, UREA, NPK, MAP, DAP, stajski gnoj, gnojovka…), sama računa kilograme dušika po hektaru i upozorava kad dušik iz stajskog gnoja prijeđe 170 kg N/ha.', 'date' => '2026-11-04', 'read' => '4 min'],
            ['slug' => 'evidencija-szb-format-ministarstva-2027', 'title' => 'Evidencija prskanja u formatu Ministarstva: AgroLog je spreman za 2027.', 'kratko' => 'Evidencija SZB u formatu Ministarstva za 2027.', 'opis' => 'EPPO kod uz svaku kulturu, fenofaza (BBCH) pri prskanju i izvoz evidencije izravno u Ministarstvovu Excel tablicu — obvezni format od 1.1.2027.', 'excerpt' => 'Od 1. siječnja 2027. evidencija o uporabi sredstava za zaštitu bilja vodi se u propisanom elektroničkom formatu. AgroLog sada bilježi EPPO kod, fenofazu (BBCH), vrstu uporabe i sustav proizvodnje te izvozi evidenciju izravno u Ministarstvovu Excel tablicu.', 'date' => '2026-11-04', 'read' => '4 min'],
        ];
    }

    public function novostiIndex(): View
    {
        return view('novosti.index', ['novosti' => self::novosti()]);
    }

    public function novost(string $slug): View
    {
        $n = collect(self::novosti())->firstWhere('slug', $slug);
        abort_unless($n && view()->exists('novosti.posts.'.$slug), 404);

        return view('novosti.show', ['n' => $n, 'ostali' => collect(self::novosti())->where('slug', '!=', $slug)->values()->all()]);
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
