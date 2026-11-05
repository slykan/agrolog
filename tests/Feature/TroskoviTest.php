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
            ->assertSee('1515096')->assertSee('Trošak po parceli i kulturi')
            ->call('postavi', 'prosla')->assertViewHas('nabavaUkupno', 90.0);
        $this->get('/troskovi')->assertOk()->assertSee('Nabava po mjesecima');
    }

    public function test_bez_skladista_uputa(): void
    {
        $this->actingAs(User::factory()->create())->get('/troskovi')->assertOk()->assertSee('Troškovi se računaju iz cijena nabave na skladištu');
    }
}
