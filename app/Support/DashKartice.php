<?php

namespace App\Support;

use App\Models\Gnojidba;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\Prskanje;
use App\Models\Stroj;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/** Podaci za šest kartica na početnoj: kratka brojka i mali graf. Sve aktivnosti i troškovi su za zadnjih 12 mjeseci. */
class DashKartice
{
    public const GNOJIDBA = '#008300';   // iste boje kao u Pregledu gospodarstva
    public const PRSKANJE = '#2a78d6';

    public static function za(User $user): array
    {
        $do = now();
        $od = now()->startOfMonth()->subMonths(11);

        return [
            self::parcele($user),
            self::aktivnost('gnojidba', 'Evidencija gnojidbe', 'Zapisi o primjeni gnojiva po parcelama.',
                Gnojidba::where('user_id', $user->id)->where('datum', '>=', $od->toDateString())->pluck('datum'), $od, self::GNOJIDBA),
            self::aktivnost('prskanje', 'Evidencija prskanja', 'Upotreba sredstava za zaštitu bilja.',
                Prskanje::where('user_id', $user->id)->where('datum_tretiranja', '>=', $od->toDateString())->pluck('datum_tretiranja'), $od, self::PRSKANJE),
            self::skladiste($user),
            self::mehanizacija($user),
            self::troskovi($user, $od, $do),
        ];
    }

    /** Hrvatska množina: 1 parcela, 2 parcele, 5 parcela (11–14 kao 5). */
    public static function mn(int $n, string $jedan, string $dva, string $pet): string
    {
        $d = $n % 10;
        $s = $n % 100;

        return $n . ' ' . ($d === 1 && $s !== 11 ? $jedan : ($d >= 2 && $d <= 4 && ($s < 12 || $s > 14) ? $dva : $pet));
    }

    private static function parcele(User $user): array
    {
        $kulture = Kultura::where('user_id', $user->id)->whereNull('zavrsena_at')->get(['naziv', 'posadjena_povrsina_ha']);
        $poNazivu = $kulture->groupBy(fn ($k) => mb_strtolower(trim($k->naziv)))
            ->map(fn (Collection $g) => ['naziv' => trim($g->first()->naziv), 'ha' => (float) $g->sum('posadjena_povrsina_ha')])
            ->sortByDesc('ha')->values();
        $najveca = max((float) $poNazivu->max('ha'), 0.01);
        $brojParcela = Parcela::where('user_id', $user->id)->count();

        return [
            'ruta' => 'parcele', 'naslov' => 'Parcele & Kulture', 'opis' => 'ARKOD parcele i kulture koje na njima uzgajate.', 'ikona' => 'parcele',
            'brojka' => number_format((float) Parcela::where('user_id', $user->id)->sum('povrsina_ha'), 2, ',', '.') . ' ha',
            'pod' => self::mn($brojParcela, 'parcela', 'parcele', 'parcela') . ' · ' . self::mn($kulture->count(), 'kultura', 'kulture', 'kultura'),
            'graf' => 'kulture',
            'redovi' => $poNazivu->take(3)->map(fn ($k) => $k + ['posto' => round($k['ha'] / $najveca * 100)])->all(),
            'prazno' => $brojParcela === 0 ? 'Dodajte prvu parcelu →' : ($kulture->isEmpty() ? 'Dodajte kulturu na parcelu →' : null),
        ];
    }

    private static function aktivnost(string $ruta, string $naslov, string $opis, Collection $datumi, Carbon $od, string $boja): array
    {
        $mjeseci = [];
        for ($i = 0; $i < 12; $i++) {
            $m = $od->copy()->addMonths($i);
            $mjeseci[$m->format('Y-m')] = ['oznaka' => $m->format('m.Y.'), 'broj' => 0];
        }
        // samo prozor od 12 mjeseci: zapis s budućim datumom ne ulazi ni u broj ni u "zadnji"
        $datumi = $datumi->map(fn ($d) => Carbon::parse($d))->filter(fn ($d) => isset($mjeseci[$d->format('Y-m')]));
        foreach ($datumi as $d) {
            $mjeseci[$d->format('Y-m')]['broj']++;
        }
        $vrh = max(1, max(array_column($mjeseci, 'broj')));
        $zadnji = $datumi->max();
        $n = $datumi->count();

        return [
            'ruta' => $ruta, 'naslov' => $naslov, 'opis' => $opis, 'ikona' => $ruta,
            'brojka' => self::mn($n, 'zapis', 'zapisa', 'zapisa'),
            'pod' => $zadnji ? 'zadnji ' . $zadnji->format('d.m.Y.') : 'u zadnjih 12 mjeseci',
            'graf' => 'stupci', 'boja' => $boja, 'vrh' => $vrh, 'stupci' => array_values($mjeseci),
            'prazno' => null,
        ];
    }

