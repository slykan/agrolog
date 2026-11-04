<?php

namespace App\Support;

use App\Models\Gnojidba;
use App\Models\Prskanje;
use App\Models\SkladisteUlaz;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Stanje skladišta sredstava za zaštitu bilja i gnojiva.
 *
 * Prati se samo artikl za koji postoji barem jedan ulaz (nabava). Potrošnja se ne sprema nego računa
 * iz evidencije: prskanje = doza (L ili kg po ha) × tretirana površina, gnojidba = kg/ha × površina kulture.
 * Računaju se samo zapisi od datuma prvog ulaza tog artikla, da stara evidencija ne ode u minus.
 */
class Skladiste
{
    public const VRSTE = ['szb' => 'Sredstvo za zaštitu bilja', 'gnojivo' => 'Gnojivo'];

    /** Udio nabavljene količine ispod kojeg je artikl "pri kraju". */
    public const PRAG_PRI_KRAJU = 0.15;

    /** Ključ artikla iz naziva: velika slova, jednostruki razmaci. */
    public static function kljuc(?string $naziv): string
    {
        return mb_strtoupper(trim(preg_replace('/\s+/u', ' ', (string) $naziv)));
    }

    /**
     * Svi praćeni artikli korisnika sa stanjem.
     *
     * @return Collection<string, array> ključ "vrsta|NAZIV"
     */
    public static function stanje(User $user, bool $sPotrosnjom = false): Collection
    {
        $ulazi = SkladisteUlaz::where('user_id', $user->id)->orderBy('datum')->orderBy('id')->get();
        if ($ulazi->isEmpty()) {
            return collect();
        }

        $artikli = [];
        foreach ($ulazi as $u) {
            $k = $u->vrsta . '|' . self::kljuc($u->naziv);
            $a = $artikli[$k] ?? [
                'kljuc' => $k, 'vrsta' => $u->vrsta, 'naziv' => $u->naziv, 'jedinica' => $u->jedinica,
                'od' => $u->datum, 'nabavljeno' => 0.0, 'korekcije' => 0.0, 'potroseno' => 0.0,
                'placeno' => 0.0, 'placena_kolicina' => 0.0, 'ulazi' => [], 'potrosnja' => [],
            ];
            // naziv i jedinica iz zadnjeg ulaza
            $a['naziv'] = $u->naziv;
            $a['jedinica'] = $u->jedinica;
            if ($u->korekcija) {
                $a['korekcije'] += (float) $u->kolicina;
            } else {
                $a['nabavljeno'] += (float) $u->kolicina;
                if ($u->cijena !== null && (float) $u->kolicina > 0) {
                    $a['placeno'] += (float) $u->cijena;
                    $a['placena_kolicina'] += (float) $u->kolicina;
                }
            }
            $a['ulazi'][] = $u;
            $artikli[$k] = $a;
        }

        $od = $ulazi->min('datum')->toDateString();
        $trazeni = collect($artikli)->groupBy('vrsta')->map(fn ($g) => $g->keys()->all());

        if (isset($trazeni['szb'])) {
            $prskanja = Prskanje::where('prskanja.user_id', $user->id)
                ->where('prskanja.datum_tretiranja', '>=', $od)
                ->whereNotNull('prskanja.kolicina_sredstva_l_ha')
                ->join('kulture', 'kulture.id', '=', 'prskanja.kultura_id')
                ->join('parcele', 'parcele.id', '=', 'kulture.parcela_id')
                ->orderBy('prskanja.datum_tretiranja')->orderBy('prskanja.id')
                ->get(['prskanja.id', 'prskanja.datum_tretiranja', 'prskanja.trgovacki_naziv_sredstva', 'prskanja.kolicina_sredstva_l_ha',
                       'prskanja.tretirana_povrsina_ha', 'kulture.posadjena_povrsina_ha', 'kulture.naziv as kultura_naziv', 'parcele.arkod_broj']);
            foreach ($prskanja as $p) {
                $k = 'szb|' . self::kljuc($p->trgovacki_naziv_sredstva);
                $povrsina = (float) ($p->tretirana_povrsina_ha ?? $p->posadjena_povrsina_ha);
                self::dodajPotrosnju($artikli, $k, $p->datum_tretiranja, (float) $p->kolicina_sredstva_l_ha, $povrsina, $p);
            }
        }

        if (isset($trazeni['gnojivo'])) {
            $gnojidbe = Gnojidba::where('gnojidbe.user_id', $user->id)
                ->where('gnojidbe.datum', '>=', $od)
                ->join('kulture', 'kulture.id', '=', 'gnojidbe.kultura_id')
                ->join('parcele', 'parcele.id', '=', 'kulture.parcela_id')
                ->orderBy('gnojidbe.datum')->orderBy('gnojidbe.id')
                ->get(['gnojidbe.id', 'gnojidbe.datum', 'gnojidbe.tip_gnojiva', 'gnojidbe.kolicina_kg_ha',
                       'kulture.posadjena_povrsina_ha', 'kulture.naziv as kultura_naziv', 'parcele.arkod_broj']);
            foreach ($gnojidbe as $g) {
                $k = 'gnojivo|' . self::kljuc($g->tip_gnojiva);
                self::dodajPotrosnju($artikli, $k, $g->datum, (float) $g->kolicina_kg_ha, (float) $g->posadjena_povrsina_ha, $g);
            }
        }

        return collect($artikli)->map(function ($a) use ($sPotrosnjom) {
            $a['stanje'] = round($a['nabavljeno'] + $a['korekcije'] - $a['potroseno'], 3);
            $a['cijena_po_jedinici'] = $a['placena_kolicina'] > 0 ? round($a['placeno'] / $a['placena_kolicina'], 2) : null;
            $a['vrijednost'] = $a['cijena_po_jedinici'] !== null ? round(max($a['stanje'], 0) * $a['cijena_po_jedinici'], 2) : null;
            $a['status'] = $a['stanje'] < -0.0005 ? 'minus'
                : ($a['stanje'] < 0.0005 || ($a['nabavljeno'] > 0 && $a['stanje'] < $a['nabavljeno'] * self::PRAG_PRI_KRAJU) ? 'malo' : 'ok');
            if (! $sPotrosnjom) {
                unset($a['ulazi'], $a['potrosnja']);
            }

            return $a;
        })->sortBy(fn ($a) => [$a['vrsta'] === 'szb' ? 0 : 1, self::kljuc($a['naziv'])]);
    }

