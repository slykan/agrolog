<?php

namespace Tests\Feature;

use App\Livewire\ParcelaManager;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SlobodnaPovrsinaTest extends TestCase
{
    use RefreshDatabase;

    public function test_nova_kultura_dobije_ostatak_a_puna_parcela_ne_dopusta_dodavanje(): void
    {
        $u = User::factory()->create();
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1515096', 'povrsina_ha' => 10]);
        $k = Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Pšenica', 'posadjena_povrsina_ha' => 6, 'godina' => (int) date('Y')]);
        Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Stara', 'posadjena_povrsina_ha' => 4, 'godina' => (int) date('Y'), 'zavrsena_at' => now()]);   // završena se ne broji
        $this->actingAs($u);

        // ostatak 4 ha; "Spremi i nova" s 3 ha ostavi 1 ha
        $lw = Livewire::test(ParcelaManager::class)
            ->call('openKulturaForm', $p->id)
            ->assertSet('showKulturaForm', true)
            ->assertSet('kulturaForm.posadjena_povrsina_ha', 4.0)
            ->set('kulturaForm.naziv', 'Kukuruz')->set('kulturaForm.posadjena_povrsina_ha', 3)
            ->call('saveKultura', true)->assertHasNoErrors()
            ->assertSet('kulturaForm.posadjena_povrsina_ha', 1.0);

        // zadnji hektar popuni parcelu: forma se zatvori
        $lw->set('kulturaForm.naziv', 'Soja')->call('saveKultura', true)
            ->assertSet('showKulturaForm', false)->assertSet('poruka', fn ($p) => str_contains($p, 'popunjena'));

        // puna parcela: forma se ne otvara, gumb je zamijenjen natpisom
        Livewire::test(ParcelaManager::class)
            ->call('openKulturaForm', $p->id)
            ->assertSet('showKulturaForm', false)
            ->assertSet('poruka', fn ($p) => str_contains($p, 'Zamijeni kulturu'))
            ->assertSee('Parcela popunjena');

        // druga godina je slobodna
        $k->update(['godina' => (int) date('Y') - 1]);
        Livewire::test(ParcelaManager::class)->call('openKulturaForm', $p->id)->assertSet('kulturaForm.posadjena_povrsina_ha', 6.0)
            ->set('kulturaForm.godina', (int) date('Y') - 1)->assertSet('kulturaForm.posadjena_povrsina_ha', 4.0);
    }
}
