<?php

namespace App\Support;

use App\Models\Kultura;
use App\Models\Prskanje;
use App\Models\SzbSredstvo;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Provjera prskanja prema registraciji sredstva u FIS-u: je li registrirano za kulturu, karenca do berbe/žetve
 * i najveći broj tretmana. Doze su u FIS-u slobodan tekst, pa se ne provjeravaju nego prikazuju.
 */
class ProvjeraSzb
{
    private const URL = 'https://fis.mps.hr/fis-public-bff/api/v1/public-search/plant-protection-product/';
    private const VRIJEDI_DANA = 30;

    /**
     * Sažeti detalji registracije: [['kultura' => 'Pšenica', 'karenca_dana' => 35|null, 'karenca' => '35 dana',
     * 'max' => 2|null, 'primjene' => [['organizam', 'uporaba', 'napomena', 'max']]]]. Null ako FIS nije dostupan.
     *
     * @param bool $dohvati smije li se pitati FIS (u zvonu i popisima ne — samo spremljeno)
     */
    public static function detalji(SzbSredstvo $s, bool $dohvati = true): ?array
    {
        $svjeze = $s->detalji_at && $s->detalji_at->gt(now()->subDays(self::VRIJEDI_DANA));
        if ($s->detalji !== null && ($svjeze || ! $dohvati)) {
            return $s->detalji;
        }
        if (! $dohvati || Cache::has('fis-nedostupan')) {
            return $s->detalji;
        }
        try {
            $o = Http::timeout(8)->acceptJson()->get(self::URL . $s->fis_id);
            if (! $o->successful() || ! is_array($o->json('culturesAndHarmfulOrganisms'))) {
                return $s->detalji;
            }
        } catch (\Throwable) {
            Cache::put('fis-nedostupan', true, 600);   // ne pitaj FIS ponovno 10 minuta
            return $s->detalji;   // FIS ne odgovara: koristi staro ako postoji
        }

        $kulture = [];
        foreach (array_merge($o->json('culturesAndHarmfulOrganisms') ?? [], $o->json('culturesAndHarmfulOrganismsWithSmallPurpose') ?? []) as $c) {
            $naziv = trim((string) ($c['culture']['name']['hr'] ?? ''));
            if ($naziv === '') {
                continue;
            }
            $karenca = trim((string) ($c['culture']['safetyPeriod'] ?? ''));
            $primjene = array_map(fn ($h) => [
                'organizam' => trim((string) ($h['name']['hr'] ?? '')),
                'uporaba' => trim((string) ($h['usage'] ?? '')),
                'napomena' => trim((string) ($h['additionalInfo'] ?? '')),
                'max' => isset($h['maxNumberOfTreatmentsPerSeason']) ? (int) $h['maxNumberOfTreatmentsPerSeason'] : null,
            ], $c['harmfulOrganisms'] ?? []);
            $maxovi = array_filter(array_merge([$c['culture']['maxNumberOfTreatmentsPerSeason'] ?? null], array_column($primjene, 'max')), fn ($m) => $m !== null);
            $kulture[] = [
                'kultura' => $naziv,
                'karenca' => $karenca,
                'karenca_dana' => preg_match('/(\d+)\s*dan/u', $karenca, $m) ? (int) $m[1] : null,
                'max' => $maxovi ? (int) max($maxovi) : null,   // najblaže ograničenje među namjenama
                'primjene' => $primjene,
                'mala_namjena' => (bool) ($c['isSmallPurpose'] ?? false),
            ];
        }
        $s->forceFill(['detalji' => $kulture, 'detalji_at' => now()])->save();

        return $kulture;
    }

    /** Normalizirani naziv kulture za usporedbu (mala slova, bez dijakritika i zagrada). */
    public static function kljuc(?string $t): string
    {
        $t = mb_strtolower(trim((string) $t));
        $t = strtr($t, ['č' => 'c', 'ć' => 'c', 'š' => 's', 'ž' => 'z', 'đ' => 'd']);

        return trim(preg_replace('/\s+/', ' ', preg_replace('/\(.*?\)|[^a-z0-9 ]/', ' ', $t)));
    }

