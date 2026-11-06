<?php

namespace Tests\Feature;

use App\Livewire\BerbaTable;
use App\Livewire\ParcelaManager;
use App\Livewire\TroskoviPregled;
use App\Models\Berba;
use App\Models\Kultura;
use App\Models\NasadGodina;
use App\Models\Parcela;
use App\Models\Prskanje;
use App\Models\SzbSredstvo;
use App\Models\User;
use App\Support\Berbe;
use App\Support\ProvjeraSzb;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class BerbaTest extends TestCase
{
    use RefreshDatabase;

    private function nasad(array $podaci = []): array
    {
        $u = User::factory()->create();
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1515096', 'povrsina_ha' => 10]);
        $k = Kultura::create($podaci + ['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Jabuka', 'posadjena_povrsina_ha' => 4, 'godina' => 2025, 'trajni' => true]);

        return [$u, $p, $k];
    }

    public function test_unos_vise_berbi_u_tonama_i_kg(): void
    {
        [$u, , $k] = $this->nasad();
        $this->actingAs($u);

        Livewire::test(BerbaTable::class)
            ->call('dodaj')
            ->set('form.kultura_id', $k->id)->set('form.datum', '2026-09-10')
            ->set('form.kolicina', 2.5)->set('form.jedinica', 't')->set('form.cijena', 400)->set('form.kupac', 'Zadruga')
            ->call('spremi')->assertHasNoErrors()
            ->call('dodaj')
            ->set('form.kultura_id', $k->id)->set('form.datum', '2026-09-20')->set('form.kolicina', 500)
            ->call('spremi')->assertHasNoErrors()
            ->assertSet('godina', 2026)->assertSee('3,00 t')->assertSee('1.000,00 €')->assertSee('20.09.2026.')->assertSee('1 bez cijene');

        $prva = Berba::orderBy('datum')->first();
        $this->assertEquals(2500, $prva->kolicina_kg);
        $this->assertEquals(0.4, $prva->cijena_kg);
        $this->assertEquals(2.5, $prva->kolicina);
        $this->assertEquals(400, $prva->cijena);
        $this->assertEquals(1000, $prva->iznos);

        // uređivanje vrati vrijednosti u upisanoj jedinici
        Livewire::test(BerbaTable::class)->call('uredi', $prva->id)->assertSet('form.kolicina', 2.5)->assertSet('form.jedinica', 't')->assertSet('form.cijena', 400.0);
    }

    public function test_tudja_kultura_i_tudja_berba_nisu_dostupne(): void
    {
        [$u, , $k] = $this->nasad();
        $b = Berba::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => '2026-09-10', 'kolicina_kg' => 100]);
        $drugi = User::factory()->create();
        $this->actingAs($drugi);

        Livewire::test(BerbaTable::class)->call('dodaj')->set('form.kultura_id', $k->id)->set('form.kolicina', 5)
            ->call('spremi')->assertHasErrors('form.kultura_id');
        $this->assertSame(1, Berba::count());

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Livewire::test(BerbaTable::class)->call('obrisi', $b->id);
    }

    public function test_karenca_pri_berbi(): void
    {
        Http::fake(['fis.mps.hr/*' => Http::response(file_get_contents(base_path('tests/fixtures/fis-detalji-amistar.json')), 200)]);
        SzbSredstvo::create(['fis_id' => 20, 'naziv' => 'AMISTAR 250 SC', 'registracijski_broj' => 'UP/1', 'aktivno' => true]);
        [$u, , $k] = $this->nasad(['naziv' => 'Pšenica', 'trajni' => false]);
        Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum_tretiranja' => '2026-05-01', 'trgovacki_naziv_sredstva' => 'Amistar 250 SC',
            'kolicina_sredstva_l_ha' => 1, 'vrsta_uporabe' => 'otvoreno', 'sustav_proizvodnje' => 'neekoloska']);

        // karenca 35 dana: do 05.06.2026.
        $unutra = ProvjeraSzb::karencaZaBerbu($u, $k->id, '2026-06-01', true);
        $this->assertSame('05.06.2026.', $unutra['krsi']['do']->format('d.m.Y.'));
        $this->assertSame(35, $unutra['krsi']['dana']);
        $this->assertNull(ProvjeraSzb::karencaZaBerbu($u, $k->id, '2026-06-05', true)['krsi']);
        $this->assertSame(1, ProvjeraSzb::karencaZaBerbu($u, $k->id, '2026-06-05', true)['provjereno']);
        // prskanje nakon berbe ne utječe
        $this->assertSame(0, ProvjeraSzb::karencaZaBerbu($u, $k->id, '2026-04-01', true)['provjereno']);

        // nepoznato sredstvo se ne prikazuje kao provjereno
        Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum_tretiranja' => '2026-05-02', 'trgovacki_naziv_sredstva' => 'Nepoznato',
            'kolicina_sredstva_l_ha' => 1, 'vrsta_uporabe' => 'otvoreno', 'sustav_proizvodnje' => 'neekoloska']);
        $this->assertSame(['Nepoznato'], ProvjeraSzb::karencaZaBerbu($u, $k->id, '2026-07-01', true)['neprovjereno']);

        $this->actingAs($u);
        Livewire::test(BerbaTable::class)->call('dodaj')->set('form.kultura_id', $k->id)->set('form.datum', '2026-06-01')
            ->assertSee('Karenca nije prošla')->assertSee('05.06.2026.')->assertSee('Nepoznato')
            ->set('form.kolicina', 100)->call('spremi')->assertHasNoErrors()   // upozorenje ne blokira spremanje
            ->assertSee('u karenci do 05.06.2026.');
    }

    public function test_trajni_nasad_zauzima_parcelu_svake_godine(): void
    {
        [$u, $p, $k] = $this->nasad(['posadjena_povrsina_ha' => 10]);
        $this->actingAs($u);

        // parcela je 2027. i dalje puna
        Livewire::test(ParcelaManager::class)->set('kulturaParcelaId', $p->id)->set('kulturaForm.godina', 2027)
            ->assertSet('slobodno', 0.0)->assertSee('Parcela popunjena');
        $this->assertSame(1, Kultura::uGodini(2027)->count());
        $this->assertSame(0, Kultura::uGodini(2024)->count());   // prije unosa

        // iskrčen 2027.: te godine se još računa, ali sljedeće ne
        $k->update(['zavrsena_at' => '2027-03-01']);
        $this->assertSame(1, Kultura::uGodini(2027)->count());
        $this->assertSame(0, Kultura::uGodini(2028)->count());
    }

    public function test_jednogodisnja_kultura_i_dalje_vrijedi_samo_za_svoju_godinu(): void
    {
        [$u, $p] = $this->nasad(['posadjena_povrsina_ha' => 1]);
        Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Kukuruz', 'posadjena_povrsina_ha' => 9, 'godina' => 2026]);
        $this->actingAs($u);
        Livewire::test(ParcelaManager::class)->set('kulturaParcelaId', $p->id)->set('kulturaForm.godina', 2026)->assertSet('slobodno', 0.0);
        Livewire::test(ParcelaManager::class)->set('kulturaParcelaId', $p->id)->set('kulturaForm.godina', 2027)->assertSet('slobodno', 9.0);
    }

    public function test_forma_kulture_predlozi_i_sprema_nasad(): void
    {
        $this->assertTrue(Kultura::jeTrajniNaziv('Jabuka'));
        $this->assertTrue(Kultura::jeTrajniNaziv('vinova loza'));
        $this->assertFalse(Kultura::jeTrajniNaziv('Kukuruz'));
        $this->assertFalse(Kultura::jeTrajniNaziv('Jabukovača'));

        $u = User::factory()->create();
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1', 'povrsina_ha' => 5]);
        $this->actingAs($u);
        Livewire::test(ParcelaManager::class)->call('openKulturaForm', $p->id)
            ->set('kulturaForm.naziv', 'Kruška')->assertSet('kulturaForm.trajni', true)
            ->set('kulturaForm.sorta', 'Viljamovka')->set('kulturaForm.godina_sadnje', 2018)->set('kulturaForm.broj_stabala', 800)
            ->call('saveKultura')->assertHasNoErrors()
            ->assertSee('trajni nasad')->assertSee('Viljamovka')->assertSee('Iskrči')->assertDontSeeHtml('wire:click="zamijeniKulturu');

        $k = Kultura::firstOrFail();
        $this->assertTrue($k->trajni);
        $this->assertSame(800, $k->broj_stabala);

        // parcela iz ARKOD-a kao voćnjak: oznaka je već predložena
        $p2 = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '2', 'povrsina_ha' => 5, 'arkod_vrsta_uporabe' => 'Voćnjak']);
        Livewire::test(ParcelaManager::class)->call('openKulturaForm', $p2->id)->assertSet('kulturaForm.trajni', true);
    }

    public function test_postojecu_kulturu_ne_mijenja_sama_nego_na_klik(): void
    {
        [$u, , $k] = $this->nasad(['trajni' => false]);
        $this->assertFalse($k->fresh()->trajni);
        $this->actingAs($u);
        Livewire::test(ParcelaManager::class)->assertSee('označi kao trajni nasad')->call('oznaciTrajni', $k->id);
        $this->assertTrue($k->fresh()->trajni);
    }

    public function test_kultura_s_berbom_se_ne_brise(): void
    {
        [$u, , $k] = $this->nasad();
        Berba::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => '2026-09-10', 'kolicina_kg' => 100]);
        $this->actingAs($u);
        Livewire::test(ParcelaManager::class)->call('deleteKultura', $k->id);
        $this->assertNotNull($k->fresh());
    }

    public function test_dobit_iz_berbi_po_godinama(): void
    {
        $this->travelTo('2026-11-06 10:00');
        [$u, , $k] = $this->nasad(['prinos_t_ha' => 99, 'cijena_t' => 99, 'trosak_ostalo' => 50]);
        Berba::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => '2026-09-10', 'kolicina_kg' => 2000, 'cijena_kg' => 0.5]);
        Berba::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => '2026-09-20', 'kolicina_kg' => 1000]);   // bez cijene
        Berba::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => '2025-09-10', 'kolicina_kg' => 4000, 'cijena_kg' => 0.25]);
        NasadGodina::create(['kultura_id' => $k->id, 'godina' => 2026, 'potpora_ha' => 100, 'trosak_ostalo' => 300]);
        NasadGodina::create(['kultura_id' => $k->id, 'godina' => 2025, 'potpora_ha' => 10, 'trosak_ostalo' => 20]);
        $this->actingAs($u);

        $dobit = fn ($c) => $c->viewData('dobit')->first();

        // 2026.: berbe 1000 € + potpore 100 × 4 ha; trošak 300 €; ručni prinos se ignorira, a podaci kulture (2025.) ne ulaze
        $c = Livewire::test(TroskoviPregled::class);
        $this->assertEquals(1400, $dobit($c)['prihod']);
        $this->assertEquals(300, $dobit($c)['trosak']);
        $this->assertEquals(1100, $dobit($c)['dobit']);
        $this->assertEquals(110, $dobit($c)['dobit_ha']);   // grupirano po parceli: 1100 € na 10 ha
        $this->assertSame(1, $dobit($c)['berbe_bez_cijene']);
        $c->assertSee('Berbi bez cijene: 1');

        // 2025.: berbe 1000 € + potpore 10 × 4 ha; trošak 20 + 50 €
        $c->call('postavi', 'prosla');
        $this->assertEquals(1040, $dobit($c)['prihod']);
        $this->assertEquals(70, $dobit($c)['trosak']);
    }

    public function test_jednogodisnja_kultura_bez_berbi_koristi_rucni_prinos(): void
    {
        [, , $k] = $this->nasad(['trajni' => false, 'naziv' => 'Pšenica', 'prinos_t_ha' => 7, 'cijena_t' => 200, 'potpora_ha' => 100]);
        $r = Berbe::prihodKulture($k, null, false, now()->setYear(2030)->startOfYear(), now()->setYear(2030)->endOfYear());
        $this->assertEquals(7 * 4 * 200 + 400, $r['prihod']);
    }

    public function test_izvjestaji_i_kartica(): void
    {
        $this->travelTo('2026-11-06 10:00');
        [$u, , $k] = $this->nasad();
        Berba::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => '2026-09-10', 'kolicina_kg' => 2500, 'cijena_kg' => 0.4, 'sorta' => 'Idared', 'kupac' => 'Zadruga']);
        $this->actingAs($u);

        $this->get('/berba')->assertOk()->assertSee('Berba i žetva');
        $this->get('/izvjestaji/ispis/berba?od=2026-01-01&do=2026-12-31')->assertOk()->assertSee('Idared')->assertSee('10.09.2026.')->assertSee('Zadruga');
        $csv = $this->get('/izvjestaji/berba.csv?od=2026-01-01&do=2026-12-31');
        $csv->assertOk();
        $this->assertStringContainsString('10.09.2026;1515096;Jabuka;Idared', $csv->streamedContent());

        $this->get('/dashboard')->assertOk()->assertSee('Berba i žetva')->assertSee('2,50 t')->assertSee('zadnja 10.09.2026.');
        $this->get('/upute')->assertOk()->assertSee('Berba, žetva i trajni nasadi');
        $this->get('/novosti/berba-i-trajni-nasadi')->assertOk()->assertSee('Upozorenje na karencu');
    }
}
