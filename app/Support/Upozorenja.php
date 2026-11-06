<?php

namespace App\Support;

use App\Models\Kultura;
use App\Models\Prskanje;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Obavijesti uz zvono u izborniku: rokovi mehanizacije, zapisi za dopuniti (evidencija 2027.) i skladište pri kraju.
 * Svaka stavka: naslov, kratki tekst, poveznica "Više" i razina (crveno = hitno, žuto = upozorenje).
 */
class Upozorenja
{
    private static array $cache = [];

    /** @return Collection<int, array{kljuc: string, naslov: string, tekst: string, link: string, razina: string}> */
    public static function za(User $user): Collection
    {
        // jednom po zahtjevu: isti objekt korisnika (zvono i stranica) dijeli izračun
        return self::$cache[spl_object_id($user) . ":" . $user->id] ??= self::izracunaj($user);
    }

    private static function izracunaj(User $user): Collection
    {
        $stavke = collect();

        // mehanizacija: istekli rokovi i oni unutar razdoblja podsjetnika
        foreach (Mehanizacija::aktivniRokovi($user)->filter(fn ($r) => $r->status !== 'ok') as $r) {
            $stavke->push([
                'kljuc' => 'rok-' . $r->id . '-' . $r->status,
                'naslov' => $r->stroj->naziv . ' · ' . $r->opis,
                'tekst' => $r->dana < 0 ? 'Isteklo ' . $r->datum_isteka->format('d.m.Y.')
                    : ($r->dana === 0 ? 'Ističe danas' : 'Ističe za ' . $r->dana . ' d. (' . $r->datum_isteka->format('d.m.Y.') . ')'),
                'link' => route('mehanizacija'),
                'razina' => $r->status === 'istekao' ? 'crveno' : 'zuto',
            ]);
        }

        // karenca: berba/žetva ne prije (iz registracije u FIS-u, samo spremljeni detalji)
        foreach (ProvjeraSzb::aktivneKarence($user) as $kar) {
            $stavke->push([
                'kljuc' => 'karenca-' . $kar['kultura']->id . '-' . $kar['do']->format('Ymd'),
                'naslov' => 'Karenca: ' . $kar['kultura']->naziv . ' (' . $kar['kultura']->parcela?->arkod_broj . ')',
                'tekst' => 'Berba/žetva ne prije ' . $kar['do']->format('d.m.Y.') . ' · ' . $kar['sredstvo'],
                'link' => route('prskanje'),
                'razina' => 'info',
            ]);
        }

        // evidencija 2027.: prskanja ove godine bez reg. broja ili fenofaze, aktivne kulture bez EPPO koda
        $bezPodataka = Prskanje::where('user_id', $user->id)->whereYear('datum_tretiranja', now()->year)
            ->where(fn ($q) => $q->whereNull('registracijski_broj')->orWhere('registracijski_broj', '')->orWhereNull('bbch')->orWhere('bbch', ''))
            ->count();
        if ($bezPodataka) {
            $stavke->push([
                'kljuc' => 'prskanja-' . $bezPodataka,
                'naslov' => 'Za dopuniti (evidencija 2027.)',
                'tekst' => $bezPodataka . ' ' . ($bezPodataka === 1 ? 'prskanje' : 'prskanja') . ' bez reg. broja ili fenofaze',
                'link' => route('prskanje'),
                'razina' => 'zuto',
            ]);
        }
        $bezEppo = Kultura::where('user_id', $user->id)->whereNull('zavrsena_at')->where(fn ($q) => $q->whereNull('eppo_kod')->orWhere('eppo_kod', ''))->count();
        if ($bezEppo) {
            $stavke->push([
                'kljuc' => 'eppo-' . $bezEppo,
                'naslov' => 'Kulture bez EPPO koda',
                'tekst' => $bezEppo . ' ' . ($bezEppo % 10 >= 2 && $bezEppo % 10 <= 4 && ($bezEppo % 100 < 12 || $bezEppo % 100 > 14) ? 'kulture' : 'kultura') . ' bez koda za evidenciju 2027.',
                'link' => route('parcele'),
                'razina' => 'zuto',
            ]);
        }

        // skladište: u minusu ili pri kraju (do 3 pojedinačno, inače zbirno)
        if ($user->skladista()->exists()) {
            $zaliha = Skladiste::stanje($user)->whereIn('status', ['minus', 'malo'])->sortBy(fn ($a) => $a['status'] === 'minus' ? 0 : 1)->values();
            $vise = $zaliha->pluck('skladiste_id')->unique()->count() > 1;
            foreach ($zaliha->take(3) as $a) {
                $stavke->push([
                    'kljuc' => 'zaliha-' . md5($a['kljuc']) . '-' . $a['status'],
                    'naslov' => $a['naziv'] . ($vise ? ' · ' . $a['skladiste'] : ''),
                    'tekst' => ($a['status'] === 'minus' ? 'U minusu: ' : 'Pri kraju: ') . Skladiste::broj($a['stanje'], 3) . ' ' . $a['jedinica'],
                    'link' => route('skladiste'),
                    'razina' => $a['status'] === 'minus' ? 'crveno' : 'zuto',
                ]);
            }
            if ($zaliha->count() > 3) {
                $stavke->push([
                    'kljuc' => 'zaliha-jos-' . ($zaliha->count() - 3),
                    'naslov' => 'Skladište',
                    'tekst' => 'Još ' . ($zaliha->count() - 3) . ' pri kraju ili u minusu',
                    'link' => route('skladiste'),
                    'razina' => 'zuto',
                ]);
            }
        }

        // preporuke: samo vlasniku računa, ne savjetniku koji radi u tuđem gospodarstvu
        if (! request()->attributes->has('pristup')) {
            $nove = \App\Models\Preporuka::where('vlasnik_id', $user->id)->where('status', 'poslano')->latest('id')->get();
            foreach ($nove->take(3) as $p) {
                $stavke->push([
                    'kljuc' => 'preporuka-' . $p->id,
                    'naslov' => 'Preporuka: ' . $p->savjetnik_ime,
                    'tekst' => $p->sazetak() ?: \Illuminate\Support\Str::limit((string) $p->napomena, 80),
                    'link' => route('preporuke.index'),
                    'razina' => 'zuto',
                ]);
            }
            if ($nove->count() > 3) {
                $stavke->push(['kljuc' => 'preporuka-jos-' . ($nove->count() - 3), 'naslov' => 'Preporuke', 'tekst' => 'Još ' . ($nove->count() - 3) . ' čeka odgovor', 'link' => route('preporuke.index'), 'razina' => 'zuto']);
            }
            $odgovori = \App\Models\Preporuka::where('savjetnik_id', $user->id)->whereNotNull('odgovoreno_at')->whereNull('savjetnik_vidio_at')->count();
            if ($odgovori) {
                $stavke->push(['kljuc' => 'preporuka-odgovori-' . $odgovori, 'naslov' => 'Odgovori na preporuke', 'tekst' => 'Novih odgovora klijenata: ' . $odgovori, 'link' => route('klijenti.preporuke'), 'razina' => 'zuto']);
            }
        }

        return $stavke->sortBy(fn ($s) => ['crveno' => 0, 'zuto' => 1, 'info' => 2][$s['razina']] ?? 3)->values();
    }
}
