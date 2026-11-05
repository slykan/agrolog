<?php

namespace Tests\Feature;

use App\Livewire\DashPregled;
use App\Models\Gnojidba;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\Prskanje;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashPregledTest extends TestCase
{
    use RefreshDatabase;

    public function test_brojke_i_razdoblja(): void
    {
        $this->travelTo('2026-11-05 10:00');
        $u = User::factory()->create();
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1515096', 'povrsina_ha' => 12.5]);
        $k = Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Kukuruz', 'posadjena_povrsina_ha' => 12.5, 'godina' => 2026]);
        $prsk = fn ($d) => Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum_tretiranja' => $d, 'kolicina_sredstva_l_ha' => 1,
            'registracijski_broj' => 'UP/1', 'bbch' => '30', 'vrsta_uporabe' => 'otvoreno', 'sustav_proizvodnje' => 'neekoloska']);
        $prsk('2026-05-10');
        $prsk('2026-05-20');
        Gnojidba::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => '2026-03-01', 'tip_gnojiva' => 'KAN 27% N', 'kolicina_kg_ha' => 200]);
        $prsk('2025-05-10');   // prošla godina
        Prskanje::whereDate('datum_tretiranja', '2025-05-10')->update(['bbch' => null]);
        $this->actingAs($u);

        Livewire::test(DashPregled::class)
            ->assertViewHas('ukupno', 3)->assertViewHas('prskanja', 2)->assertViewHas('gnojidbe', 1)
            ->assertViewHas('promjena', 200)                      // 3 prema 1
            ->assertViewHas('mjeseci', fn ($m) => count($m) === 12 && $m[4]['prskanje'] === 2 && $m[2]['gnojidba'] === 1)
            ->assertViewHas('bezPodataka', 0)
            ->assertSee('12,5')
            ->call('postavi', 'prosla')->assertViewHas('ukupno', 1)->assertViewHas('bezPodataka', 1)
            ->call('postavi', '12mj')->assertViewHas('mjeseci', fn ($m) => count($m) === 12)
            ->call('postavi', 'oddo')->set('od', '2026-05-01')->set('do', '2026-05-31')->assertViewHas('ukupno', 2);

        $this->get('/dashboard')->assertOk()->assertSee('Pregled gospodarstva');
    }
}