    /** Registracija za kulturu: točno ime, pa sadrži (npr. "ozima pšenica" ↔ "pšenica"), i preko EPPO naziva. */
    public static function zaKulturu(array $detalji, Kultura $k): ?array
    {
        $trazi = array_filter([self::kljuc($k->naziv), self::kljuc($k->eppo_naziv)]);
        foreach ([fn ($a, $b) => $a === $b, fn ($a, $b) => str_contains($a, $b) || str_contains($b, $a)] as $usporedi) {
            foreach ($detalji as $d) {
                $fis = self::kljuc($d['kultura']);
                foreach ($trazi as $t) {
                    if ($fis !== '' && $usporedi($t, $fis)) {
                        return $d;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Provjera za formu prskanja.
     *
     * @return array{stavke: array<int, array{razina: string, tekst: string}>, primjene: array, kultura_fis: ?string}|null
     */
    public static function provjeri(User $user, $kulturaId, ?string $sredstvo, $datum = null, $doza = null, ?int $bezPrskanja = null): ?array
    {
        if (blank($sredstvo) || ! $kulturaId) {
            return null;
        }
        $s = SzbSredstvo::poNazivu($sredstvo);
        $k = Kultura::where('user_id', $user->id)->find((int) $kulturaId);
        if (! $s || ! $k) {
            return null;   // nije u registru: to već javlja napomena uz naziv
        }
        $detalji = self::detalji($s);
        if ($detalji === null) {
            return ['stavke' => [['razina' => 'info', 'tekst' => 'Registar FIS-a trenutno ne odgovara — provjera registracije nije moguća.']], 'primjene' => [], 'kultura_fis' => null];
        }

        $stavke = [];
        $reg = self::zaKulturu($detalji, $k);
        if (! $reg) {
            $popis = implode(', ', array_slice(array_unique(array_column($detalji, 'kultura')), 0, 8));
            $stavke[] = ['razina' => 'upozorenje', 'tekst' => "{$s->naziv} nije registriran za kulturu „{$k->naziv}”." . ($popis ? " Registriran je za: {$popis}." : '')];

            return ['stavke' => $stavke, 'primjene' => [], 'kultura_fis' => null];
        }
        $stavke[] = ['razina' => 'ok', 'tekst' => "Registriran za kulturu {$reg['kultura']}" . ($reg['mala_namjena'] ? ' (mala namjena)' : '') . '.'];

        // karenca
        if ($reg['karenca_dana']) {
            $d = $datum ? Carbon::parse($datum) : now();
            $stavke[] = ['razina' => 'info', 'tekst' => "Karenca {$reg['karenca_dana']} dana: berba/žetva ne prije " . $d->copy()->addDays($reg['karenca_dana'])->format('d.m.Y.')];
        } elseif ($reg['karenca'] !== '') {
            $stavke[] = ['razina' => 'info', 'tekst' => "Karenca: {$reg['karenca']}."];
        }

        // broj tretmana tim sredstvom na ovoj kulturi
        if ($reg['max']) {
            $kljuc = Skladiste::kljuc($s->naziv);
            $bilo = Prskanje::where('user_id', $user->id)->where('kultura_id', $k->id)
                ->when($bezPrskanja, fn ($q) => $q->where('id', '!=', $bezPrskanja))
                ->pluck('trgovacki_naziv_sredstva')->filter(fn ($n) => Skladiste::kljuc($n) === $kljuc)->count();
            $ovo = $bilo + 1;
            $stavke[] = $ovo > $reg['max']
                ? ['razina' => 'upozorenje', 'tekst' => "Ovo je {$ovo}. tretman ovim sredstvom na ovoj kulturi, a dozvoljeno je najviše {$reg['max']}."]
                : ['razina' => 'ok', 'tekst' => "{$ovo}. tretman od najviše {$reg['max']} dozvoljenih."];
        }

        return ['stavke' => $stavke, 'primjene' => array_slice($reg['primjene'], 0, 6), 'kultura_fis' => $reg['kultura']];
    }

    /**
     * Aktivne karence (za zvono): kulture na kojima je zadnje prskanje s poznatom karencom još traje. Samo spremljeni detalji.
     *
     * @return array<int, array{kultura: Kultura, sredstvo: string, do: Carbon}>
     */
    public static function aktivneKarence(User $user): array
    {
        $prskanja = Prskanje::where('user_id', $user->id)->where('datum_tretiranja', '>=', now()->subDays(150)->toDateString())
            ->whereNotNull('trgovacki_naziv_sredstva')->with('kultura.parcela')->get();
        $karence = [];
        foreach ($prskanja as $p) {
            if (! $p->kultura || $p->kultura->zavrsena_at) {
                continue;
            }
            $s = SzbSredstvo::poNazivu($p->trgovacki_naziv_sredstva);
            $detalji = $s ? self::detalji($s, false) : null;
            $reg = $detalji ? self::zaKulturu($detalji, $p->kultura) : null;
            if (! $reg || ! $reg['karenca_dana']) {
                continue;
            }
            $do = $p->datum_tretiranja->copy()->addDays($reg['karenca_dana']);
            if ($do->isFuture() && (! isset($karence[$p->kultura_id]) || $do->gt($karence[$p->kultura_id]['do']))) {
                $karence[$p->kultura_id] = ['kultura' => $p->kultura, 'sredstvo' => $s->naziv, 'do' => $do];
            }
        }

        return array_values($karence);
    }
}
