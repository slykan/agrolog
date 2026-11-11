<?php

namespace Tests\Feature;

use App\Livewire\GnojidbaTable;
use App\Livewire\KalendarPregled;
use App\Livewire\PrskanjeTable;
use App\Livewire\RadoviTable;
use App\Models\Berba;
use App\Models\Gnojidba;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\PlaniraniRad;
use App\Models\Prskanje;
use App\Models\Rad;
use App\Models\Stroj;
use App\Models\StrojRok;
use App\Models\SzbSredstvo;
use App\Models\User;
use App\Support\Kalendar;
use App\Support\KalendarObavijesti;
use App\Support\Prognoza;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class KalendarTest extends TestCase
{
    use RefreshDatabase;

    private function kultura(User $u, string $naziv = 'Kukuruz', array $parcela = []): Kultura
    {
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => (string) random_int(100000, 999999), 'povrsina_ha' => 3] + $parcela);

        return Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => $naziv, 'posadjena_povrsina_ha' => 2, 'godina' => date('Y')]);
    }

    public function test_kalendar_skuplja_zapise_rokove_i_planove_samo_svog_korisnika(): void
    {
        $u = User::factory()->create();
        $k = $this->kultura($u);
        $danas = now()->toDateString();
        Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum_tretiranja' => $danas, 'trgovacki_naziv_sredstva' => 'Sredstvo X', 'kolicina_sredstva_l_ha' => 1.5]);
        Gnojidba::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => $danas, 'tip_gnojiva' => 'KAN', 'kolicina_kg_ha' => 200]);
        Berba::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => $danas, 'kolicina_kg' => 1000, 'jedinica' => 'kg']);
        Rad::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => $danas, 'vrsta' => 'Oranje', 'izvedba' => 'vlastito']);
        $s = Stroj::create(['user_id' => $u->id, 'vrsta' => 'traktor', 'naziv' => 'IMT 539']);
        StrojRok::create(['user_id' => $u->id, 'stroj_id' => $s->id, 'vrsta' => 'registracija', 'datum_isteka' => $danas, 'podsjetnik_dana' => 14]);
        PlaniraniRad::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => now()->subDay()->toDateString(), 'vrsta' => 'gnojidba', 'opis' => 'UREA']);

        $drugi = User::factory()->create();
        Prskanje::create(['user_id' => $drugi->id, 'kultura_id' => $this->kultura($drugi)->id, 'datum_tretiranja' => $danas, 'kolicina_sredstva_l_ha' => 1]);

        $this->actingAs($u);
        $d = Kalendar::dogadaji($u, now()->startOfMonth()->subDays(7), now()->endOfMonth()->addDays(7));

        $this->assertEqualsCanonicalizing(['prskanje', 'gnojidba', 'berba', 'rad', 'mehanizacija', 'plan'], $d->pluck('grupa')->unique()->values()->all());
        $this->assertSame(1, $d->where('grupa', 'prskanje')->count());
        $this->assertSame('kasni', $d->firstWhere('grupa', 'plan')['stanje']);

        // filter po vrsti
        $this->assertSame(['gnojidba'], Kalendar::dogadaji($u, now()->subDays(3), now()->addDays(3), null, ['gnojidba'])->pluck('grupa')->unique()->values()->all());

        $this->get(route('kalendar'))->assertOk()->assertSee('Kalendar')->assertSee('Sredstvo X')->assertSee('IMT 539');
    }

    public function test_karenca_iz_spremljenih_detalja_fis(): void
    {
        $u = User::factory()->create();
        $k = $this->kultura($u, 'Pšenica');
        SzbSredstvo::forceCreate(['fis_id' => 1, 'naziv' => 'Fungicid Y', 'detalji' => [['kultura' => 'Pšenica', 'karenca_dana' => 10, 'karenca' => '10 dana']], 'detalji_at' => now()]);
        Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum_tretiranja' => now()->toDateString(), 'trgovacki_naziv_sredstva' => 'Fungicid Y', 'kolicina_sredstva_l_ha' => 1]);

        $karence = Kalendar::karence($u, now()->startOfMonth(), now()->addMonth());
        $this->assertCount(1, $karence);
        $this->assertSame(now()->addDays(10)->toDateString(), $karence[0]['do']->toDateString());

        $kraj = Kalendar::dogadaji($u, now(), now()->addDays(20), null, ['karenca']);
        $this->assertSame(now()->addDays(10)->toDateString(), $kraj->first()['datum']);
    }

    public function test_planiranje_i_odradjeno_upisom_prskanja_zatvara_plan(): void
    {
        $u = User::factory()->create();
        $k = $this->kultura($u);
        $this->actingAs($u);

        Livewire::test(KalendarPregled::class)
            ->call('noviPlan', now()->addDays(2)->toDateString())
            ->set('plan.vrsta', 'prskanje')
            ->call('spremiPlan')->assertHasErrors('plan.kultura_id')
            ->set('plan.kultura_id', $k->id)->set('plan.opis', 'Sredstvo Z')
            ->call('spremiPlan')->assertHasNoErrors();

        $plan = PlaniraniRad::firstOrFail();
        $this->assertSame('prskanje', $plan->vrsta);
        $this->assertNull($plan->obavljeno_at);

        // forma prskanja iz plana: kultura i sredstvo popunjeni, datum najkasnije danas
        $t = Livewire::withQueryParams(['plan' => $plan->id])->test(PrskanjeTable::class)
            ->assertSet('showForm', true)->assertSet('planId', $plan->id)
            ->assertSet('form.kultura_id', $k->id)->assertSet('form.trgovacki_naziv_sredstva', 'Sredstvo Z')
            ->assertSet('form.datum_tretiranja', now()->toDateString());
        $t->set('form.kolicina_sredstva_l_ha', 2)->call('saveNew')->assertHasNoErrors();

        $plan->refresh();
        $this->assertNotNull($plan->obavljeno_at);
        $this->assertSame('prskanje', $plan->zapis_vrsta);
        $this->assertSame(Prskanje::first()->id, $plan->zapis_id);
    }

    public function test_datum_iz_kalendara_i_tudji_plan_se_ne_preuzima(): void
    {
        $u = User::factory()->create();
        $k = $this->kultura($u);
        $drugi = User::factory()->create();
        $tudji = PlaniraniRad::create(['user_id' => $drugi->id, 'kultura_id' => $this->kultura($drugi)->id, 'datum' => now()->toDateString(), 'vrsta' => 'gnojidba', 'opis' => 'Tajno']);
        $this->actingAs($u);

        Livewire::withQueryParams(['datum' => '2026-03-15'])->test(GnojidbaTable::class)
            ->assertSet('showForm', true)->assertSet('form.datum', '2026-03-15');
        Livewire::withQueryParams(['datum' => 'nije-datum'])->test(GnojidbaTable::class)->assertSet('showForm', false);
        Livewire::withQueryParams(['plan' => $tudji->id])->test(GnojidbaTable::class)
            ->assertSet('planId', null)->assertSet('form.tip_gnojiva', '');

        // ostali rad: vrsta iz plana
        $plan = PlaniraniRad::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => now()->toDateString(), 'vrsta' => 'rad', 'opis' => 'Rezidba']);
        Livewire::withQueryParams(['plan' => $plan->id])->test(RadoviTable::class)
            ->assertSet('showForm', true)->assertSet('form.vrsta', 'Rezidba')
            ->call('spremi')->assertHasNoErrors();
        $this->assertNotNull($plan->fresh()->obavljeno_at);
    }

    public function test_ostalo_se_oznacava_obavljenim_a_tudji_plan_ne_moze_se_mijenjati(): void
    {
        $u = User::factory()->create();
        $plan = PlaniraniRad::create(['user_id' => $u->id, 'datum' => now()->toDateString(), 'vrsta' => 'ostalo', 'opis' => 'Kupiti mreže']);
        $drugi = User::factory()->create();
        $tudji = PlaniraniRad::create(['user_id' => $drugi->id, 'datum' => now()->toDateString(), 'vrsta' => 'ostalo', 'opis' => 'X']);
        $this->actingAs($u);

        Livewire::test(KalendarPregled::class)->call('obavljeno', $plan->id);
        $this->assertNotNull($plan->fresh()->obavljeno_at);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Livewire::test(KalendarPregled::class)->call('obrisiPlan', $tudji->id);
    }

    public function test_prognoza_i_vecernja_obavijest_o_prskanju(): void
    {
        $sutra = now()->addDay()->toDateString();
        Http::fake(['api.open-meteo.com/*' => Http::response(['daily' => [
            'time' => [now()->toDateString(), $sutra],
            'weather_code' => [61, 0], 'temperature_2m_max' => [12, 18], 'temperature_2m_min' => [4, 6],
            'precipitation_sum' => [5, 0], 'precipitation_probability_max' => [90, 5], 'wind_speed_10m_max' => [25, 8],
        ]])]);

        $u = User::factory()->create(['push_obavijesti' => true]);
        $k = $this->kultura($u, 'Kukuruz', ['lat' => 45.55, 'lng' => 18.69]);

        $p = Prognoza::za($u);
        $this->assertFalse($p[now()->toDateString()]['prskanje']);
        $this->assertStringContainsString('vjetar', $p[now()->toDateString()]['razlog']);
        $this->assertTrue($p[$sutra]['prskanje']);

        // bez plana ili preporuke nema obavijesti; s planom prskanja korisnik je kandidat (bez uređaja se ništa ne šalje)
        $this->assertSame(0, KalendarObavijesti::vecer());
        PlaniraniRad::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => $sutra, 'vrsta' => 'prskanje']);
        $this->assertSame(0, KalendarObavijesti::vecer());
        $this->assertSame(0, KalendarObavijesti::jutro());
    }
}
