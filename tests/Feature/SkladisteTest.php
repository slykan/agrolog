<?php

namespace Tests\Feature;

use App\Livewire\SkladisteManager;
use App\Models\Gnojidba;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\Prskanje;
use App\Models\SkladisteUlaz;
use App\Models\User;
use App\Support\Skladiste;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SkladisteTest extends TestCase
{
    use RefreshDatabase;

    private function kultura(User $u, float $ha = 2): Kultura
    {
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1515096', 'povrsina_ha' => 10]);

        return Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Kukuruz', 'posadjena_povrsina_ha' => $ha, 'godina' => (int) date('Y')]);
    }

    private function prskanje(User $u, Kultura $k, string $datum, string $sredstvo, float $doza, ?float $ha = null): Prskanje
    {
        return Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum_tretiranja' => $datum, 'trgovacki_naziv_sredstva' => $sredstvo,
            'kolicina_sredstva_l_ha' => $doza, 'tretirana_povrsina_ha' => $ha, 'vrsta_uporabe' => 'otvoreno', 'sustav_proizvodnje' => 'neekoloska']);
    }

    public function test_iskljuceno_zadano_i_bez_izbornika(): void
    {
        $u = User::factory()->create();
        $this->assertFalse($u->fresh()->koristi_skladiste);

        $this->actingAs($u)->get('/dashboard')->assertOk()->assertDontSee(route('skladiste'), false);
        $this->actingAs($u)->get('/skladiste')->assertOk()->assertSee('Uključi skladište');
    }

    public function test_prekidac_u_profilu(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->patch('/profile/skladiste', ['koristi_skladiste' => '1'])->assertRedirect('/profile');
        $this->assertTrue($u->fresh()->koristi_skladiste);
        $this->actingAs($u->fresh())->get('/dashboard')->assertSee(route('skladiste'), false);

        $this->actingAs($u)->patch('/profile/skladiste', ['koristi_skladiste' => '0']);
        $this->assertFalse($u->fresh()->koristi_skladiste);
    }

    public function test_potrosnja_iz_prskanja_i_gnojidbe(): void
    {
        $u = User::factory()->create(['koristi_skladiste' => true]);
        $k = $this->kultura($u, 2);

        SkladisteUlaz::create(['user_id' => $u->id, 'vrsta' => 'szb', 'naziv' => 'Roundup', 'datum' => '2026-03-01', 'kolicina' => 20, 'jedinica' => 'L', 'cijena' => 100]);
        SkladisteUlaz::create(['user_id' => $u->id, 'vrsta' => 'gnojivo', 'naziv' => 'KAN 27% N', 'datum' => '2026-03-01', 'kolicina' => 1000, 'jedinica' => 'kg']);

        $this->prskanje($u, $k, '2026-02-01', 'Roundup', 3);          // prije prve nabave: ne broji se
        $this->prskanje($u, $k, '2026-04-01', 'roundup ', 3, 1.5);   // 4,5 L (tretirana površina)
        $this->prskanje($u, $k, '2026-05-01', 'ROUNDUP', 2);          // 4 L (površina kulture)
        $this->prskanje($u, $k, '2026-05-01', 'Drugo sredstvo', 9);   // ne prati se
        Gnojidba::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => '2026-04-10', 'tip_gnojiva' => 'KAN 27% N', 'kolicina_kg_ha' => 250]);

        $s = Skladiste::stanje($u);
        $this->assertCount(2, $s);
        $r = $s['szb|ROUNDUP'];
        $this->assertEqualsWithDelta(8.5, $r['potroseno'], 0.001);
        $this->assertEqualsWithDelta(11.5, $r['stanje'], 0.001);
        $this->assertSame(5.0, $r['cijena_po_jedinici']);
        $this->assertEqualsWithDelta(57.5, $r['vrijednost'], 0.001);
        $this->assertSame('ok', $r['status']);
        $this->assertEqualsWithDelta(500, $s['gnojivo|KAN 27% N']['stanje'], 0.001);

        // izmjena zapisa (update bez modela) ispravi stanje
        Prskanje::where('kolicina_sredstva_l_ha', 2)->update(['kolicina_sredstva_l_ha' => 8]);
        $r = Skladiste::stanje($u)['szb|ROUNDUP'];
        $this->assertEqualsWithDelta(-0.5, $r['stanje'], 0.001);
        $this->assertSame('minus', $r['status']);
    }

    public function test_nabava_inventura_i_upozorenje(): void
    {
        $u = User::factory()->create(['koristi_skladiste' => true]);
        $k = $this->kultura($u, 2);
        $this->actingAs($u);

        Livewire::test(SkladisteManager::class)
            ->call('novaNabava', 'gnojivo')
            ->set('form.naziv', 'UREA 46% N')
            ->set('form.kolicina', '2')
            ->set('form.jedinica', 't')
            ->set('form.cijena', '1200')
            ->call('spremi')
            ->assertHasNoErrors();

        $ulaz = SkladisteUlaz::first();
        $this->assertSame('gnojivo', $ulaz->vrsta);
        $this->assertEquals(2000, $ulaz->kolicina);
        $this->assertSame('kg', $ulaz->jedinica);

        Gnojidba::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => date('Y-m-d'), 'tip_gnojiva' => 'UREA 46% N', 'kolicina_kg_ha' => 900]);
        $this->assertSame('malo', Skladiste::stanje($u)['gnojivo|UREA 46% N']['status']);   // 200 od 2000 kg
        $this->get('/dashboard')->assertSee('pri kraju ili u minusu');

        $h = md5('gnojivo|UREA 46% N');
        Livewire::test(SkladisteManager::class)
            ->set("inventura.$h", '150')
            ->call('spremiInventuru', $h)
            ->assertHasNoErrors();
        $this->assertEqualsWithDelta(150, Skladiste::stanje($u)['gnojivo|UREA 46% N']['stanje'], 0.001);
        $this->assertTrue(SkladisteUlaz::where('korekcija', true)->exists());
    }

    public function test_napomena_u_formi_i_tudji_podaci(): void
    {
        $u = User::factory()->create(['koristi_skladiste' => true]);
        $drugi = User::factory()->create(['koristi_skladiste' => true]);
        SkladisteUlaz::create(['user_id' => $u->id, 'vrsta' => 'szb', 'naziv' => 'Roundup', 'datum' => '2026-03-01', 'kolicina' => 5, 'jedinica' => 'L']);

        $n = Skladiste::napomena($u, 'szb', 'roundup', 2.5, 3);
        $this->assertStringContainsString('Na skladištu: 5 L', $n['tekst']);
        $this->assertStringContainsString('ostaje -2,5 L', $n['tekst']);
        $this->assertTrue($n['minus']);

        $this->assertNull(Skladiste::napomena($drugi, 'szb', 'Roundup'));
        $this->assertNull(Skladiste::napomena($u->forceFill(['koristi_skladiste' => false]), 'szb', 'Roundup'));

        // tuđi ulaz se ne može obrisati
        $this->actingAs($drugi);
        Livewire::test(SkladisteManager::class)->call('obrisi', SkladisteUlaz::first()->id);
        $this->assertSame(1, SkladisteUlaz::count());
    }
}
