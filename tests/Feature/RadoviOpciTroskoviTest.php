<?php

namespace Tests\Feature;

use App\Livewire\OpciTroskovi;
use App\Livewire\ParcelaManager;
use App\Livewire\RadoviTable;
use App\Models\Kultura;
use App\Models\OpciTrosak;
use App\Models\Parcela;
use App\Models\Pristup;
use App\Models\Rad;
use App\Models\Ruta;
use App\Models\User;
use App\Support\Radovi;
use App\Support\Troskovi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class RadoviOpciTroskoviTest extends TestCase
{
    use RefreshDatabase;

    private function kultura(User $u, string $naziv = 'Kukuruz', float $ha = 2, bool $trajni = false): Kultura
    {
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => (string) random_int(100000, 999999), 'povrsina_ha' => 3]);

        return Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => $naziv, 'posadjena_povrsina_ha' => $ha, 'godina' => date('Y'), 'trajni' => $trajni]);
    }

    private function godina(): array
    {
        return [Carbon::create((int) date('Y'), 1, 1), Carbon::create((int) date('Y'), 12, 31)];
    }

    public function test_vrste_rada_ovise_o_kulturi_i_ukljucuju_vlastite(): void
    {
        $u = User::factory()->create();
        $vino = $this->kultura($u, 'Vinova loza', 1, true);
        $jabuka = $this->kultura($u, 'Jabuka', 1, true);
        $kukuruz = $this->kultura($u);

        $this->assertContains('Zelena rezidba', Radovi::vrsteZa($u->id, $vino));
        $this->assertContains('Prorjeđivanje plodova', Radovi::vrsteZa($u->id, $jabuka));
        $this->assertContains('Oranje', Radovi::vrsteZa($u->id, $kukuruz));
        $this->assertNotContains('Oranje', Radovi::vrsteZa($u->id, $vino));

        Rad::create(['user_id' => $u->id, 'kultura_id' => $kukuruz->id, 'datum' => date('Y-m-d'), 'vrsta' => 'Čišćenje kanala', 'izvedba' => 'vlastito']);
        $this->assertContains('Čišćenje kanala', Radovi::vrsteZa($u->id, $vino));
    }

    public function test_usluga_po_ha_i_vlastiti_rad_racunaju_trosak(): void
    {
        $u = User::factory()->create();
        $k = $this->kultura($u, 'Kukuruz', 2);
        $this->actingAs($u);

        Livewire::test(RadoviTable::class)->call('dodaj')
            ->set('form.kultura_id', $k->id)->set('form.vrsta', 'Oranje')->set('form.izvedba', 'usluga')
            ->set('form.usluga_cijena', 60)->set('form.usluga_po', 'ha')->set('form.izvodac', 'Susjed')
            ->call('spremi')->assertHasNoErrors();
        $this->assertEquals(120, (float) Rad::first()->trosak);    // 60 €/ha × 2 ha kulture

        Livewire::test(RadoviTable::class)->call('dodaj')
            ->set('form.kultura_id', $k->id)->set('form.vrsta', 'Tanjuranje')
            ->set('form.gorivo_l', 30)->set('form.cijena_goriva', 1.5)->set('form.radni_sati', 4)->set('form.cijena_sata', 10)
            ->set('form.usluga_cijena', 999)   // zaostalo iz druge izvedbe: ne smije se uračunati
            ->call('spremi')->assertHasNoErrors();
        $r = Rad::where('vrsta', 'Tanjuranje')->first();
        $this->assertEquals(85, (float) $r->trosak);               // 30 × 1,5 + 4 × 10
        $this->assertNull($r->usluga_cijena);

        // novi unos predlaže zadnje cijene goriva i sata
        $c = Livewire::test(RadoviTable::class)->call('dodaj');
        $this->assertEquals(1.5, $c->get('form.cijena_goriva'));
    }

    public function test_tudja_kultura_i_stroj_odbijeni(): void
    {
        $u = User::factory()->create();
        $tudja = $this->kultura(User::factory()->create());
        $this->actingAs($u);

        Livewire::test(RadoviTable::class)->call('dodaj')->set('form.kultura_id', $tudja->id)->set('form.vrsta', 'Oranje')
            ->call('spremi')->assertHasErrors('form.kultura_id');
        Livewire::test(OpciTroskovi::class)->call('dodaj', 'Zakup zemlje')->set('form.iznos', 100)->set('form.kultura_id', $tudja->id)
            ->call('spremi')->assertHasErrors('form.kultura_id');
        $this->assertSame(0, Rad::count() + OpciTrosak::count());
    }

    public function test_voznja_tamo_i_natrag_s_rutom(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u);

        Livewire::test(OpciTroskovi::class)->call('dodaj', 'Vožnja')
            ->set('form.opis', 'Voćnjak Hrastin')->set('form.km', 25)->set('form.povratno', true)->set('form.cijena_km', 0.5)
            ->set('zapamtiRutu', true)->call('spremi')->assertHasNoErrors()->assertDispatched('troskovi-promijenjeni');

        $t = OpciTrosak::first();
        $this->assertEquals(25, (float) $t->iznos);                // 50 km × 0,50
        $this->assertEquals(50, $t->ukupno_km);
        $ruta = Ruta::first();
        $this->assertSame('Voćnjak Hrastin', $ruta->naziv);

        $c = Livewire::test(OpciTroskovi::class)->call('izRute', $ruta->id);
        $this->assertSame('Vožnja', $c->get('form.vrsta'));
        $this->assertEquals(25, $c->get('form.km'));
        $this->assertEquals(0.5, $c->get('form.cijena_km'));   // zadnja upisana
    }

    public function test_troskovi_vezani_uz_kulturu_ulaze_u_ha_a_ostali_samo_u_ukupno(): void
    {
        $u = User::factory()->create();
        $k = $this->kultura($u, 'Jabuka', 2, true);
        Rad::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => date('Y-m-d'), 'vrsta' => 'Rezidba', 'izvedba' => 'vlastito', 'trosak' => 100]);
        OpciTrosak::create(['user_id' => $u->id, 'datum' => date('Y-m-d'), 'vrsta' => 'Vožnja', 'km' => 25, 'povratno' => true, 'cijena_km' => 0.5, 'iznos' => 25, 'kultura_id' => $k->id]);
        OpciTrosak::create(['user_id' => $u->id, 'datum' => date('Y-m-d'), 'vrsta' => 'Članarina', 'iznos' => 40]);
        OpciTrosak::create(['user_id' => $u->id, 'datum' => (date('Y') - 1) . '-06-01', 'vrsta' => 'Članarina', 'iznos' => 999]);

        [$od, $do] = $this->godina();
        $t = Troskovi::izracun($u, $od, $do);
        $red = $t['redovi']->firstWhere('kultura_id', $k->id);
        $this->assertEquals(125, $red['radovi']);
        $this->assertEquals(125, $red['ukupno']);
        $this->assertEquals(62.5, $red['po_ha']);
        $this->assertEquals(40, $t['opci_nevezano']);
        $this->assertEquals(65, $t['opci_ukupno']);

        $this->actingAs($u)->get(route('troskovi'))->assertOk()->assertSee('Opći troškovi')->assertSee('Radovi i ostalo');
        $this->get(route('radovi'))->assertOk()->assertSee('Ostali radovi');
    }

    public function test_savjetnik_vezuje_voznju_na_klijenta_ali_ne_na_tudjeg(): void
    {
        $s = User::factory()->create(['je_savjetnik' => true]);
        $v = User::factory()->create(['name' => 'OPG Horvat']);
        $stranac = User::factory()->create();
        Pristup::create(['vlasnik_id' => $v->id, 'email' => $s->email, 'savjetnik_id' => $s->id, 'prava' => 'pregled', 'token' => Pristup::noviToken(), 'status' => 'prihvaceno', 'prihvaceno_at' => now()]);
        $this->actingAs($s);

        Livewire::test(OpciTroskovi::class)->call('dodaj', 'Vožnja')->set('form.km', 30)->set('form.klijent_id', $stranac->id)
            ->call('spremi')->assertHasErrors('form.klijent_id');
        Livewire::test(OpciTroskovi::class)->call('dodaj', 'Vožnja')->set('form.km', 30)->set('form.klijent_id', $v->id)
            ->call('spremi')->assertHasNoErrors()->assertSee('OPG Horvat')->assertSee('Po klijentu');
        $this->assertSame($v->id, OpciTrosak::first()->klijent_id);
    }

    public function test_kultura_s_radovima_se_ne_brise(): void
    {
        $u = User::factory()->create();
        $k = $this->kultura($u);
        Rad::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => date('Y-m-d'), 'vrsta' => 'Oranje', 'izvedba' => 'vlastito']);
        $this->actingAs($u);

        Livewire::test(ParcelaManager::class)->call('deleteKultura', $k->id);
        $this->assertNotNull($k->fresh());
    }
}
