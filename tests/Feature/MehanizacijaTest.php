<?php

namespace Tests\Feature;

use App\Livewire\MehanizacijaManager;
use App\Mail\PodsjetnikRokovaMail;
use App\Models\Stroj;
use App\Models\StrojRok;
use App\Models\StrojServis;
use App\Models\User;
use App\Support\Mehanizacija;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class MehanizacijaTest extends TestCase
{
    use RefreshDatabase;

    public function test_stranica_i_prazno_stanje(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->get('/mehanizacija')->assertOk()->assertSee('Dodaj stroj ili vozilo');
        $this->actingAs($u)->get('/dashboard')->assertSee(route('mehanizacija'), false);
    }

    public function test_novi_stroj_s_rokovima_i_obnova(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u);

        Livewire::test(MehanizacijaManager::class)
            ->call('noviStroj')
            ->set('f.vrsta', 'traktor')->set('f.naziv', 'John Deere 6120M')->set('f.registracija', 'os 123-ab')
            ->set('f.rokovi.registracija', '2026-12-01')->set('f.rokovi.osiguranje', '2027-03-15')
            ->call('spremiStroj')->assertHasNoErrors();

        $s = Stroj::first();
        $this->assertSame('OS 123-AB', $s->registracija);
        $this->assertSame(2, $s->rokovi()->count());

        $reg = StrojRok::where('vrsta', 'registracija')->first();
        Livewire::test(MehanizacijaManager::class)
            ->call('obnovi', $reg->id)
            ->assertSet('f.datum_isteka', '2027-12-01')
            ->call('spremiRok')->assertHasNoErrors();

        $this->assertNotNull($reg->fresh()->obnovljen_at);
        $this->assertSame('2027-12-01', StrojRok::where('vrsta', 'registracija')->whereNull('obnovljen_at')->first()->datum_isteka->format('Y-m-d'));
    }

    public function test_servis_pomakne_rok_servisa(): void
    {
        $u = User::factory()->create();
        $s = Stroj::create(['user_id' => $u->id, 'vrsta' => 'traktor', 'naziv' => 'IMT 539']);
        StrojRok::create(['user_id' => $u->id, 'stroj_id' => $s->id, 'vrsta' => 'servis', 'datum_isteka' => now()->addDays(5)]);
        $this->actingAs($u);

        Livewire::test(MehanizacijaManager::class)
            ->call('noviServis', $s->id)->assertSet('f.pomakni_rok', true)
            ->set('f.opis', 'ulje i filteri')->set('f.radni_sati', '4250')->set('f.trosak', '180')
            ->call('spremiServis')->assertHasNoErrors();

        $this->assertSame(1, StrojServis::count());
        $novi = StrojRok::whereNull('obnovljen_at')->first();
        $this->assertSame(now()->addYear()->format('Y-m-d'), $novi->datum_isteka->format('Y-m-d'));
    }

    public function test_podsjetnici_mailom(): void
    {
        Mail::fake();
        $u = User::factory()->create();
        $s = Stroj::create(['user_id' => $u->id, 'vrsta' => 'prskalica', 'naziv' => 'Agromehanika 1000']);
        $uskoro = StrojRok::create(['user_id' => $u->id, 'stroj_id' => $s->id, 'vrsta' => 'pregled_prskalice', 'datum_isteka' => now()->addDays(20), 'podsjetnik_dana' => 30]);
        $daleko = StrojRok::create(['user_id' => $u->id, 'stroj_id' => $s->id, 'vrsta' => 'servis', 'datum_isteka' => now()->addDays(100), 'podsjetnik_dana' => 30]);
        $istekao = StrojRok::create(['user_id' => $u->id, 'stroj_id' => $s->id, 'vrsta' => 'ostalo', 'naziv' => 'Baždarenje', 'datum_isteka' => now()->subDays(2), 'podsjetnik_dana' => 30, 'podsjetnik_poslan_at' => now()->subDays(30)]);
        StrojRok::create(['user_id' => $u->id, 'stroj_id' => $s->id, 'vrsta' => 'kasko', 'datum_isteka' => now()->addDays(3), 'podsjetnik_dana' => 0]);   // bez podsjetnika

        $this->assertSame(1, Mehanizacija::posaljiPodsjetnike());
        Mail::assertSent(PodsjetnikRokovaMail::class, fn ($m) => $m->hasTo($u->email)
            && $m->uskoro->pluck('id')->all() === [$uskoro->id] && $m->istekli->pluck('id')->all() === [$istekao->id]);
        $this->assertNotNull($uskoro->fresh()->podsjetnik_poslan_at);
        $this->assertNull($daleko->fresh()->podsjetnik_poslan_at);

        // drugi put se ne šalje ništa
        $this->assertSame(0, Mehanizacija::posaljiPodsjetnike());
        // mail se iscrta
        $html = (new PodsjetnikRokovaMail($u, collect([$uskoro->fresh()]), collect()))->render();
        $this->assertStringContainsString('Agromehanika 1000', $html);
    }

    public function test_tudji_stroj(): void
    {
        $u = User::factory()->create();
        $drugi = User::factory()->create();
        $s = Stroj::create(['user_id' => $u->id, 'vrsta' => 'traktor', 'naziv' => 'Tuđi']);
        $this->actingAs($drugi);
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Livewire::test(MehanizacijaManager::class)->call('obrisiStroj', $s->id);
    }
}
