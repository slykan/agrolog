<?php

namespace App\Support;

use App\Models\PlaniraniRad;
use App\Models\Preporuka;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Push iz kalendara (samo korisnici s uključenim obavijestima):
 * - ujutro: što je danas planirano, uz upozorenje ako je planirano prskanje a dan nije povoljan;
 * - navečer: ako prskanje čeka (plan ili preporuka u iduća 3 dana ili zakašnjelo), je li sutra povoljno.
 */
class KalendarObavijesti
{
    public static function jutro(): int
    {
        $danas = now()->toDateString();
        $poslano = 0;
        self::korisnici(PlaniraniRad::otvoreni()->where('datum', $danas)->distinct()->pluck('user_id'), function (User $u) use ($danas, &$poslano) {
            $planovi = PlaniraniRad::with('kultura')->where('user_id', $u->id)->otvoreni()->where('datum', $danas)->orderBy('id')->get();
            if ($planovi->isEmpty()) {
                return;
            }
            $tekst = $planovi->take(4)->map(fn ($p) => $p->naslov() . ($p->kultura ? ' · ' . $p->kultura->naziv : ''))->implode(', ')
                . ($planovi->count() > 4 ? ' i još ' . ($planovi->count() - 4) : '');
            if ($planovi->contains('vrsta', 'prskanje') && ($dan = Prognoza::za($u)[$danas] ?? null) && ! $dan['prskanje']) {
                $tekst .= '. Danas nije povoljno za prskanje: ' . $dan['razlog'] . '.';
            }
            $naslov = 'Danas planirano' . ($planovi->count() > 1 ? ' (' . $planovi->count() . ')' : '');
            $poslano += WebPush::korisniku($u, $naslov, $tekst, route('kalendar', ['mjesec' => now()->format('Y-m')])) > 0 ? 1 : 0;
        });

        return $poslano;
    }

    public static function vecer(): int
    {
        $sutra = now()->addDay();
        $do = now()->addDays(3)->toDateString();
        $planovi = PlaniraniRad::otvoreni()->where('vrsta', 'prskanje')->where('datum', '<=', $do)->distinct()->pluck('user_id');
        $preporuke = Preporuka::whereIn('status', Preporuka::OTVORENO)->where('vrsta', 'prskanje')->whereNotNull('rok')->where('rok', '<=', $do)->distinct()->pluck('vlasnik_id');
        $poslano = 0;

        self::korisnici($planovi->merge($preporuke)->unique(), function (User $u) use ($sutra, $do, &$poslano) {
            $dan = Prognoza::za($u)[$sutra->toDateString()] ?? null;
            if (! $dan) {
                return;
            }
            $ceka = PlaniraniRad::with('kultura')->where('user_id', $u->id)->otvoreni()->where('vrsta', 'prskanje')->where('datum', '<=', $do)->get()
                ->map(fn ($p) => ['datum' => $p->datum, 'tekst' => ($p->opis ?: 'prskanje') . ($p->kultura ? ' · ' . $p->kultura->naziv : '')])
                ->merge(Preporuka::where('vlasnik_id', $u->id)->whereIn('status', Preporuka::OTVORENO)->where('vrsta', 'prskanje')->whereNotNull('rok')->where('rok', '<=', $do)->get()
                    ->map(fn ($p) => ['datum' => $p->rok, 'tekst' => ($p->sredstvo ?: 'prskanje') . ($p->kultura_naziv ? ' · ' . $p->kultura_naziv : '')]));
            if ($ceka->isEmpty()) {
                return;
            }
            $sutraPlanirano = $ceka->contains(fn ($c) => $c['datum']->isSameDay($sutra));
            $popis = $ceka->take(3)->pluck('tekst')->implode(', ');
            if ($dan['prskanje']) {
                $naslov = 'Sutra je povoljno za prskanje';
                $tekst = $sutra->format('d.m.Y.') . ': ' . $dan['ikona'] . ' ' . $dan['min'] . '–' . $dan['max'] . ' °C, vjetar ' . $dan['vjetar'] . ' km/h. Čeka: ' . $popis;
            } elseif ($sutraPlanirano) {
                $naslov = 'Sutra nije povoljno za prskanje';
                $tekst = $sutra->format('d.m.Y.') . ': ' . $dan['razlog'] . '. Planirano: ' . $popis . '. Pogledajte kalendar za drugi dan.';
            } else {
                return;
            }
            $poslano += WebPush::korisniku($u, $naslov, $tekst, route('kalendar', ['mjesec' => $sutra->format('Y-m')])) > 0 ? 1 : 0;
        });

        return $poslano;
    }

    private static function korisnici($ids, callable $fn): void
    {
        User::whereIn('id', $ids->filter()->values())->where('push_obavijesti', true)
            ->where(fn ($q) => $q->whereHas('pushPretplate')->orWhereHas('mobilePushDevices'))
            ->chunkById(100, fn ($korisnici) => $korisnici->each($fn));
    }
}
