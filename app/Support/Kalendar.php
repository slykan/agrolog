<?php

namespace App\Support;

use App\Models\Berba;
use App\Models\Gnojidba;
use App\Models\Parcela;
use App\Models\PlaniraniRad;
use App\Models\Preporuka;
use App\Models\Prskanje;
use App\Models\Rad;
use App\Models\StrojRok;
use App\Models\StrojServis;
use App\Models\SzbSredstvo;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Sve što se dogodilo i što slijedi, po danima: zapisi iz evidencije, karence, rokovi mehanizacije,
 * otvorene preporuke savjetnika i planirani radovi. Ništa se ne sprema; kalendar samo čita postojeće podatke.
 */
class Kalendar
{
    /** Skupine za filter: naziv i boja (iste boje kao grafovi i troškovi gdje postoje). */
    public const GRUPE = [
        'prskanje' => ['Prskanje', '#2a78d6'],
        'gnojidba' => ['Gnojidba', '#008300'],
        'berba' => ['Berba i žetva', '#b45309'],
        'rad' => ['Ostali radovi', '#a24ea8'],
        'karenca' => ['Karence', '#d97706'],
        'mehanizacija' => ['Mehanizacija', '#64748b'],
        'preporuka' => ['Preporuke', '#0d9488'],
        'plan' => ['Planirano', '#334155'],
    ];

