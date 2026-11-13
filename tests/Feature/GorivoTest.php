<?php

namespace Tests\Feature;

use App\Livewire\GorivoUnos;
use App\Livewire\PrskanjeTable;
use App\Models\Gnojidba;
use App\Models\GorivoDan;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\Prskanje;
use App\Models\Rad;
use App\Models\User;
use App\Support\Gorivo;
use App\Support\Troskovi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class GorivoTest extends TestCase
{
    use RefreshDatabase;

    private function kultura(User $u, string $naziv = 'Kukuruz', float $ha = 2): Kultura
    {
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => (string) random_int(100000, 999999), 'povrsina_ha' => 10]);

        return Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => $naziv, 'posadjena_povrsina_ha' => $ha, 'godina' => date('Y')]);
    }

    private function prskanje(User $u, Kultura $k, string $datum, string $sredstvo = 'Sredstvo', ?float $ha = null): Prskanje
    {
        return Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum_tretiranja' => $datum, 'tretirana_povrsina_ha' => $ha ?? $k->posadjena_povrsina_ha,
            'trgovacki_naziv_sredstva' => $sredstvo, 'kolicina_sredstva_l_ha' => 1, 'vrsta_uporabe' => 'otvoreno', 'sustav_proizvodnje' => 'neekoloska']);
    }

    private function godina(): array
    {
        return [Carbon::create((int) date('Y'), 1, 1), Carbon::create((int) date('Y'), 12, 31)];
    }

    public function test_gorivo_dana_dijeli_se_po_hektarima_i_tank_mix_je_jedan_posao(): void
    {
        $u = User::factory()->create();
        $a = $this->kultura($u, 'Pšenica', 6);
        $b = $this->kultura($u, 'Kukuruz', 2);
        $d = date('Y-m-d');
        $this->prskanje($u, $a, $d, 'Fungicid');
        $this->prskanje($u, $a, $d, 'Insekticid');     // isto prskanje, dva sredstva: jedan prolaz
        $this->prskanje($u, $b, $d);
        GorivoDan::create(['user_id' => $u->id, 'datum' => $d, 'litara' => 100, 'cijena_l' => 1.5, 'iznos' => 150]);

        $this->assertCount(2, Gorivo::posloviDana($u->id, $d));
        $t = Troskovi::izracun($u, ...$this->godina());
        $redovi = $t['redovi']->keyBy('kultura_id');
        $this->assertEquals(112.5, $redovi[$a->id]['gorivo']);      // 6 / 8 ha
        $this->assertEquals(37.5, $redovi[$b->id]['gorivo']);
        $this->assertEquals(150, $t['gorivo_ukupno']);

        // kasnije upisana gnojidba istog dana sama ulazi u raspodjelu
        Gnojidba::create(['user_id' => $u->id, 'kultura_id' => $b->id, 'datum' => $d, 'tip_gnojiva' => 'KAN', 'kolicina_kg_ha' => 200]);
        $redovi = Troskovi::izracun($u, ...$this->godina())['redovi']->keyBy('kultura_id');
        $this->assertEquals(90, $redovi[$a->id]['gorivo']);         // 6 / 10 ha
        $this->assertEquals(60, $redovi[$b->id]['gorivo']);         // prskanje 2 ha + gnojidba 2 ha
    }

    public function test_izuzeti_posao_i_rad_s_vlastitim_gorivom_ne_dobivaju_dio(): void
    {
        $u = User::factory()->create();
        $a = $this->kultura($u, 'Pšenica', 4);
        $b = $this->kultura($u, 'Kukuruz', 4);
        $d = date('Y-m-d');
        $this->prskanje($u, $a, $d);
        $this->prskanje($u, $b, $d);
        Rad::create(['user_id' => $u->id, 'kultura_id' => $b->id, 'datum' => $d, 'vrsta' => 'Oranje', 'izvedba' => 'vlastito', 'gorivo_l' => 40, 'cijena_goriva' => 1.5, 'trosak' => 60]);
        GorivoDan::create(['user_id' => $u->id, 'datum' => $d, 'iznos' => 80, 'izuzeto' => ['prskanje:' . $b->id]]);

        $this->assertCount(2, Gorivo::posloviDana($u->id, $d));     // rad s upisanim gorivom nije na popisu
        $redovi = Troskovi::izracun($u, ...$this->godina())['redovi']->keyBy('kultura_id');
        $this->assertEquals(80, $redovi[$a->id]['gorivo']);
        $this->assertEquals(0, $redovi[$b->id]['gorivo']);
    }

    public function test_dan_bez_poslova_ide_u_nevezano(): void
    {
        $u = User::factory()->create();
        GorivoDan::create(['user_id' => $u->id, 'datum' => date('Y-m-d'), 'iznos' => 50]);

        $t = Troskovi::izracun($u, ...$this->godina());
        $this->assertEquals(50, $t['gorivo_nevezano']);
        $this->assertTrue($t['redovi']->isEmpty());
    }

    public function test_popup_nakon_prvog_prskanja_kasnije_i_ne_pratim(): void
    {
        $u = User::factory()->create();
        $k = $this->kultura($u);
        $this->actingAs($u);
        $d = date('Y-m-d');

        // spremanje prskanja javlja gorivu
        Livewire::test(PrskanjeTable::class)->call('addRow')->set('form.kultura_id', $k->id)
            ->set('form.kolicina_sredstva_l_ha', 1)->call('saveNew')->assertHasNoErrors()
            ->assertDispatched('posao-spremljen', datum: $d);

        $c = Livewire::test(GorivoUnos::class)->dispatch('posao-spremljen', datum: $d)->assertSet('otvoren', true)
            ->assertSee('Gorivo za ' . date('d.m.Y.'));
        $c->call('kasnije')->assertSet('otvoren', false);
        $this->assertSame($d, Carbon::parse($u->fresh()->gorivo_kasnije)->toDateString());

        // isti dan: bez popupa, traka podsjeća
        Livewire::test(GorivoUnos::class)->dispatch('posao-spremljen', datum: $d)->assertSet('otvoren', false)
            ->assertSee('još nije uneseno');

        // unos s trake: litre × cijena, traka nestaje
        Livewire::test(GorivoUnos::class)->call('otvori', $d)->set('litara', 95)->set('cijena', 1.42)->call('spremi')->assertHasNoErrors()
            ->assertSet('otvoren', false);
        $this->assertEquals(134.9, (float) GorivoDan::first()->iznos);
        $this->assertNull($u->fresh()->gorivo_kasnije);

        // drugi posao istog dana: samo poruka, bez popupa
        Livewire::test(GorivoUnos::class)->dispatch('posao-spremljen', datum: $d)->assertSet('otvoren', false)->assertSee('Uključeno u gorivo');

        // ispravak 95 → 105 L preko istog dana: isti zapis
        Livewire::test(GorivoUnos::class)->call('otvori', $d)->assertSet('litara', 95.0)->set('litara', 105)->call('spremi');
        $this->assertSame(1, GorivoDan::count());
        $this->assertEquals(149.1, (float) GorivoDan::first()->iznos);

        // ne pratim: više nema popupa
        Livewire::test(GorivoUnos::class)->call('otvori', Carbon::yesterday()->toDateString())->call('nePratim');
        $this->assertFalse((bool) $u->fresh()->prati_gorivo);
        Livewire::test(GorivoUnos::class)->dispatch('posao-spremljen', datum: Carbon::yesterday()->toDateString())->assertSet('otvoren', false);

        // ponovno uključivanje u Profilu
        $this->patch(route('profile.gorivo'), ['prati' => 1])->assertRedirect();
        $this->assertTrue((bool) $u->fresh()->prati_gorivo);
    }

    public function test_samo_iznos_bez_litara_i_prazan_unos_odbijen(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u);

        Livewire::test(GorivoUnos::class)->call('otvori')->set('cijena', '')->call('spremi')->assertHasErrors('litara');
        Livewire::test(GorivoUnos::class)->call('otvori')->set('cijena', '')->set('iznos', 70)->call('spremi')->assertHasNoErrors();
        $this->assertEquals(70, (float) GorivoDan::first()->iznos);
    }

    public function test_tudje_gorivo_se_ne_vidi(): void
    {
        $u = User::factory()->create();
        $drugi = User::factory()->create();
        GorivoDan::create(['user_id' => $drugi->id, 'datum' => date('Y-m-d'), 'litara' => 50, 'iznos' => 75]);
        $this->actingAs($u);

        Livewire::test(GorivoUnos::class)->call('otvori', date('Y-m-d'))->assertSet('danId', null)->assertSet('litara', '');
        $this->assertEquals(0, Troskovi::izracun($u, ...$this->godina())['gorivo_ukupno']);
    }
}
