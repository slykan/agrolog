<?php

namespace Tests\Feature;

use App\Livewire\ParcelaManager;
use App\Models\Parcela;
use App\Models\User;
use App\Support\ArkodServis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/** Parcela iz javnog ARKOD-a (APPRRR WMS, GeoRSS) — fixture je stvarni odgovor servisa za parcelu 1515096. */
class ArkodKartaTest extends TestCase
{
    use RefreshDatabase;

    private function lazniArkod(): void
    {
        Cache::flush();
        Http::fake(function ($request) {
            $nadjena = str_contains(urldecode($request->url()), 'id=1515096');

            return Http::response(file_get_contents(base_path('tests/fixtures/' . ($nadjena ? 'arkod-1515096.xml' : 'arkod-prazno.xml'))));
        });
    }

    public function test_citanje_georss_odgovora(): void
    {
        $p = ArkodServis::procitaj(file_get_contents(base_path('tests/fixtures/arkod-1515096.xml')), '1515096');

        $this->assertSame(18.75, $p['povrsina_ha']);
        $this->assertSame('KOD PETROSA', $p['naziv']);
        $this->assertSame('Oranica', $p['vrsta_uporabe']);
        $this->assertCount(40, $p['poligon']);
        $this->assertEqualsWithDelta(45.549, $p['centar'][0], 0.01);
        $this->assertEqualsWithDelta(18.642, $p['centar'][1], 0.01);
        $this->assertNull(ArkodServis::procitaj(file_get_contents(base_path('tests/fixtures/arkod-prazno.xml')), '999'));
    }

    public function test_unos_arkod_broja_pokaze_parcelu_i_preuzme_povrsinu(): void
    {
        $this->lazniArkod();
        $u = User::factory()->create();

        Livewire::actingAs($u)->test(ParcelaManager::class)
            ->set('showParcelaForm', true)
            ->set('parcelaForm.arkod_broj', '1515096')
            ->assertSet('arkodNadjen.povrsina_ha', 18.75)
            ->assertSet('parcelaForm.povrsina_ha', 18.75)
            ->assertDispatched('arkod-pregled')
            ->assertSee('Potvrdi i spremi')
            ->call('saveParcela')
            ->assertHasNoErrors();

        $p = Parcela::first();
        $this->assertSame('1515096', $p->arkod_broj);
        $this->assertCount(40, $p->arkod_poligon);
        $this->assertEquals(18.75, $p->arkod_povrsina_ha);
        $this->assertNotNull($p->arkod_provjereno_at);
    }

    public function test_nepostojeci_arkod_se_moze_spremiti_bez_karte(): void
    {
        $this->lazniArkod();
        $u = User::factory()->create();

        Livewire::actingAs($u)->test(ParcelaManager::class)
            ->set('showParcelaForm', true)
            ->set('parcelaForm.arkod_broj', '123')
            ->assertSet('arkodNadjen', null)
            ->assertSee('nije pronađen')
            ->set('parcelaForm.povrsina_ha', 2)
            ->call('saveParcela')
            ->assertHasNoErrors();

        $this->assertNull(Parcela::first()->arkod_poligon);
    }

    public function test_ikona_karte_dohvati_oblik_stare_parcele(): void
    {
        $this->lazniArkod();
        $u = User::factory()->create();
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1515096', 'povrsina_ha' => 20]);

        Livewire::actingAs($u)->test(ParcelaManager::class)
            ->call('prikaziKartu', $p->id)
            ->assertDispatched('arkod-karta', fn ($name, $params) => $params['arkod'] === '1515096' && count($params['poligon']) === 40 && $params['arkodPovrsina'] === 18.75)
            ->assertSee('ARKOD: 18.75 ha');   // upisano 20 ha > 18,75 ha iz ARKOD-a
    }

    public function test_dashboard_karta_i_json(): void
    {
        $this->lazniArkod();
        $u = User::factory()->create();
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1515096', 'povrsina_ha' => 18.75]);

        $this->actingAs($u)->get('/dashboard')->assertOk()->assertSee('Karta mojih parcela')->assertSee('dashKarta(', false);
        $this->actingAs($u)->getJson("/parcele/{$p->id}/arkod")->assertOk()->assertJsonCount(40, 'poligon');

        $tudi = User::factory()->create();
        $this->actingAs($tudi)->getJson("/parcele/{$p->id}/arkod")->assertNotFound();
    }
}
