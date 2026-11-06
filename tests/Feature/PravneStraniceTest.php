<?php

namespace Tests\Feature;

use App\Models\Parcela;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PravneStraniceTest extends TestCase
{
    use RefreshDatabase;

    public function test_stranice_su_javne_i_povezane(): void
    {
        foreach (['/uvjeti-koristenja' => 'Uvjeti korištenja', '/politika-privatnosti' => 'Pravila privatnosti', '/kolacici' => 'Pravila o kolačićima', '/brisanje-racuna' => 'Brisanje računa'] as $put => $naslov) {
            $this->get($put)->assertOk()->assertSee('<h1', false)->assertSee($naslov)->assertSee('06.11.2026.');
        }
        $this->get('/uvjeti-koristenja')->assertSee(route('brisanje'))->assertSee(route('kolacici'));
        $this->get('/register')->assertOk()->assertSee(route('uvjeti'))->assertSee(route('privacy'));
        $this->get('/sitemap.xml')->assertOk()->assertSee(route('uvjeti'))->assertSee(route('brisanje'));
    }

    public function test_privatnost_ne_tvrdi_da_nema_oglasa(): void
    {
        $this->get('/politika-privatnosti')->assertDontSee('Ne koristimo kolačiće za praćenje')->assertSee('Oglasni kolačići');
    }

    public function test_oglasi_se_ne_ucitavaju_bez_privole(): void
    {
        $r = $this->get(route('vodici'));   // popis, bez oglasa
        $r->assertOk()->assertDontSee('window.agroOglasi = function', false);

        $slug = \App\Http\Controllers\SadrzajController::vodici()[0]['slug'];
        $s = $this->get(route('vodic', $slug));
        $s->assertOk()->assertSee('window.agroOglasi = function', false)->assertSee("localStorage.getItem('cookie_consent') === 'accepted'", false);
        // skripta se ne učitava izravno iz HTML-a
        $s->assertDontSee('<script async src="https://pagead2', false);
    }

    public function test_google_korisnik_moze_obrisati_racun_upisom_maila(): void
    {
        $u = User::factory()->create(['email' => 'ivo@example.com', 'google_id' => 'g-1', 'password' => Hash::make('nepoznata-nasumicna')]);
        Parcela::create(['user_id' => $u->id, 'arkod_broj' => '1', 'povrsina_ha' => 1]);

        $this->actingAs($u)->get('/profile')->assertSee('potvrda_email', false)->assertSee(route('brisanje'));

        // pogrešan mail ne briše
        $this->actingAs($u)->from('/profile')->delete('/profile', ['potvrda_email' => 'drugi@example.com'])->assertSessionHasErrorsIn('userDeletion', 'potvrda_email');
        $this->assertNotNull($u->fresh());

        $this->actingAs($u)->delete('/profile', ['potvrda_email' => 'IVO@example.com'])->assertRedirect('/');
        $this->assertNull($u->fresh());
        $this->assertSame(0, Parcela::count());
    }

    public function test_obican_korisnik_ne_moze_preskociti_lozinku_mailom(): void
    {
        $u = User::factory()->create(['email' => 'ana@example.com']);
        $this->actingAs($u)->from('/profile')->delete('/profile', ['potvrda_email' => 'ana@example.com'])->assertSessionHasErrorsIn('userDeletion', 'password');
        $this->assertNotNull($u->fresh());
        $this->actingAs($u)->get('/profile')->assertDontSee('potvrda_email', false);
    }
}
