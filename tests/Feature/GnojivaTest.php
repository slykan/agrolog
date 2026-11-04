<?php

namespace Tests\Feature;

use App\Livewire\GnojidbaTable;
use App\Models\Gnojidba;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\User;
use App\Support\Gnojiva;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GnojivaTest extends TestCase
{
    use RefreshDatabase;

    public function test_prepoznavanje_gnojiva(): void
    {
        $this->assertSame(27.0, (float) Gnojiva::prepoznaj('KAN 27%N')['n']);
        $this->assertSame(46.0, (float) Gnojiva::prepoznaj('urea')['n']);
        $g = Gnojiva::prepoznaj('NPK 7-20-30');
        $this->assertEquals([7, 20, 30], [$g['n'], $g['p'], $g['k']]);
        $this->assertEquals(15, Gnojiva::prepoznaj('15:15:15 + S')['k']);
        $this->assertTrue(Gnojiva::prepoznaj('goveđa gnojovka')['organsko']);
        $this->assertNull(Gnojiva::prepoznaj('Nešto nepoznato'));
        $this->assertSame(81.0, Gnojiva::kgHraniva(300, 27));
        $this->assertSame('7-20-30', Gnojiva::postotak('7') . '-' . Gnojiva::postotak('20') . '-' . Gnojiva::postotak('30.00'));   // SQLite vraća "20"
    }

    private function kultura(User $u, float $ha = 2): Kultura
    {
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1515096', 'povrsina_ha' => 10]);

        return Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Kukuruz', 'posadjena_povrsina_ha' => $ha, 'godina' => (int) date('Y')]);
    }

    public function test_unos_gnojidbe_racuna_dusik(): void
    {
        $u = User::factory()->create();
        $k = $this->kultura($u);

        Livewire::actingAs($u)->test(GnojidbaTable::class)
            ->call('addRow')
            ->call('selectKultura', $k->id)
            ->set('form.tip_gnojiva', 'KAN 27% N')
            ->assertSet('form.n_posto', 27)
            ->set('form.kolicina_kg_ha', 300)
            ->assertSee('81,0 kg N')
            ->call('saveNew')
            ->assertHasNoErrors();

        $g = Gnojidba::first();
        $this->assertEquals(27, $g->n_posto);
        $this->assertEquals(81.0, $g->kg_n_ha);
        $this->assertFalse($g->organsko);

        // vlastito gnojivo s ručnim sastavom
        Livewire::actingAs($u)->test(GnojidbaTable::class)
            ->call('addRow')
            ->call('selectKultura', $k->id)
            ->set('form.tip_gnojiva', 'Moje tekuće gnojivo')
            ->set('form.n_posto', 10)
            ->set('form.kolicina_kg_ha', 50)
            ->call('saveNew')
            ->assertHasNoErrors();
        $this->assertEquals(5.0, Gnojidba::latest('id')->first()->kg_n_ha);
    }

    public function test_upozorenje_za_170_kg_n_iz_stajskog_gnoja(): void
    {
        $u = User::factory()->create();
        $k = $this->kultura($u);
        // 40 t/ha goveđe gnojovke × 0,4 % N = 160 kg N/ha, pa još 10 t × 0,4 % = 40 → 200 kg N/ha
        foreach ([40000, 10000] as $kg) {
            Gnojidba::create(Gnojidba::sHranivima(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => date('Y-m-d'), 'tip_gnojiva' => 'Goveđa gnojovka', 'kolicina_kg_ha' => $kg]));
        }

        $bilanca = Livewire::actingAs($u)->test(GnojidbaTable::class)->instance()->bilancaDusika;
        $this->assertEquals(200, (float) $bilanca['redovi'][0]->n_stajski);
        $this->assertEquals(200.0, $bilanca['prosjek']);   // jedina kultura, 2 ha

        Livewire::actingAs($u)->test(GnojidbaTable::class)
            ->assertSee('Stajski gnoj, prosjek gospodarstva: 200,0 / 170 kg N/ha')
            ->assertSee('text-red-700', false);
    }
}
