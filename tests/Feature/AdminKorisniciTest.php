<?php

namespace Tests\Feature;

use App\Models\Gnojidba;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\Prskanje;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminKorisniciTest extends TestCase
{
    use RefreshDatabase;

    private function farmer(): User
    {
        $u = User::factory()->create(['name' => 'Ivo Ratar', 'naziv_gospodarstva' => 'OPG Ratar', 'mipg' => '123456']);
        $p = Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1708275', 'povrsina_ha' => 2.5]);
        $k = Kultura::create(['user_id' => $u->id, 'parcela_id' => $p->id, 'naziv' => 'Pšenica', 'posadjena_povrsina_ha' => 2.5, 'godina' => 2026]);
        Prskanje::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum_tretiranja' => '2026-04-10', 'trgovacki_naziv_sredstva' => 'Folicur', 'kolicina_sredstva_l_ha' => 1]);
        Gnojidba::create(['user_id' => $u->id, 'kultura_id' => $k->id, 'datum' => '2026-03-01', 'tip_gnojiva' => 'KAN', 'kolicina_kg_ha' => 200]);

        return $u;
    }

    public function test_admin_vidi_popis_i_detalje(): void
    {
        $admin = User::factory()->create(['email' => 'slynetwork@gmail.com']);
        $u = $this->farmer();

        $this->actingAs($admin)->get('/admin/korisnici')
            ->assertOk()
            ->assertSee('Ivo Ratar')
            ->assertSee('OPG Ratar')
            ->assertSee('2,50 ha');

        $this->actingAs($admin)->get('/admin/korisnici?q=Ratar&sort=zapisi&dir=desc')->assertOk()->assertSee('Ivo Ratar');
        $this->actingAs($admin)->get('/admin/korisnici?q=nepostoji')->assertOk()->assertDontSee('Ivo Ratar');

        $this->actingAs($admin)->get("/admin/korisnici/{$u->id}")
            ->assertOk()
            ->assertSee('ARKOD 1708275')
            ->assertSee('Folicur')
            ->assertSee('KAN');

        // stavka u izborniku samo adminu
        $this->actingAs($admin)->get('/dashboard')->assertSee(route('admin.korisnici'));
    }

    public function test_obican_korisnik_nema_pristup(): void
    {
        $u = $this->farmer();

        $this->actingAs($u)->get('/admin/korisnici')->assertForbidden();
        $this->actingAs($u)->get("/admin/korisnici/{$u->id}")->assertForbidden();
        $this->actingAs($u)->get('/dashboard')->assertDontSee(route('admin.korisnici'));
    }

    public function test_gost_ide_na_prijavu(): void
    {
        $this->get('/admin/korisnici')->assertRedirect('/login');
    }

    public function test_aktivnost_se_biljezi(): void
    {
        $u = User::factory()->create();
        $this->assertNull($u->last_seen_at);

        $this->actingAs($u)->get('/dashboard')->assertOk();
        $this->assertNotNull($u->fresh()->last_seen_at);
    }
}
