<?php

namespace Tests\Feature;

use App\Livewire\PrskanjeTable;
use App\Models\SzbSredstvo;
use App\Models\User;
use App\Support\FisRegistar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/** Registar sredstava iz javne FIS tražilice — fixture su stvarni zapisi iz FIS-a (4 od ~920). */
class FisRegistarTest extends TestCase
{
    use RefreshDatabase;

    private function ucitajRegistar(): void
    {
        Http::fake(['fis.mps.hr/*' => Http::response(file_get_contents(base_path('tests/fixtures/fis-sredstva.json')))]);
        $this->artisan('szb:registar')->assertSuccessful();
    }

    public function test_sinkronizacija_registra(): void
    {
        $this->ucitajRegistar();

        $this->assertSame(4, SzbSredstvo::count());
        $this->assertSame('UP/I-320-20/97-01/22', SzbSredstvo::poNazivu('folicur ew 250')->registracijski_broj);
        $this->assertSame('AMISTAR ERA 240 EC', SzbSredstvo::poNazivu('Amistar Era 250 EC')->naziv);   // stari naziv
        $this->assertFalse(SzbSredstvo::poNazivu('CAPTAN ST-S')->aktivno);

        // ponovna sinkronizacija ne duplira
        $this->assertSame(4, FisRegistar::sinkroniziraj());
        $this->assertSame(4, SzbSredstvo::count());
    }

    public function test_odabir_sredstva_upise_registracijski_broj(): void
    {
        $this->ucitajRegistar();
        $u = User::factory()->create();

        Livewire::actingAs($u)->test(PrskanjeTable::class)
            ->call('addRow')
            ->set('form.trgovacki_naziv_sredstva', 'FOLICUR EW 250')
            ->assertSet('form.registracijski_broj', 'UP/I-320-20/97-01/22')
            ->assertSet('sredstvoNapomena', '')
            // promjena sredstva mijenja i broj
            ->set('form.trgovacki_naziv_sredstva', 'AMISTAR 250 SC')
            ->assertSet('form.registracijski_broj', 'UP/I-320-20/08-01/99')
            ->set('form.trgovacki_naziv_sredstva', 'CAPTAN ST-S')
            ->assertSee('nije aktivna')
            ->set('form.trgovacki_naziv_sredstva', 'Domaći pripravak')
            ->assertSee('nije pronađeno u registru');
    }

    public function test_stranica_nudi_sredstva_iz_registra(): void
    {
        $this->ucitajRegistar();
        $u = User::factory()->create();

        $this->actingAs($u)->get('/prskanje')->assertOk()
            ->assertSee('<datalist id="szb-sredstva">', false)
            ->assertSee('FOLICUR EW 250')
            ->assertSee('UP/I-320-20/94-01/250 (neaktivno)');
    }

    public function test_pad_fis_a_ne_brise_registar(): void
    {
        Http::fake(['fis.mps.hr/*' => Http::sequence()
            ->push(file_get_contents(base_path('tests/fixtures/fis-sredstva.json')))
            ->push('', 503)]);
        $this->artisan('szb:registar')->assertSuccessful();

        $this->artisan('szb:registar')->assertFailed();
        $this->assertSame(4, SzbSredstvo::count());
    }
}