    private static function skladiste(User $user): array
    {
        $ima = Skladiste::skladista($user)->isNotEmpty();
        $st = $ima ? Skladiste::stanje($user) : collect();
        $broj = fn (string $s) => $st->where('status', $s)->count();

        return [
            'ruta' => 'skladiste', 'naslov' => 'Skladište', 'opis' => 'Zalihe sredstava i gnojiva, potrošnja ide sama.', 'ikona' => 'skladiste',
            'brojka' => self::mn($st->count(), 'artikl', 'artikla', 'artikala'),
            'pod' => $ima ? self::mn(Skladiste::skladista($user)->count(), 'skladište', 'skladišta', 'skladišta') : '',
            'graf' => 'segmenti',
            'segmenti' => [
                ['naziv' => 'u redu', 'broj' => $broj('ok'), 'boja' => '#16a34a'],
                ['naziv' => 'pri kraju', 'broj' => $broj('malo'), 'boja' => '#f59e0b'],
                ['naziv' => 'u minusu', 'broj' => $broj('minus'), 'boja' => '#dc2626'],
            ],
            'prazno' => ! $ima ? 'Dodajte prvo skladište →' : ($st->isEmpty() ? 'Upišite prvu nabavu →' : null),
        ];
    }

    private static function mehanizacija(User $user): array
    {
        $strojeva = Stroj::where('user_id', $user->id)->count();
        $rokovi = Mehanizacija::aktivniRokovi($user);
        $broj = fn (string $s) => $rokovi->filter(fn ($r) => $r->status === $s)->count();

        return [
            'ruta' => 'mehanizacija', 'naslov' => 'Mehanizacija', 'opis' => 'Registracije, osiguranja i servisi s podsjetnikom.', 'ikona' => 'mehanizacija',
            'brojka' => self::mn($strojeva, 'stroj', 'stroja', 'strojeva'),
            'pod' => self::mn($rokovi->count(), 'rok', 'roka', 'rokova'),
            'graf' => 'segmenti',
            'segmenti' => [
                ['naziv' => 'vrijedi', 'broj' => $broj('ok'), 'boja' => '#16a34a'],
                ['naziv' => 'uskoro', 'broj' => $broj('uskoro'), 'boja' => '#f59e0b'],
                ['naziv' => 'isteklo', 'broj' => $broj('istekao'), 'boja' => '#dc2626'],
            ],
            'prazno' => $strojeva === 0 ? 'Dodajte prvi stroj →' : ($rokovi->isEmpty() ? 'Upišite prvi rok →' : null),
        ];
    }

    private static function troskovi(User $user, Carbon $od, Carbon $do): array
    {
        $t = Troskovi::izracun($user, $od, $do);
        $szb = (float) $t['redovi']->sum('szb');
        $gnojivo = (float) $t['redovi']->sum('gnojivo');
        $meh = (float) Troskovi::mehanizacija($user, $od, $do)['ukupno'];
        $ukupno = $szb + $gnojivo + $meh;

        return [
            'ruta' => 'troskovi', 'naslov' => 'Troškovi', 'opis' => 'Trošak po parceli i kulturi, dobit po hektaru.', 'ikona' => 'troskovi',
            'brojka' => number_format($ukupno, 0, ',', '.') . ' €',
            'pod' => 'u zadnjih 12 mjeseci',
            'graf' => 'segmenti', 'novac' => true,
            'segmenti' => [
                ['naziv' => 'zaštita', 'broj' => $szb, 'boja' => self::PRSKANJE],
                ['naziv' => 'gnojidba', 'broj' => $gnojivo, 'boja' => self::GNOJIDBA],
                ['naziv' => 'mehanizacija', 'broj' => $meh, 'boja' => '#6b7280'],
            ],
            'prazno' => $ukupno <= 0 ? 'Upišite cijene uz nabavu →' : null,
        ];
    }
}
