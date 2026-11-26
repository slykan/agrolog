<?php

namespace Tests\Feature;

use App\Livewire\GnojidbaTable;
use App\Livewire\ParcelaManager;
use App\Livewire\PrskanjeTable;
use App\Models\Gnojidba;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\Prskanje;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

/** Vlastiti naziv parcele ("Iza kuće") uz ARKOD broj u svim popisima. */
class ParcelaNazivTest extends TestCase
{
    use RefreshDatabase;

    public function test_naziv_se_sprema_uz_arkod_parcelu_i_moze_se_obrisati(): void
    {
        Cache::flush();
        Http::fake(fn () => Http::response(file_get_contents(base_path('tests/fixtures/arkod-1515096.xml'))));
        $u = User::factory()->create();

        Livewire::actingAs($u)->test(ParcelaManager::class)
            ->set('showParcelaForm', true)
            ->set('parcelaForm.arkod_broj', '1515096')
            ->set('parcelaForm.naziv', '  Kod bunara ')
            ->call('saveParcela')->assertHasNoErrors()
            ->assertSee('Kod bunara');

        $p = Parcela::first();
        $this->assertSame('Kod bunara', $p->naziv);

        Livewire::actingAs($u)->test(ParcelaManager::class)
            ->call('editParcela', $p->id)->assertSet('parcelaForm.naziv', 'Kod bunara')
            ->set('parcelaForm.naziv', '')->call('saveParcela')->assertHasNoErrors();
        $this->assertNull($p->fresh()->naziv);
    }

    public function test_naziv_se_vidi_u_prskanju_gnojidbi_i_trazilici(): void
    {
        $u = User::factory()->create();
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1708275', 'naziv' => 'Iza kuće', 'povrsina_ha' => 3]);
        $k = Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Kukuruz', 'posadjena_povrsina_ha' => 3, 'godina' => date('Y')]);
        Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum_tretiranja' => date('Y') . '-05-10', 'tretirana_povrsina_ha' => 3,
            'trgovacki_naziv_sredstva' => 'Sredstvo', 'kolicina_sredstva_l_ha' => 1, 'vrsta_uporabe' => 'otvoreno', 'sustav_proizvodnje' => 'neekoloska']);
        Gnojidba::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => date('Y') . '-04-01', 'tip_gnojiva' => 'KAN', 'kolicina_kg_ha' => 200]);

        $this->actingAs($u);
        Livewire::test(PrskanjeTable::class)->assertSee('Iza kuće')->set('search', 'iza ku')->assertSee('Sredstvo');
        Livewire::test(GnojidbaTable::class)->assertSee('Iza kuće');
        $this->get(route('dashboard'))->assertOk()->assertSee('Iza kuće');

        $this->assertSame('1708275 · Iza kuće', Parcela::kratko('1708275', 'Iza kuće'));
        $this->assertSame('Ručno ucrtana: Njiva', Parcela::oznaka('Njiva', true));
    }
}
