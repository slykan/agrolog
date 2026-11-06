<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;
use RuntimeException;
use ZipArchive;

/**
 * Popunjava Ministarstvov predložak "Evidencija SBZ.xlsx" (list "tablica", od retka 5) zapisima prskanja,
 * tako da izvoz ima točno njihove stupce, padajuće liste i listove s popisima.
 * Radi izravno nad XML-om u .xlsx (ZipArchive), bez dodatnih biblioteka.
 */
class SzbExcelIzvoz
{
    private const PREDLOZAK = 'data/szb/evidencija-szb-predlozak.xlsx';
    private const LIST = 'xl/worksheets/sheet2.xml';   // "tablica"
    private const PRVI_RED = 5;

    /** @param Collection<int, object> $redovi prskanja s kultura_naziv, eppo_naziv, eppo_kod, arkod_broj */
    public static function napravi(Collection $redovi, User $korisnik): string
    {
        $put = tempnam(sys_get_temp_dir(), 'szb') . '.xlsx';
        copy(resource_path(self::PREDLOZAK), $put);

        $zip = new ZipArchive();
        if ($zip->open($put) !== true) {
            throw new RuntimeException('Predložak evidencije nije moguće otvoriti.');
        }

        $xml = $zip->getFromName(self::LIST);

        // korisnik uz natpis "Profesionalni korisnik (ime, prezime, OIB):"
        $korisnikTekst = trim($korisnik->name . ($korisnik->oib ? ', OIB ' . $korisnik->oib : '')
            . ($korisnik->naziv_gospodarstva ? ' — ' . $korisnik->naziv_gospodarstva : ''));
        $xml = preg_replace('#<c r="D3"[^>]*/>|<c r="D3"[^>]*>.*?</c>#s', self::tekst('D3', 7, $korisnikTekst), $xml, 1);

        $broj = fn ($v) => $v === null ? '' : str_replace('.', ',', rtrim(rtrim(number_format((float) $v, 3, '.', ''), '0'), '.'));
        $zadnjiRed = self::PRVI_RED - 1;

        foreach ($redovi->values() as $i => $r) {
            $red = self::PRVI_RED + $i;
            $zadnjiRed = $red;
            $celije = [
                self::tekst("B$red", 13, ($i + 1) . '.'),
                self::tekst("C$red", 1, SzbPopisi::VRSTE_UPORABE[$r->vrsta_uporabe ?? 'otvoreno'] ?? ''),
                self::tekst("D$red", 1, (string) $r->trgovacki_naziv_sredstva),
                self::tekst("E$red", 4, (string) $r->registracijski_broj),
                self::tekst("F$red", 4, $r->datum_tretiranja ? $r->datum_tretiranja->format('d.m.Y.') : ''),
                self::tekst("G$red", 4, $r->vrijeme_od ? substr($r->vrijeme_od, 0, 5) : ''),
                self::tekst("H$red", 4, $r->kolicina_sredstva_l_ha !== null ? $broj($r->kolicina_sredstva_l_ha) . ' l/ha' : ''),
                self::tekst("I$red", 4, $r->rucno_ucrtana ? \App\Models\Parcela::oznaka($r->arkod_broj, true) : ($r->arkod_broj ? 'ARKOD ' . $r->arkod_broj : '')),
                self::tekst("J$red", 4, $r->tretirana_povrsina_ha !== null ? $broj($r->tretirana_povrsina_ha) . ' ha' : ''),
                self::tekst("K$red", 4, (string) ($r->eppo_naziv ?: $r->kultura_naziv)),
                // vrijednost umjesto VLOOKUP formule: kod ostaje i kad naziv kulture nije s popisa
                self::tekst("L$red", 4, (string) $r->eppo_kod),
                self::tekst("M$red", 4, (string) $r->bbch),
                self::tekst("N$red", 14, SzbPopisi::SUSTAVI[$r->sustav_proizvodnje ?? 'neekoloska'] ?? ''),
            ];
            $xml = self::zamijeniRed($xml, $red, '<row r="' . $red . '" spans="2:15">' . implode('', $celije) . '</row>');
        }

        // primjeri iz predloška (redovi 5 i 6) brišu se ako ih nismo prepisali
        for ($red = $zadnjiRed + 1; $red <= self::PRVI_RED + 1; $red++) {
            $xml = self::zamijeniRed($xml, $red, '<row r="' . $red . '" spans="2:15">'
                . self::tekst("B$red", 13, ($red - self::PRVI_RED + 1) . '.') . '</row>');
        }

        $zip->addFromString(self::LIST, $xml);

        // L-stupac više nema formula pa calcChain ne smije na njih upućivati (Excel bi tražio popravak)
        $zip->deleteName('xl/calcChain.xml');
        $zip->addFromString('xl/_rels/workbook.xml.rels', preg_replace(
            '#<Relationship [^>]*calcChain[^>]*/>#', '', $zip->getFromName('xl/_rels/workbook.xml.rels')));
        $zip->addFromString('[Content_Types].xml', preg_replace(
            '#<Override [^>]*calcChain[^>]*/>#', '', $zip->getFromName('[Content_Types].xml')));

        $zip->close();

        return $put;
    }

    private static function tekst(string $ref, int $stil, string $v): string
    {
        if ($v === '') {
            return '<c r="' . $ref . '" s="' . $stil . '"/>';
        }

        return '<c r="' . $ref . '" s="' . $stil . '" t="inlineStr"><is><t xml:space="preserve">'
            . htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</t></is></c>';
    }

    private static function zamijeniRed(string $xml, int $red, string $novi): string
    {
        $uzorak = '#<row r="' . $red . '"[^>]*?(?:/>|>.*?</row>)#s';
        if (preg_match($uzorak, $xml)) {
            return preg_replace($uzorak, $novi, $xml, 1);
        }

        // red još ne postoji u predlošku (iza zadnjeg) — dodaje se na kraj, redovi idu redom
        return str_replace('</sheetData>', $novi . '</sheetData>', $xml);
    }
}
