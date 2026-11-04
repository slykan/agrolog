<?php

namespace Tests\Feature;

use App\Livewire\ParcelaManager;
use App\Livewire\PrskanjeTable;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\Prskanje;
use App\Models\User;
use App\Support\SzbPopisi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

/** Ministarstvov format evidencije SZB (EU 2023/564), obvezan od 1.1.2027. */
class Evidencija2027Test extends TestCase
{
    use RefreshDatabase;

    public function test_eppo_se_prepoznaje_iz_naziva_kulture(): void
    {
        $this->assertSame(['Pšenica', 'TRZAX'], SzbPopisi::usjevZaKulturu('pšenica'));
        $this->assertSame(['Kukuruz', 'ZEAMX'], SzbPopisi::usjevZaKulturu('Kukuruz hibrid 2026'));
        $this->assertNull(SzbPopisi::usjevZaKulturu('Nešto izmišljeno'));
        $this->assertNotEmpty(SzbPopisi::bbchZaUsjev('Pšenica'));
    }

    public function test_kultura_dobiva_eppo_kod(): void
    {
        $u = User::factory()->create();
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1708275', 'povrsina_ha' => 5]);

        Livewire::actingAs($u)->test(ParcelaManager::class)
            ->call('openKulturaForm', $p->id)
            ->set('kulturaForm.naziv', 'Soja')
            ->assertSet('kulturaForm.eppo_naziv', 'Soja')
            ->set('kulturaForm.posadjena_povrsina_ha', 2)
            ->call('saveKultura')
            ->assertHasNoErrors();
        $this->assertDatabaseHas('kulture', ['naziv' => 'Soja', 'eppo_kod' => 'GLXMA']);

        // usjev koji nije s popisa se ne prihvaća
        Livewire::actingAs($u)->test(ParcelaManager::class)
            ->call('openKulturaForm', $p->id)
            ->set('kulturaForm.naziv', 'Moja mješavina')
            ->set('kulturaForm.eppo_naziv', 'Ne postoji')
            ->set('kulturaForm.posadjena_povrsina_ha', 1)
            ->call('saveKultura')
            ->assertHasErrors('kulturaForm.eppo_naziv');
    }

    public function test_prskanje_sprema_fenofazu_i_vrstu_uporabe(): void
    {
        $u = User::factory()->create();
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1708275', 'povrsina_ha' => 5]);
        $k = Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Pšenica', 'eppo_naziv' => 'Pšenica', 'eppo_kod' => 'TRZAX', 'posadjena_povrsina_ha' => 5, 'godina' => 2026]);

        Livewire::actingAs($u)->test(PrskanjeTable::class)
            ->assertSet('form.vrsta_uporabe', 'otvoreno')
            ->assertSet('form.sustav_proizvodnje', 'neekoloska')
            ->call('addRow')
            ->call('selectKultura', $k->id)
            ->set('form.trgovacki_naziv_sredstva', 'Folicur')
            ->set('form.kolicina_sredstva_l_ha', 1)
            ->set('form.bbch', 'Pšenica - Klasanje')
            ->set('form.sustav_proizvodnje', 'ekoloska')
            ->call('saveNew')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('prskanja', ['bbch' => 'Pšenica - Klasanje', 'vrsta_uporabe' => 'otvoreno', 'sustav_proizvodnje' => 'ekoloska']);

        // sljedeći unos preuzima sustav proizvodnje iz zadnjeg
        Livewire::actingAs($u)->test(PrskanjeTable::class)->assertSet('form.sustav_proizvodnje', 'ekoloska');

        Livewire::actingAs($u)->test(PrskanjeTable::class)
            ->call('addRow')
            ->call('selectKultura', $k->id)
            ->set('form.kolicina_sredstva_l_ha', 1)
            ->set('form.vrsta_uporabe', 'svemir')
            ->call('saveNew')
            ->assertHasErrors('form.vrsta_uporabe');
    }

