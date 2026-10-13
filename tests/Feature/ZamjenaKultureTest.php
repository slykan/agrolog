<?php

namespace Tests\Feature;

use App\Livewire\ParcelaManager;
use App\Livewire\PrskanjeTable;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\Prskanje;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ZamjenaKultureTest extends TestCase
{
    use RefreshDatabase;

    private function setup_(): array
    {
        $u = User::factory()->create();
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1708275', 'povrsina_ha' => 2]);
        $jecam = Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Ječam', 'posadjena_povrsina_ha' => 2, 'godina' => 2026]);
        Prskanje::create(['user_id' => $u->id, 'kultura_id' => $jecam->id, 'datum_tretiranja' => '2026-04-10', 'trgovacki_naziv_sredstva' => 'X', 'kolicina_sredstva_l_ha' => 1]);

        return [$u, $p, $jecam];
    }

    public function test_zamjena_cuva_staru_kulturu_i_zapise(): void
    {
        [$u, $p, $jecam] = $this->setup_();

        Livewire::actingAs($u)->test(ParcelaManager::class)
            ->call('zamijeniKulturu', $jecam->id)
            ->set('kulturaForm.naziv', 'Kukuruz')
            ->set('zavrsenaDatum', '2026-07-05')
            ->call('saveKultura')
            ->assertHasNoErrors();

        $jecam->refresh();
        $this->assertSame('Ječam', $jecam->naziv);
        $this->assertSame('2026-07-05', $jecam->zavrsena_at->toDateString());
        $this->assertSame($jecam->id, Prskanje::first()->kultura_id);
        $this->assertDatabaseHas('kulture', ['parcela_id' => $p->id, 'naziv' => 'Kukuruz', 'zavrsena_at' => null]);
    }

    public function test_naziv_kulture_sa_zapisima_se_ne_mijenja(): void
    {
        [$u, , $jecam] = $this->setup_();

        Livewire::actingAs($u)->test(ParcelaManager::class)
            ->call('editKultura', $jecam->id)
            ->set('kulturaForm.naziv', 'Kukuruz')
            ->call('saveKultura')
            ->assertHasErrors('kulturaForm.naziv');

        $this->assertSame('Ječam', $jecam->fresh()->naziv);
    }

    public function test_kultura_sa_zapisima_se_ne_brise(): void
    {
        [$u, $p, $jecam] = $this->setup_();

        Livewire::actingAs($u)->test(ParcelaManager::class)
            ->call('deleteKultura', $jecam->id)
            ->call('deleteParcela', $p->id);

        $this->assertNotNull($jecam->fresh());
        $this->assertNotNull($p->fresh());
        $this->assertSame(1, Prskanje::count());
    }

    public function test_zavrsena_kultura_se_ne_nudi_za_nove_zapise(): void
    {
        [$u, , $jecam] = $this->setup_();
        $jecam->update(['zavrsena_at' => '2026-07-05']);

        $ids = Livewire::actingAs($u)->test(PrskanjeTable::class)->instance()->filteredKulture->pluck('id');
        $this->assertNotContains($jecam->id, $ids->all());
    }
}
