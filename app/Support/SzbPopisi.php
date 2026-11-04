<?php

namespace App\Support;

/**
 * Popisi iz Ministarstvova predloška evidencije SZB (EU 2023/564, obvezno strojno čitljivo od 1.1.2027.):
 * vrsta uporabe, sustav proizvodnje, usjev → EPPO kod, fenofaze (BBCH).
 * Tekstovi su doslovno kao u predlošku, da izvoz odgovara njihovim padajućim listama.
 */
class SzbPopisi
{
    /** ključ => tekst iz predloška (list "vrsta uporabe", stupac B) */
    public const VRSTE_UPORABE = [
        'otvoreno'     => 'Poljoprivredne površine na otvorenom prostoru',
        'plastenik'    => 'Poljoprivredne površine u privremeno zatvorenom prostoru (npr. plastenici)',
        'nepoljopriv'  => 'Nepoljoprivredne površine (prostori za rekreaciju, željezničke pruge, područja koja nisu pod usjevima)',
        'zatvoreno'    => 'Tretiranje zatvorenih prostora ili u zatvorenim prostorima (npr. zamagljivanje/prskanje objekata za skladištenje, praznih skladišnih prostora za žitarice ili trajnih staklenika',
        'sjeme'        => 'Tretiranje sjemena ili biljnog reprodukcijskog materijala',
        'nakon_berbe'  => 'Tretiranje nakon berbe plodova',
    ];

    /** kratki nazivi za ekran */
    public const VRSTE_UPORABE_KRATKO = [
        'otvoreno'    => 'Na otvorenom',
        'plastenik'   => 'Plastenik / privremeno zatvoreno',
        'nepoljopriv' => 'Nepoljoprivredna površina',
        'zatvoreno'   => 'Zatvoreni prostor / skladište',
        'sjeme'       => 'Tretiranje sjemena',
        'nakon_berbe' => 'Nakon berbe',
    ];

    /** list "vrsta uporabe", stupac E */
    public const SUSTAVI = [
        'neekoloska' => 'Neekološka površina',
        'ekoloska'   => 'Ekološka površina',
    ];

    /**
     * Najčešće kulture za prijedloge pri unosu (nazivi točno kao na Ministarstvovu EPPO popisu,
     * pa se EPPO kod uvijek prepozna). Može se upisati i bilo koja druga kultura.
     */
    public const CESTE_KULTURE = [
        'Pšenica', 'Ječam', 'Kukuruz', 'Kukuruz za silažu', 'Kukuruz šećerac', 'Soja', 'Suncokret', 'Uljana repica',
        'Šećerna repa', 'Zob', 'Raž', 'Pšenoraž', 'Proso', 'Sirak', 'Lucerna', 'Djetelina', 'Crvena djetelina', 'Travnjaci',
        'Krumpir', 'Duhan', 'Konoplja', 'Vinova loza', 'Jabuka', 'Kruška', 'Šljiva', 'Trešnja', 'Višnja', 'Breskva',
        'Marelica', 'Orah', 'Lijeska', 'Maslina', 'Mandarina', 'Jagoda', 'Malina', 'Borovnica', 'Rajčica', 'Paprika',
        'Krastavac', 'Lubenica', 'Dinja', 'Luk', 'Češnjak', 'Mrkva', 'Kupus', 'Zelena salata', 'Grah', 'Grašak',
        'Bundeva', 'Tikva', 'Lavanda', 'Kamilica',
    ];

    private static ?array $eppo = null;
    private static ?array $bbch = null;

    /** @return array<string, string> naziv usjeva => EPPO kod */
    public static function eppo(): array
    {
        return self::$eppo ??= require resource_path('data/szb/eppo.php');
    }

    /** @return list<string> "Kultura - opis faze" */
    public static function bbch(): array
    {
        return self::$bbch ??= require resource_path('data/szb/bbch.php');
    }

    /**
     * Usjev s Ministarstvova popisa za naziv kulture koji je korisnik upisao:
     * točan naziv, pa naziv koji počinje istom riječju ("Pšenica ozima" → "Pšenica").
     *
     * @return array{0: string, 1: string}|null [naziv s popisa, EPPO kod]
     */
    public static function usjevZaKulturu(?string $naziv): ?array
    {
        $naziv = trim((string) $naziv);
        if ($naziv === '') {
            return null;
        }

        $popis = self::eppo();
        $mali = mb_strtolower($naziv);

        foreach ($popis as $ime => $kod) {
            if (mb_strtolower($ime) === $mali) {
                return [$ime, $kod];
            }
        }

        $prva = mb_strtolower(preg_split('/[\s,(]+/u', $naziv)[0]);
        foreach ($popis as $ime => $kod) {
            if (mb_strtolower($ime) === $prva) {
                return [$ime, $kod];
            }
        }

        return null;
    }

    /** Fenofaze za usjev (po nazivu s popisa ili nazivu kulture); prazno = cijeli popis. */
    public static function bbchZaUsjev(?string $usjev): array
    {
        $prva = mb_strtolower(preg_split('/[\s,(]+/u', trim((string) $usjev))[0] ?? '');
        if ($prva === '') {
            return [];
        }

        return array_values(array_filter(
            self::bbch(),
            fn ($f) => str_starts_with(mb_strtolower(ltrim($f)), $prva),
        ));
    }
}