    /**
     * Događaji u razdoblju, poredani po datumu. Svaki: datum (Y-m-d), grupa, boja, naslov, detalj, parcela, kultura_id, parcela_id,
     * link, stanje (null | 'kasni' | 'plan'), plan_id.
     */
    public static function dogadaji(User $user, Carbon $od, Carbon $do, ?int $parcelaId = null, ?array $grupe = null): Collection
    {
        $uid = $user->id;
        $od = $od->copy()->startOfDay();
        $do = $do->copy()->endOfDay();
        $grupe ??= array_keys(self::GRUPE);
        $ima = fn (string $g) => in_array($g, $grupe, true);
        $danas = now()->startOfDay();
        $savjetnik = (bool) request()->attributes->get('savjetnik');

        $naParceli = fn ($q) => $parcelaId ? $q->whereHas('kultura', fn ($k) => $k->where('parcela_id', $parcelaId)) : $q;
        $sa = ['kultura:id,parcela_id,naziv', 'kultura.parcela:id,arkod_broj,rucno_ucrtana'];
        $d = collect();

        $osnova = function ($zapis, string $grupa, string $naslov, ?string $detalj, ?string $link, $datum) {
            $k = $zapis->kultura ?? null;

            return [
                'datum' => Carbon::parse($datum)->format('Y-m-d'), 'grupa' => $grupa, 'boja' => self::GRUPE[$grupa][1],
                'naslov' => $naslov, 'detalj' => $detalj,
                'kultura' => $k?->naziv, 'parcela' => $k?->parcela ? Parcela::oznaka($k->parcela->arkod_broj, $k->parcela->rucno_ucrtana) : null,
                'kultura_id' => $k?->id, 'parcela_id' => $k?->parcela_id, 'link' => $link, 'stanje' => null, 'plan_id' => null,
            ];
        };

        if ($ima('prskanje')) {
            foreach ($naParceli(Prskanje::with($sa)->where('user_id', $uid)->whereBetween('datum_tretiranja', [$od->toDateString(), $do->toDateString()]))->get() as $p) {
                $doza = $p->kolicina_sredstva_l_ha !== null ? Skladiste::broj($p->kolicina_sredstva_l_ha) . ' l/ha' : null;
                $d->push($osnova($p, 'prskanje', 'Prskanje' . ($p->trgovacki_naziv_sredstva ? ': ' . $p->trgovacki_naziv_sredstva : ''),
                    collect([$doza, $p->tretirana_povrsina_ha ? Skladiste::broj($p->tretirana_povrsina_ha) . ' ha' : null])->filter()->implode(' · '),
                    route('prskanje'), $p->datum_tretiranja));
            }
        }

        if ($ima('gnojidba')) {
            foreach ($naParceli(Gnojidba::with($sa)->where('user_id', $uid)->whereBetween('datum', [$od->toDateString(), $do->toDateString()]))->get() as $g) {
                $d->push($osnova($g, 'gnojidba', 'Gnojidba: ' . $g->tip_gnojiva,
                    collect([Skladiste::broj($g->kolicina_kg_ha) . ' kg/ha', $g->kg_n_ha ? Skladiste::broj($g->kg_n_ha) . ' kg N/ha' : null])->filter()->implode(' · '),
                    route('gnojidba'), $g->datum));
            }
        }

        if ($ima('berba')) {
            foreach ($naParceli(Berba::with($sa)->where('user_id', $uid)->whereBetween('datum', [$od->toDateString(), $do->toDateString()]))->get() as $b) {
                $d->push($osnova($b, 'berba', 'Berba / žetva', Skladiste::broj($b->kolicina) . ' ' . $b->jedinica . ($b->kupac ? ' · ' . $b->kupac : ''),
                    route('berba'), $b->datum));
            }
        }

        if ($ima('rad')) {
            foreach ($naParceli(Rad::with($sa)->where('user_id', $uid)->whereBetween('datum', [$od->toDateString(), $do->toDateString()]))->get() as $r) {
                $d->push($osnova($r, 'rad', $r->vrsta, $r->izvedba === 'usluga' ? 'usluga' . ($r->izvodac ? ': ' . $r->izvodac : '') : null,
                    route('radovi'), $r->datum));
            }
        }

        if ($ima('karenca')) {
            foreach (self::karence($user, $od, $do, $parcelaId) as $k) {
                if ($k['do']->between($od, $do)) {
                    $d->push(['datum' => $k['do']->format('Y-m-d'), 'grupa' => 'karenca', 'boja' => self::GRUPE['karenca'][1],
                        'naslov' => 'Kraj karence: smije se brati', 'detalj' => $k['sredstvo'] . ' (' . $k['dana'] . ' dana od ' . $k['od']->format('d.m.Y.') . ')',
                        'kultura' => $k['kultura'], 'parcela' => $k['parcela'], 'kultura_id' => $k['kultura_id'], 'parcela_id' => $k['parcela_id'],
                        'link' => route('berba'), 'stanje' => null, 'plan_id' => null]);
                }
            }
        }

        if ($ima('mehanizacija') && ! $parcelaId) {
            foreach (StrojRok::with('stroj')->where('user_id', $uid)->whereNull('obnovljen_at')->whereBetween('datum_isteka', [$od->toDateString(), $do->toDateString()])->get() as $r) {
                $d->push(['datum' => $r->datum_isteka->format('Y-m-d'), 'grupa' => 'mehanizacija', 'boja' => self::GRUPE['mehanizacija'][1],
                    'naslov' => 'Rok: ' . $r->opis, 'detalj' => $r->stroj?->naziv, 'kultura' => null, 'parcela' => null, 'kultura_id' => null, 'parcela_id' => null,
                    'link' => route('mehanizacija'), 'stanje' => $r->datum_isteka->lt($danas) ? 'kasni' : null, 'plan_id' => null]);
            }
            foreach (StrojServis::with('stroj')->where('user_id', $uid)->whereBetween('datum', [$od->toDateString(), $do->toDateString()])->get() as $s) {
                $d->push(['datum' => $s->datum->format('Y-m-d'), 'grupa' => 'mehanizacija', 'boja' => self::GRUPE['mehanizacija'][1],
                    'naslov' => 'Servis: ' . ($s->stroj?->naziv ?? 'stroj'), 'detalj' => $s->opis, 'kultura' => null, 'parcela' => null, 'kultura_id' => null, 'parcela_id' => null,
                    'link' => route('mehanizacija'), 'stanje' => null, 'plan_id' => null]);
            }
        }

        if ($ima('preporuka')) {
            // otvorena preporuka stoji na roku (bez roka na danu slanja); prošao rok = kasni
            $q = Preporuka::with($sa)->where('vlasnik_id', $uid)->whereIn('status', Preporuka::OTVORENO)
                ->where(fn ($q) => $q->whereBetween('rok', [$od->toDateString(), $do->toDateString()])
                    ->orWhere(fn ($q) => $q->whereNull('rok')->whereBetween('created_at', [$od, $do])));
            foreach ($naParceli($q)->get() as $p) {
                $datum = $p->rok ?? $p->created_at;
                $link = $savjetnik ? null : ($p->imaUnos() ? route($p->vrsta, ['preporuka' => $p->id]) : route('preporuke.index'));
                $e = $osnova($p, 'preporuka', 'Preporuka (' . $p->savjetnik_ime . '): ' . (Preporuka::VRSTE[$p->vrsta] ?? $p->vrsta),
                    collect([trim($p->sredstvo . ' ' . $p->dozaTekst()), $p->napomena])->filter()->implode(' · '), $link, $datum);
                $e['kultura'] ??= $p->kultura_naziv;
                $e['parcela'] ??= $p->parcela_oznaka;
                $e['stanje'] = $p->rok && $p->rok->lt($danas) ? 'kasni' : null;
                $d->push($e);
            }
        }

        if ($ima('plan')) {
            foreach ($naParceli(PlaniraniRad::with($sa)->where('user_id', $uid)->otvoreni()->whereBetween('datum', [$od->toDateString(), $do->toDateString()]))->get() as $p) {
                $e = $osnova($p, 'plan', $p->naslov(), $p->napomena, $p->linkZaUnos(), $p->datum);
                $e['boja'] = self::GRUPE[$p->vrsta === 'rad' ? 'rad' : ($p->vrsta === 'ostalo' ? 'plan' : $p->vrsta)][1];
                $e['stanje'] = $p->datum->lt($danas) ? 'kasni' : 'plan';
                $e['plan_id'] = $p->id;
                $d->push($e);
            }
        }

        $red = array_flip(array_keys(self::GRUPE));

        return $d->sortBy([['datum', 'asc'], fn ($a, $b) => $red[$a['grupa']] <=> $red[$b['grupa']]])->values();
    }

