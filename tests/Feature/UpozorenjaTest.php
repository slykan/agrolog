<?php

namespace Tests\Feature;

use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\Prskanje;
use App\Models\Skladiste as SkladisteModel;
use App\Models\SkladisteUlaz;
use App\Models\Stroj;
use App\Models\StrojRok;
use App\Models\User;
use App\Support\Upozorenja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpozorenjaTest extends TestCase
{
    use RefreshDatabase;

    public function test_zvono_skuplja_rokove_evidenciju_i_skladiste(): void
    {
        $this->travelTo('2026-11-05 10:00');
        $u = User::factory()->create();
        $s = Stroj::create(['user_id' => $u->id, 'vrsta' => 'traktor', 'naziv' => 'John Deer x20']);
        StrojRok::create(['user_id' => $u->id, 'stroj_id' => $s->id, 'vrsta' => 'servis', 'datum_isteka' => '2026-11-30', 'podsjetnik_dana' => 30]);
        StrojRok::create(['user_id' => $u->id, 'stroj_id' => $s->id, 'vrsta' => 'registracija', 'datum_isteka' => '2027-06-01', 'podsjetnik_dana' => 30]);   // daleko: ne
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1515096', 'povrsina_ha' => 10]);
        $k = Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Pšenica', 'eppo_kod' => 'TRZAX', 'posadjena_povrsina_ha' => 2, 'godina' => 2026]);
        Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum_tretiranja' => '2026-05-01', 'trgovacki_naziv_sredstva' => 'Roundup',
            'kolicina_sredstva_l_ha' => 3, 'vrsta_uporabe' => 'otvoreno', 'sustav_proizvodnje' => 'neekoloska']);   // bez reg. broja
        $skl = SkladisteModel::create(['user_id' => $u->id, 'naziv' => 'Glavno', 'zadano' => true]);
        SkladisteUlaz::create(['user_id' => $u->id, 'skladiste_id' => $skl->id, 'vrsta' => 'szb', 'naziv' => 'Roundup', 'datum' => '2026-03-01', 'kolicina' => 5, 'jedinica' => 'L']);   // 5 − 6 = minus

        $z = Upozorenja::za($u);
        $this->assertCount(3, $z);
        $this->assertSame('crveno', $z[0]['razina']);                       // minus na skladištu prvi
        $this->assertStringContainsString('U minusu: -1 L', $z[0]['tekst']);
        $this->assertTrue($z->contains(fn ($s) => $s['naslov'] === 'John Deer x20 · Sljedeći servis' && str_contains($s['tekst'], 'za 25 d. (30.11.2026.)')));
        $this->assertTrue($z->contains(fn ($s) => str_contains($s['tekst'], '1 prskanje bez reg. broja ili fenofaze')));

        $this->actingAs($u)->get('/dashboard')->assertOk()->assertSee('Obavijesti (3)', false)->assertSee('John Deer x20');
    }

    public function test_prazno_zvono(): void
    {
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertOk()->assertSee('Nema ništa što traži pažnju');
    }
}