    public function test_excel_izvoz_u_ministarstvovom_predlosku(): void
    {
        $u = User::factory()->create(['name' => 'Ivo Ratar', 'oib' => '12345678901']);
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '2468135', 'povrsina_ha' => 5]);
        $k = Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'pšenica ozima', 'eppo_naziv' => 'Pšenica', 'eppo_kod' => 'TRZAX', 'posadjena_povrsina_ha' => 5, 'godina' => 2026]);
        Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum_tretiranja' => '2026-05-01', 'vrijeme_od' => '07:30', 'tretirana_povrsina_ha' => 3.2,
            'trgovacki_naziv_sredstva' => 'Sredstvo <Ž> & co', 'registracijski_broj' => 'REG-42', 'kolicina_sredstva_l_ha' => 1.5,
            'vrsta_uporabe' => 'otvoreno', 'bbch' => 'Pšenica - Klasanje', 'sustav_proizvodnje' => 'neekoloska']);

        $tudi = User::factory()->create();
        $tp = Parcela::create(['user_id' => $tudi->id, 'arkod_broj' => '999', 'povrsina_ha' => 1]);
        $tk = Kultura::create(['user_id' => $tudi->id, 'parcela_id' => $tp->id, 'naziv' => 'Soja', 'posadjena_povrsina_ha' => 1, 'godina' => 2026]);
        Prskanje::create(['user_id' => $tudi->id, 'kultura_id' => $tk->id, 'datum_tretiranja' => '2026-05-02', 'trgovacki_naziv_sredstva' => 'Tuđe sredstvo', 'kolicina_sredstva_l_ha' => 1]);

        $res = $this->actingAs($u)->get('/prskanje/izvoz.xlsx')->assertOk();
        $put = $res->baseResponse->getFile()->getPathname();

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($put) === true);
        $sheet = $zip->getFromName('xl/worksheets/sheet2.xml');
        $this->assertFalse($zip->getFromName('xl/calcChain.xml'));
        $this->assertStringNotContainsString('calcChain', $zip->getFromName('[Content_Types].xml'));
        $zip->close();

        $this->assertNotFalse(simplexml_load_string($sheet), 'list "tablica" mora ostati ispravan XML');
        foreach (['Ivo Ratar, OIB 12345678901', 'Poljoprivredne površine na otvorenom prostoru', 'Sredstvo &lt;Ž&gt; &amp; co', 'REG-42',
                  '01.05.2026.', '07:30', '1,5 l/ha', 'ARKOD 2468135', '3,2 ha', '>Pšenica<', 'TRZAX', 'Pšenica - Klasanje', 'Neekološka površina'] as $v) {
            $this->assertStringContainsString($v, $sheet);
        }
        $this->assertStringNotContainsString('Tuđe sredstvo', $sheet);
        $this->assertStringNotContainsString('AVESA', $sheet); // primjer iz predloška je uklonjen
    }

    public function test_novost_je_objavljena_i_istaknuta(): void
    {
        $this->get('/novosti')->assertOk()->assertSee('AgroLog je spreman za 2027.');
        $this->get('/novosti/evidencija-szb-format-ministarstva-2027')->assertOk()->assertSee('Excel (Ministarstvo)');
        $this->get('/novosti/ne-postoji')->assertNotFound();
        // naslovnica ističe najnoviju novost; sve novosti se otvaraju
        $prva = \App\Http\Controllers\SadrzajController::novosti()[0]['slug'];
        $this->get('/')->assertOk()->assertSee(route('novost', $prva));
        foreach (\App\Http\Controllers\SadrzajController::novosti() as $n) {
            $this->get('/novosti/' . $n['slug'])->assertOk()->assertSee($n['title']);
        }
        $this->get('/sitemap.xml')->assertOk()->assertSee('/novosti/evidencija-szb-format-ministarstva-2027');
    }

    public function test_excel_izvoz_traze_prijavu(): void
    {
        $this->get('/prskanje/izvoz.xlsx')->assertRedirect('/login');
    }
}
