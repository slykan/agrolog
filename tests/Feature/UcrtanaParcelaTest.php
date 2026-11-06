<?php

namespace Tests\Feature;

use App\Livewire\ParcelaManager;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\Prskanje;
use App\Models\User;
use App\Support\ArkodServis;
use App\Support\Poligon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class UcrtanaParcelaTest extends TestCase
{
    use RefreshDatabase;

    private const KVADRAT = [[45.4300, 18.5900], [45.4300, 18.5920], [45.4310, 18.5920], [45.4310, 18.5900]];

    private function ucrtana(User $u, string $naziv = 'Iza kuće'): Parcela
    {
        return Parcela::create(['user_id' => $u->id, 'arkod_broj' => $naziv, 'povrsina_ha' => 1.74] + Parcela::ucrtanaPolja(self::KVADRAT));
    }

    public function test_povrsina_odgovara_sluzbenoj_arkod_povrsini(): void
    {
        $arkod = ArkodServis::procitaj(file_get_contents(base_path('tests/fixtures/arkod-1515096.xml')), '1515096');

        $this->assertEqualsWithDelta($arkod['povrsina_ha'], Poligon::ha($arkod['poligon']), $arkod['povrsina_ha'] * 0.02);
    }

    public function test_provjera_tocaka_i_krizanja(): void
    {
        $this->assertNull(Poligon::ocisti(null));
        $this->assertNull(Poligon::ocisti([[45, 18], [45, 19]]));
        $this->assertNull(Poligon::ocisti([[45, 18], [45, 19], ['x', 19]]));
        $this->assertNull(Poligon::ocisti([[145, 18], [45, 19], [46, 19]]));
        $this->assertNull(Poligon::ocisti(array_fill(0, Poligon::MAX_TOCAKA + 1, [45, 18])));

        $this->assertFalse(Poligon::kriziSe(self::KVADRAT));
        [$a, $b, $c, $d] = self::KVADRAT;
        $this->assertTrue(Poligon::kriziSe([$a, $c, $b, $d]));
    }

    public function test_spremanje_ucrtane_parcele_bez_pozivanja_arkoda(): void
    {
        Http::fake();
        $u = User::factory()->create();

        $lw = Livewire::actingAs($u)->test(ParcelaManager::class)
            ->call('novaParcela')->call('ucrtajNaKarti')
            ->set('parcelaForm.arkod_broj', '  Njiva 12  ')
            ->set('rucniPoligon', self::KVADRAT);

        $ha = (float) $lw->get('parcelaForm.povrsina_ha');
        $this->assertEqualsWithDelta(Poligon::ha(self::KVADRAT), $ha, 0.01);
        $this->assertGreaterThan(1, $ha);

        $lw->call('saveParcela')->assertHasNoErrors()->assertSet('rucnoUnos', false)->assertSet('showKulturaForm', true);

        $p = Parcela::firstOrFail();
        $this->assertSame('Njiva 12', $p->arkod_broj);
        $this->assertTrue($p->rucno_ucrtana);
        $this->assertCount(4, $p->arkod_poligon);
        $this->assertNull($p->arkod_povrsina_ha);
        $this->assertNotNull($p->lat);
        Http::assertNothingSent();   // "Njiva 12" se ne smije tražiti u ARKOD-u kao broj 12
    }

    public function test_bez_oblika_naziva_ili_s_krizanjem_se_ne_sprema(): void
    {
        $u = User::factory()->create();
        $this->ucrtana($u);
        [$a, $b, $c, $d] = self::KVADRAT;

        Livewire::actingAs($u)->test(ParcelaManager::class)->call('novaParcela')->call('ucrtajNaKarti')
            ->set('parcelaForm.arkod_broj', 'Nova')->set('parcelaForm.povrsina_ha', 1)
            ->call('saveParcela')->assertHasErrors('rucniPoligon')
            ->set('rucniPoligon', [$a, $c, $b, $d])->call('saveParcela')->assertHasErrors('rucniPoligon')
            ->set('rucniPoligon', self::KVADRAT)->set('parcelaForm.arkod_broj', '')->call('saveParcela')->assertHasErrors('parcelaForm.arkod_broj')
            ->set('parcelaForm.arkod_broj', 'Iza kuće')->call('saveParcela')->assertHasErrors('parcelaForm.arkod_broj');

        $this->assertSame(1, Parcela::count());
    }

    public function test_uredjivanje_i_pretvaranje_u_arkod_cuva_evidenciju(): void
    {
        Cache::flush();
        Http::fake(fn () => Http::response(file_get_contents(base_path('tests/fixtures/arkod-1515096.xml'))));
        $u = User::factory()->create();
        $p = $this->ucrtana($u);
        $k = Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Šljiva', 'posadjena_povrsina_ha' => 1, 'godina' => date('Y')]);

        $lw = Livewire::actingAs($u)->test(ParcelaManager::class)
            ->call('editParcela', $p->id)->assertSet('rucnoUnos', true)->assertSet('rucniPoligon', self::KVADRAT)
            ->set('parcelaForm.arkod_broj', 'Voćnjak')->call('saveParcela')->assertHasNoErrors();
        $this->assertSame('Voćnjak', $p->fresh()->arkod_broj);
        $this->assertTrue($p->fresh()->rucno_ucrtana);

        $lw->call('editParcela', $p->id)->call('imamArkodBroj')->assertSet('rucnoUnos', false)
            ->set('parcelaForm.arkod_broj', '1515096')->call('saveParcela')->assertHasNoErrors();

        $p->refresh();
        $this->assertFalse($p->rucno_ucrtana);
        $this->assertSame('1515096', $p->arkod_broj);
        $this->assertNotNull($p->arkod_provjereno_at);
        $this->assertNotEquals(self::KVADRAT, $p->arkod_poligon);
        $this->assertSame($p->id, $k->fresh()->parcela_id);
    }

    public function test_arkod_parcela_se_moze_prebaciti_na_ucrtanu_bez_starih_arkod_podataka(): void
    {
        $u = User::factory()->create();
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '999', 'povrsina_ha' => 1, 'arkod_povrsina_ha' => 5, 'arkod_naziv' => 'Staro']);

        Livewire::actingAs($u)->test(ParcelaManager::class)->call('editParcela', $p->id)->call('ucrtajNaKarti')
            ->set('parcelaForm.arkod_broj', 'Moja')->set('rucniPoligon', self::KVADRAT)->call('saveParcela')->assertHasNoErrors();

        $p->refresh();
        $this->assertTrue($p->rucno_ucrtana);
        $this->assertNull($p->arkod_povrsina_ha);
        $this->assertNull($p->arkod_naziv);
    }

    public function test_tudja_parcela_se_ne_moze_otvoriti(): void
    {
        $p = $this->ucrtana(User::factory()->create());

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Livewire::actingAs(User::factory()->create())->test(ParcelaManager::class)->call('editParcela', $p->id);
    }

    public function test_karta_ne_zove_arkod_za_ucrtanu(): void
    {
        Http::fake();
        $u = User::factory()->create();
        $p = $this->ucrtana($u, 'Njiva 12');
        $p->update(['arkod_poligon' => null]);

        Livewire::actingAs($u)->test(ParcelaManager::class)->call('prikaziKartu', $p->id)->assertDispatched('arkod-karta');
        $this->actingAs($u)->getJson(route('parcela.arkod', $p))->assertOk();
        Http::assertNothingSent();
    }

    public function test_izvozi_i_ispisi_oznacuju_ucrtanu_a_ne_arkod(): void
    {
        $u = User::factory()->create();
        $p = $this->ucrtana($u);
        $k = Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Šljiva', 'posadjena_povrsina_ha' => 1, 'godina' => date('Y')]);
        Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum_tretiranja' => date('Y') . '-05-01', 'tretirana_povrsina_ha' => 1, 'trgovacki_naziv_sredstva' => 'Test']);

        $csv = $this->actingAs($u)->get(route('prskanje.csv'))->assertOk()->streamedContent();
        $this->assertStringContainsString('Ručno ucrtana: Iza kuće', $csv);

        // datoteku treba pročitati prije nego je odgovor pri slanju obriše
        $odgovor = $this->actingAs($u)->get(route('prskanje.xlsx'))->assertOk();
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($odgovor->baseResponse->getFile()->getPathname()) === true);
        $xml = $zip->getFromName('xl/worksheets/sheet2.xml');
        $zip->close();
        $this->assertStringContainsString('Ručno ucrtana: Iza kuće', $xml);
        $this->assertStringNotContainsString('ARKOD Iza kuće', $xml);

        $this->actingAs($u)->get(route('izvjestaji.ispis', ['vrsta' => 'prskanje', 'godina' => date('Y')]))->assertOk()->assertSee('Ručno ucrtana: Iza kuće');
    }

    public function test_upute_opisuju_rucno_ucrtavanje(): void
    {
        $this->get(route('docs'))->assertOk()
            ->assertSee('href="#parcela-bez-arkoda"', false)
            ->assertSee('id="parcela-bez-arkoda"', false)
            ->assertSee('Dodaj točku gdje stojim')
            ->assertSee('Nemam ARKOD. Mogu li ipak koristiti AgroLog?');
    }

    public function test_stranica_nudi_ucrtavanje(): void
    {
        $u = User::factory()->create();
        Livewire::actingAs($u)->test(ParcelaManager::class)->call('novaParcela')
            ->assertSee('Nemam ARKOD, ucrtaj na karti')
            ->call('ucrtajNaKarti')->assertSee('Naziv parcele')->assertSee('nije službena ARKOD parcela');
    }
}