    /**
     * Karence koje se preklapaju s razdobljem: od dana prskanja do prvog dana kad se smije brati (`do`).
     * Samo za sredstva čiji su detalji registracije već spremljeni (bez poziva FIS-u pri prikazu).
     */
    public static function karence(User $user, Carbon $od, Carbon $do, ?int $parcelaId = null): array
    {
        $q = Prskanje::with(['kultura.parcela:id,arkod_broj,rucno_ucrtana'])->where('user_id', $user->id)
            ->whereBetween('datum_tretiranja', [$od->copy()->subDays(180)->toDateString(), $do->toDateString()])
            ->whereNotNull('trgovacki_naziv_sredstva');
        if ($parcelaId) {
            $q->whereHas('kultura', fn ($k) => $k->where('parcela_id', $parcelaId));
        }

        $sredstva = [];
        $karence = [];
        foreach ($q->get() as $p) {
            if (! $p->kultura) {
                continue;
            }
            $naziv = $p->trgovacki_naziv_sredstva;
            $s = $sredstva[$naziv] ??= (SzbSredstvo::poNazivu($naziv) ?: false);
            $detalji = $s ? ProvjeraSzb::detalji($s, false) : null;
            $reg = $detalji ? ProvjeraSzb::zaKulturu($detalji, $p->kultura) : null;
            if (! $reg || ! $reg['karenca_dana']) {
                continue;
            }
            $kraj = $p->datum_tretiranja->copy()->addDays($reg['karenca_dana']);
            if ($kraj->lt($od->copy()->startOfDay())) {
                continue;
            }
            $k = $p->kultura;
            $karence[] = [
                'od' => $p->datum_tretiranja->copy(), 'do' => $kraj, 'dana' => $reg['karenca_dana'], 'sredstvo' => $s->naziv,
                'kultura' => $k->naziv, 'kultura_id' => $k->id, 'parcela_id' => $k->parcela_id,
                'parcela' => $k->parcela ? Parcela::oznaka($k->parcela->arkod_broj, $k->parcela->rucno_ucrtana) : null,
            ];
        }

        return $karence;
    }
}
