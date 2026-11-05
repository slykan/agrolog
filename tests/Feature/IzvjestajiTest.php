<?php

namespace Tests\Feature;

use App\Livewire\IzvjestajiPregled;
use App\Models\Gnojidba;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\Prskanje;
use App\Models\Skladiste as SkladisteModel;
use App\Models\SkladisteUlaz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IzvjestajiTest extends TestCase
{
    use RefreshDatabase;

    private function podaci(): User
    {
        $this->travelTo('2026-11-05 10:00');
        $u = User::factory()->create(['naziv_gospodarstva' => 'OPG Ratar', 'mipg' => '123456']);
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1515096', 'povrsina_ha' => 10]);
        $k = Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Pšenica', 'posadjena_povrsina_ha' => 4, 'godina' => 2026]);
        Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum_tretiranja' => '2026-04-01', 'trgovacki_naziv_sredstva' => 'Roundup',
            'kolicina_sredstva_l_ha' => 2.5, 'tretirana_povrsina_ha' => 2, 'vrsta_uporabe' => 'otvoreno', 'sustav_proizvodnje' => 'neekoloska']);
        Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum_tretiranja' => '2025-04-01', 'trgovacki_naziv_sredstva' => 'Staro',
            'kolicina_sredstva_l_ha' => 1, 'vrsta_uporabe' => 'otvoreno', 'sustav_proizvodnje' => 'neekoloska']);
        Gnojidba::create(Gnojidba::sHranivima(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => '2026-03-10', 'tip_gnojiva' => 'KAN 27% N', 'kolicina_kg_ha' => 250]));
        $s = SkladisteModel::create(['user_id' => $u->id, 'naziv' => 'Glavno', 'zadano' => true]);
        SkladisteUlaz::create(['user_id' => $u->id, 'skladiste_id' => $s->id, 'vrsta' => 'gnojivo', 'naziv' => 'KAN 27% N', 'datum' => '2026-03-01', 'kolicina' => 2000, 'jedinica' => 'kg', 'cijena' => 1000]);

        return $u;
    }

    public function test_stranica_i_brojke(): void
    {
        $u = $this->podaci();
        $this->actingAs($u);
        Livewire::test(IzvjestajiPregled::class)
            ->assertViewHas('brojPrskanja', 1)->assertViewHas('brojGnojidbi', 1)
            ->assertViewHas('sredstva', 5.0)                 // 2,5 L/ha × 2 ha
            ->assertViewHas('gnojiva', 1000.0)               // 250 kg/ha × 4 ha
            ->assertViewHas('dusik', 270.0)                  // 67,5 kg N/ha × 4 ha
            ->assertViewHas('brojPrometa', 2)                // nabava + potrošnja
            ->assertSee('od=2026-01-01', false);
        $this->get('/izvjestaji')->assertOk()->assertSee('Izvoz za odabrano razdoblje');
    }

    public function test_ispis_i_csv(): void
    {
        $u = $this->podaci();
        $this->actingAs($u);
        $q = '?od=2026-01-01&do=2026-12-31';

        $this->get('/izvjestaji/ispis/prskanje' . $q)->assertOk()->assertSee('Primjena sredstava za zaštitu bilja')
            ->assertSee('Roundup')->assertDontSee('Staro')->assertSee('01.04.2026.')->assertSee('OPG Ratar')->assertSee('01.01.2026. – 31.12.2026.', false);
        $this->get('/izvjestaji/ispis/gnojidba' . $q)->assertOk()->assertSee('KAN 27% N')->assertSee('27-0-0');
        $this->get('/izvjestaji/ispis/nesto' . $q)->assertNotFound();

        $csv = $this->get('/izvjestaji/gnojidba.csv' . $q)->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('10.03.2026;1515096;Pšenica;4;"KAN 27% N";250;1000;27', $csv);

        $csv = $this->get('/izvjestaji/skladiste.csv' . $q)->streamedContent();
        $this->assertStringContainsString('Nabava', $csv);
        $this->assertStringContainsString('"Potrošnja (gnojidba)"', $csv);
        $this->assertStringContainsString('-500', $csv);   // 1000 kg × 0,5 €/kg

        $csv = $this->get('/prskanje/izvoz.csv' . $q)->streamedContent();
        $this->assertStringContainsString('01.04.2026', $csv);
        $this->assertStringNotContainsString('Staro', $csv);
    }
}
