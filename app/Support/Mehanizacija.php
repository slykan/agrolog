<?php

namespace App\Support;

use App\Mail\PodsjetnikRokovaMail;
use App\Models\StrojRok;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/** Mehanizacija: vrste strojeva, vrste rokova s uobičajenim razdobljem obnove, podsjetnici mailom. */
class Mehanizacija
{
    public const VRSTE = [
        'traktor'    => 'Traktor',
        'kombajn'    => 'Kombajn',
        'prskalica'  => 'Prskalica',
        'rasipac'    => 'Rasipač gnojiva',
        'prikljucak' => 'Priključak (plug, tanjurača, sijačica...)',
        'prikolica'  => 'Prikolica',
        'vozilo'     => 'Vozilo (osobno, teretno)',
        'ostalo'     => 'Ostalo',
    ];

    /** vrsta roka => [naziv, mjeseci do sljedećeg roka] */
    public const ROKOVI = [
        'registracija'      => ['Registracija', 12],
        'tehnicki'          => ['Tehnički pregled', 12],
        'osiguranje'        => ['Osiguranje (AO)', 12],
        'kasko'             => ['Kasko osiguranje', 12],
        'servis'            => ['Sljedeći servis', 12],
        'pregled_prskalice' => ['Pregled prskalice / rasipača', 36],
        'ostalo'            => ['Ostalo', 12],
    ];

    /** Rokovi koji se za vrstu stroja nude prvi. */
    public static function prijedloziRokova(string $vrsta): array
    {
        return match ($vrsta) {
            'traktor', 'vozilo' => ['registracija', 'tehnicki', 'osiguranje', 'servis'],
            'kombajn' => ['registracija', 'osiguranje', 'servis'],
            'prikolica' => ['registracija', 'tehnicki', 'osiguranje'],
            'prskalica', 'rasipac' => ['pregled_prskalice', 'servis'],
            default => ['servis'],
        };
    }

    /** Aktivni (neobnovljeni) rokovi korisnika, od najhitnijeg. */
    public static function aktivniRokovi(User $user)
    {
        return StrojRok::with('stroj')->where('user_id', $user->id)->whereNull('obnovljen_at')->orderBy('datum_isteka')->get();
    }

    /**
     * Podsjetnici mailom: jedan mail po korisniku s rokovima koji ulaze u razdoblje podsjetnika
     * (npr. 30 dana prije) i s rokovima koji su upravo istekli. Svaki se rok javlja najviše dvaput.
     *
     * @return int broj poslanih mailova
     */
    public static function posaljiPodsjetnike(): int
    {
        $danas = now()->startOfDay();
        $rokovi = StrojRok::with(['stroj', 'stroj.user'])
            ->whereNull('obnovljen_at')
            ->where(fn ($q) => $q->whereNull('podsjetnik_poslan_at')->orWhereNull('istek_poslan_at'))
            ->where('datum_isteka', '<=', $danas->copy()->addDays(400))
            ->get()
            ->filter(fn ($r) => $r->podsjetnik_dana > 0 && $r->stroj?->user);   // 0 = bez podsjetnika

        $poslano = 0;
        foreach ($rokovi->groupBy('user_id') as $userRokovi) {
            $uskoro = $userRokovi->filter(fn ($r) => ! $r->podsjetnik_poslan_at && $r->dana >= 0 && $r->dana <= $r->podsjetnik_dana);
            $istekli = $userRokovi->filter(fn ($r) => ! $r->istek_poslan_at && $r->dana < 0 && $r->dana >= -7);
            if ($uskoro->isEmpty() && $istekli->isEmpty()) {
                continue;
            }
            $user = $userRokovi->first()->stroj->user;
            if (! $user->obavijesti_odjava_at) {
                Mail::to($user)->send(new PodsjetnikRokovaMail($user, $uskoro->values(), $istekli->values()));
                $poslano++;
            }
            $svi = $istekli->concat($uskoro);
            $prvi = $svi->first();
            WebPush::korisniku($user,
                $svi->count() === 1 ? $prvi->stroj->naziv . ' · ' . $prvi->opis : 'Mehanizacija: rokova za provjeru: ' . $svi->count(),
                $svi->count() === 1 ? ($prvi->dana < 0 ? 'Isteklo ' : 'Ističe ') . $prvi->datum_isteka->format('d.m.Y.') : $svi->take(3)->map(fn ($r) => $r->stroj->naziv . ' · ' . $r->opis)->implode(', '),
                route('mehanizacija'));
            StrojRok::whereIn('id', $uskoro->pluck('id'))->update(['podsjetnik_poslan_at' => now()]);
            StrojRok::whereIn('id', $istekli->pluck('id'))->update(['istek_poslan_at' => now(), 'podsjetnik_poslan_at' => now()]);
        }

        return $poslano;
    }
}
