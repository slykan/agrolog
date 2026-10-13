<?php

namespace Tests\Feature;

use App\Livewire\PrskanjeTable;
use App\Models\Prskanje;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class EvidencijaSzbTest extends TestCase
{
    use RefreshDatabase;

    private function kultura(User $u): int
    {
        $p = DB::table('parcele')->insertGetId(['user_id' => $u->id, 'arkod_broj' => '2468135', 'povrsina_ha' => 3.2, 'created_at' => now(), 'updated_at' => now()]);

        return DB::table('kulture')->insertGetId(['user_id' => $u->id, 'parcela_id' => $p, 'naziv' => 'Pšenica', 'posadjena_povrsina_ha' => 3.2, 'godina' => 2026, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function test_sprema_registracijski_broj(): void
    {
        $u = User::factory()->create();
        $k = $this->kultura($u);

        Livewire::actingAs($u)->test(PrskanjeTable::class)
            ->call('addRow')
            ->set('form.kultura_id', $k)
            ->set('form.trgovacki_naziv_sredstva', 'Sredstvo X')
            ->set('form.registracijski_broj', 'UP/I-320-20/19-01/123')
            ->set('form.kolicina_sredstva_l_ha', 1.5)
            ->call('saveNew')
            ->assertHasNoErrors();

        $this->assertSame('UP/I-320-20/19-01/123', Prskanje::first()->registracijski_broj);
    }

    public function test_registracijski_broj_se_predlaze_iz_ranijeg_unosa(): void
    {
        $u = User::factory()->create();
        $k = $this->kultura($u);
        Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k, 'datum_tretiranja' => '2026-05-01', 'trgovacki_naziv_sredstva' => 'Sredstvo X', 'registracijski_broj' => 'REG-42', 'kolicina_sredstva_l_ha' => 1]);

        Livewire::actingAs($u)->test(PrskanjeTable::class)
            ->call('addRow')
            ->set('form.trgovacki_naziv_sredstva', 'Sredstvo X')
            ->assertSet('form.registracijski_broj', 'REG-42');
    }

    public function test_csv_izvoz_samo_vlastitih_zapisa(): void
    {
        $u = User::factory()->create();
        $k = $this->kultura($u);
        Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k, 'datum_tretiranja' => '2026-05-01', 'trgovacki_naziv_sredstva' => 'Sredstvo Ž', 'registracijski_broj' => 'REG-42', 'kolicina_sredstva_l_ha' => 1.5, 'tretirana_povrsina_ha' => 3.2]);

        $drugi = User::factory()->create();
        $k2 = $this->kultura($drugi);
        Prskanje::create(['user_id' => $drugi->id, 'kultura_id' => $k2, 'datum_tretiranja' => '2026-05-02', 'trgovacki_naziv_sredstva' => 'Tuđe sredstvo', 'kolicina_sredstva_l_ha' => 2]);

        $csv = $this->actingAs($u)->get('/prskanje/izvoz.csv')->assertOk()->streamedContent();

        $this->assertStringContainsString('Registracijski broj', $csv);
        $this->assertStringContainsString('2026-05-01;;;2468135;Pšenica;3,2;"Sredstvo Ž";REG-42;1,5;', $csv);
        $this->assertStringNotContainsString('Tuđe sredstvo', $csv);
    }

    public function test_izvoz_zahtijeva_prijavu(): void
    {
        $this->get('/prskanje/izvoz.csv')->assertRedirect('/login');
    }
}