    private static function dodajPotrosnju(array &$artikli, string $k, $datum, float $doza, float $povrsina, $zapis): void
    {
        if (! isset($artikli[$k]) || $datum->lt($artikli[$k]['od'])) {
            return;
        }
        $kolicina = round($doza * $povrsina, 3);
        $artikli[$k]['potroseno'] += $kolicina;
        $artikli[$k]['potrosnja'][] = [
            'datum' => $datum, 'arkod' => $zapis->arkod_broj, 'kultura' => $zapis->kultura_naziv,
            'doza' => $doza, 'povrsina' => $povrsina, 'kolicina' => $kolicina,
        ];
    }

    /** Stanje jednog artikla (za napomenu u formi prskanja i gnojidbe), ili null ako se ne prati. */
    public static function artikl(User $user, string $vrsta, ?string $naziv): ?array
    {
        if (! $user->koristi_skladiste || blank($naziv)) {
            return null;
        }

        return self::stanje($user)->get($vrsta . '|' . self::kljuc($naziv));
    }

    /**
     * Napomena uz naziv u formi prskanja ili gnojidbe: trenutno stanje i, ako su poznati doza i površina,
     * stanje nakon ovog zapisa. Null ako korisnik ne koristi skladište ili se artikl ne prati.
     *
     * @return array{tekst: string, minus: bool}|null
     */
    public static function napomena(User $user, string $vrsta, ?string $naziv, $doza = null, $povrsina = null): ?array
    {
        $a = self::artikl($user, $vrsta, $naziv);
        if (! $a) {
            return null;
        }
        $j = $a['jedinica'];
        $tekst = 'Na skladištu: ' . self::broj($a['stanje'], 3) . ' ' . $j;
        $nakon = $a['stanje'];
        if (is_numeric($doza) && is_numeric($povrsina) && (float) $doza > 0 && (float) $povrsina > 0) {
            $trosi = round((float) $doza * (float) $povrsina, 3);
            $nakon = round($a['stanje'] - $trosi, 3);
            $tekst .= ' · ovaj zapis troši ' . self::broj($trosi, 3) . ' ' . $j . ' → ostaje ' . self::broj($nakon, 3) . ' ' . $j;
        }
        if ($nakon < -0.0005) {
            $tekst .= ' (nema dovoljno na stanju, zapis se svejedno sprema)';
        }

        return ['tekst' => $tekst, 'minus' => $nakon < -0.0005];
    }

    /** Broj za prikaz: 1234.5 → 1.234,5 (bez suvišnih nula). */
    public static function broj($v, int $decimale = 2): string
    {
        $s = number_format((float) $v, $decimale, ',', '.');

        return str_contains($s, ',') ? rtrim(rtrim($s, '0'), ',') : $s;
    }
}
