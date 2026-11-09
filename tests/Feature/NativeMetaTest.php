<?php

namespace Tests\Feature;

use App\Models\Pristup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Android aplikacija čita oznaku agro-native-push na svakoj stranici; bez nje zaboravi korisnika i odbacuje push. */
class NativeMetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_javne_stranice_imaju_oznaku_prijavljenog_korisnika(): void
    {
        $u = User::factory()->create(['push_obavijesti' => true]);
        foreach (['trznica', 'gospodarstva', 'docs', 'novosti', 'home'] as $ruta) {
            $this->actingAs($u)->get(route($ruta))->assertOk()
                ->assertSee('name="agro-native-push" data-user="' . $u->id . '" data-enabled="1"', false)
                ->assertSee('name="csrf-token"', false);
        }
        $this->get(route('dashboard'))->assertSee('data-user="' . $u->id . '"', false);
    }

    public function test_u_aplikaciji_naslovnica_vodi_prijavljenog_na_pocetnu(): void
    {
        $u = User::factory()->create();
        $app = ['User-Agent' => 'Mozilla/5.0 (Linux; Android 14) AgroLogNative/0.5.0 Android'];
        $this->actingAs($u)->get('/', $app)->assertRedirect(route('dashboard'));
        $this->actingAs($u)->get('/')->assertOk();                 // preglednik: naslovnica
        auth()->logout();
        $this->get('/', $app)->assertOk();                        // gost u aplikaciji: naslovnica
    }

    public function test_gost_ima_praznu_oznaku(): void
    {
        $this->get(route('trznica'))->assertOk()->assertSee('name="agro-native-push" data-user="" data-enabled="0"', false);
        $this->get(route('login'))->assertOk()->assertSee('data-user=""', false);
    }

    public function test_u_klijentovom_gospodarstvu_oznaka_nosi_savjetnika(): void
    {
        $s = User::factory()->create(['je_savjetnik' => true, 'push_obavijesti' => true]);
        $v = User::factory()->create();
        $p = Pristup::create(['vlasnik_id' => $v->id, 'email' => $s->email, 'savjetnik_id' => $s->id, 'prava' => 'pregled', 'token' => Pristup::noviToken(), 'status' => 'prihvaceno', 'prihvaceno_at' => now()]);
        $this->actingAs($s)->withSession(['klijent_pristup_id' => $p->id, 'klijent_savjetnik_id' => $s->id])
            ->get(route('dashboard'))->assertOk()
            ->assertSee('data-user="' . $s->id . '"', false)->assertDontSee('data-user="' . $v->id . '"', false);
    }
}
