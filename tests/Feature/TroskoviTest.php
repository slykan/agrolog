<?php

namespace Tests\Feature;

use App\Livewire\TroskoviPregled;
use App\Models\Gnojidba;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\Prskanje;
use App\Models\Skladiste as SkladisteModel;
use App\Models\SkladisteUlaz;
use App\Models\User;
use App\Support\Troskovi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TroskoviTest extends TestCase
{
    use RefreshDatabase;

    public function test_nabava_i_trosak_po_kulturi(): void
    {
        $this->travelTo('2026-11-05 10:00');
        $u = User::factory()->create();
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1515096', 'povrsina_ha' => 10]);
        $k = Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Pšenica', 'posadjena_povrsina_ha' => 4, 'godina' => 2026]);
        $glavno = SkladisteModel::create(['user_id' => $u->id, 'naziv' => 'Glavno', 'zadano' => true]);
        $eko = SkladisteModel::create(['user_id' => $u->id, 'naziv' => 'Ekonomija', 'zadano' => false]);

        $ulaz = fn ($s, $vrsta, $naziv, $kol, $jed, $cijena, $datum, $x = []) => SkladisteUlaz::create(['user_id' => $u->id, 'skladiste_id' => $s->id, 'vrsta' => $vrsta,
            'naziv' => $naziv, 'datum' => $datum, 'kolicina' => $kol, 'jedinica' => $jed, 'cijena' => $cijena] + $x);
        $ulaz($glavno, 'szb', 'Roundup', 20, 'L', 200, '2026-03-01');          // 10 €/L
        $ulaz($glavno, 'gnojivo', 'KAN 27% N', 1000, 'kg', 500, '2026-03-10'); // 0,5 €/kg
        $ulaz($glavno, 'szb', 'Bez cijene', 5, 'L', null, '2026-03-01');
        $ulaz($eko, 'szb', 'Roundup', 5, 'L', 60, '2026-03-01', ['prijenos_par' => 'x']);   // prijenos: nije nabava
        $ulaz($glavno, 'szb', 'Roundup', -5, 'L', null, '2026-03-01', ['prijenos_par' => 'x', 'korekcija' => true]);
        $ulaz($glavno, 'szb', 'Roundup', 10, 'L', 90, '2025-06-01');           // prošla godina

        Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum_tretiranja' => '2026-04-01', 'trgovacki_naziv_sredstva' => 'Roundup',
            'kolicina_sredstva_l_ha' => 2, 'vrsta_uporabe' => 'otvoreno', 'sustav_proizvodnje' => 'neekoloska']);   // 8 L
        Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum_tretiranja' => '2026-04-02', 'trgovacki_naziv_sredstva' => 'Bez cijene',
            'kolicina_sredstva_l_ha' => 1, 'vrsta_uporabe' => 'otvoreno', 'sustav_proizvodnje' => 'neekoloska']);
        Gnojidba::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => '2026-04-10', 'tip_gnojiva' => 'KAN 27% N', 'kolicina_kg_ha' => 250]);   // 1000 kg

        $t = Troskovi::izracun($u, now()->startOfYear(), now()->endOfYear());
        $this->assertEqualsWithDelta(700, $t['nabava']->sum('cijena'), 0.01);   // 200 + 500, bez prijenosa i prošle godine
        $r = $t['redovi']->first();
        // Roundup u glavnom: (200 + 90) / 30 L = 9,667 €/L × 8 L = 77,33 €; KAN 0,5 × 1000 = 500 €
        $this->assertEqualsWithDelta(77.33, $r['szb'], 0.01);
        $this->assertEqualsWithDelta(500, $r['gnojivo'], 0.01);
        $this->assertEqualsWithDelta(144.33, $r['po_ha'], 0.01);
        $this->assertSame(1, $t['bez_cijene']);

        $this->actingAs($u);
        Livewire::test(TroskoviPregled::class)
            ->assertViewHas('nabavaUkupno', 700.0)
            ->assertSee('1515096')->assertSee('Trošak po parceli')
            ->call('postavi', 'prosla')->assertViewHas('nabavaUkupno', 90.0);
        $this->get('/troskovi')->assertOk()->assertSee('Nabava po mjesecima');
    }

    public function test_dvije_kulture_na_parceli_po_parceli_i_po_kulturi(): void
    {
        $this->travelTo('2026-11-05 10:00');
        $u = User::factory()->create();
        $p1 = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1111111', 'povrsina_ha' => 10]);
        $p2 = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '2222222', 'povrsina_ha' => 5]);
        $psenica1 = Kultura::create(['user_id' => $u->id, 'parcela_id' => $p1->id, 'naziv' => 'Pšenica', 'posadjena_povrsina_ha' => 6, 'godina' => 2026]);
        $kukuruz = Kultura::create(['user_id' => $u->id, 'parcela_id' => $p1->id, 'naziv' => 'Kukuruz', 'posadjena_povrsina_ha' => 4, 'godina' => 2026]);
        $psenica2 = Kultura::create(['user_id' => $u->id, 'parcela_id' => $p2->id, 'naziv' => 'pšenica', 'posadjena_povrsina_ha' => 5, 'godina' => 2026]);
        $s = SkladisteModel::create(['user_id' => $u->id, 'naziv' => 'Glavno', 'zadano' => true]);
        SkladisteUlaz::create(['user_id' => $u->id, 'skladiste_id' => $s->id, 'vrsta' => 'szb', 'naziv' => 'Roundup', 'datum' => '2026-03-01', 'kolicina' => 100, 'jedinica' => 'L', 'cijena' => 1000]);   // 10 €/L
        foreach ([[$psenica1, 1], [$kukuruz, 2], [$psenica2, 1]] as [$k, $doza]) {   // 6 L, 8 L, 5 L
            Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum_tretiranja' => '2026-04-01', 'trgovacki_naziv_sredstva' => 'Roundup',
                'kolicina_sredstva_l_ha' => $doza, 'vrsta_uporabe' => 'otvoreno', 'sustav_proizvodnje' => 'neekoloska']);
        }
        $this->actingAs($u);

        Livewire::test(TroskoviPregled::class)
            ->assertSet('grupa', 'parcela')
            ->assertViewHas('grupe', function ($g) {
                $p1 = $g->firstWhere('naziv', '1111111');   // obje kulture: 60 + 80 € na 10 ha parcele
                return $g->count() === 2 && abs($p1['ukupno'] - 140) < 0.01 && abs($p1['po_ha'] - 14) < 0.01
                    && str_contains($p1['opis'], 'Pšenica') && str_contains($p1['opis'], 'Kukuruz');
            })
            ->call('grupiraj', 'kultura')
            ->assertViewHas('grupe', function ($g) {
                $ps = $g->firstWhere('naziv', 'Pšenica');   // pšenica s obje parcele: 60 + 50 € na 11 ha
                return $g->count() === 2 && abs($ps['ukupno'] - 110) < 0.01 && abs($ps['ha'] - 11) < 0.01 && abs($ps['po_ha'] - 10) < 0.01
                    && str_contains($ps['opis'], '1111111') && str_contains($ps['opis'], '2222222');
            })
            ->assertSee('Trošak po kulturi');
    }

    public function test_troskovi_mehanizacije_i_sveukupno(): void
    {
        $this->travelTo('2026-11-05 10:00');
        $u = User::factory()->create();
        $t = \App\Models\Stroj::create(['user_id' => $u->id, 'vrsta' => 'traktor', 'naziv' => 'John Deere']);
        \App\Models\StrojServis::create(['user_id' => $u->id, 'stroj_id' => $t->id, 'datum' => '2026-05-10', 'opis' => 'ulje', 'trosak' => 240]);
        \App\Models\StrojServis::create(['user_id' => $u->id, 'stroj_id' => $t->id, 'datum' => '2025-05-10', 'opis' => 'staro', 'trosak' => 999]);
        // registracija do 23.11.2027. → plaćena 23.11.2026. (ova godina); osiguranje do 01.03.2026. → plaćeno 2025.
        \App\Models\StrojRok::create(['user_id' => $u->id, 'stroj_id' => $t->id, 'vrsta' => 'registracija', 'datum_isteka' => '2027-11-23', 'iznos' => 210]);
        \App\Models\StrojRok::create(['user_id' => $u->id, 'stroj_id' => $t->id, 'vrsta' => 'osiguranje', 'datum_isteka' => '2026-03-01', 'iznos' => 95]);

        $m = Troskovi::mehanizacija($u, now()->startOfYear(), now()->endOfYear());
        $this->assertEqualsWithDelta(450, $m['ukupno'], 0.01);
        $this->assertEqualsWithDelta(240, $m['po_stroju'][0]['servisi'], 0.01);
        $this->assertEqualsWithDelta(210, $m['po_stroju'][0]['rokovi'], 0.01);

        $this->actingAs($u);
        Livewire::test(TroskoviPregled::class)
            ->assertSee('Trošak mehanizacije')->assertSee('Sveukupni trošak')->assertSee('450 €')
            ->assertSee('John Deere')->assertSee('vrijedi do 23.11.2027.');
    }

    public function test_prinos_i_dobit_po_hektaru(): void
    {
        $this->travelTo('2026-11-05 10:00');
        $u = User::factory()->create();
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '3333333', 'povrsina_ha' => 10]);
        $k = Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Kukuruz', 'posadjena_povrsina_ha' => 10, 'godina' => 2026]);
        $s = SkladisteModel::create(['user_id' => $u->id, 'naziv' => 'Glavno', 'zadano' => true]);
        SkladisteUlaz::create(['user_id' => $u->id, 'skladiste_id' => $s->id, 'vrsta' => 'gnojivo', 'naziv' => 'UREA 46% N', 'datum' => '2026-03-01', 'kolicina' => 3000, 'jedinica' => 'kg', 'cijena' => 1500]);
        Gnojidba::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => '2026-04-01', 'tip_gnojiva' => 'UREA 46% N', 'kolicina_kg_ha' => 200]);   // 2000 kg × 0,5 = 1000 €
        $this->actingAs($u);

        // bez prinosa: dobit nije poznata
        Livewire::test(TroskoviPregled::class)->assertViewHas('dobit', fn ($d) => $d->first()['dobit'] === null)->assertSee('Upišite prinos i cijenu');

        // prinos 9 t/ha × 10 ha × 200 €/t + potpore 100 €/ha = 19.000 €; trošak 1000 + sjeme 2000 + ostalo 500
        Livewire::test(\App\Livewire\ParcelaManager::class)
            ->call('otvoriPrinos', $k->id)
            ->set('prinosForm.prinos_t_ha', '9')->set('prinosForm.cijena_t', '200')->set('prinosForm.potpora_ha', '100')
            ->set('prinosForm.trosak_sjeme', '2000')->set('prinosForm.trosak_ostalo', '500')
            ->call('spremiPrinos')->assertHasNoErrors()
            ->assertSee('9,00 t/ha · 200 €/t');

        $this->assertSame(19000.0, $k->fresh()->prihod());
        Livewire::test(TroskoviPregled::class)->assertViewHas('dobit', function ($d) {
            $r = $d->first();
            return abs($r['prihod'] - 19000) < 0.01 && abs($r['trosak'] - 3500) < 0.01 && abs($r['dobit'] - 15500) < 0.01 && abs($r['dobit_ha'] - 1550) < 0.01;
        })->assertSee('Dobit po hektaru');
    }

    public function test_bez_skladista_uputa(): void
    {
        $this->actingAs(User::factory()->create())->get('/troskovi')->assertOk()->assertSee('Troškovi se računaju iz cijena nabave na skladištu');
    }
}
