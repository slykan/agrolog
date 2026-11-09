<?php

namespace Tests\Feature;

use App\Mail\PreporukaMail;
use App\Models\Prezentacija;
use App\Models\Proizvod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TrznicaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function gospodarstvo(User $u, bool $odobreno = true): Prezentacija
    {
        return Prezentacija::create([
            'user_id' => $u->id, 'vidljivo' => true, 'odobreno_at' => $odobreno ? now() : null, 'slug' => 'opg-pcelica-' . $u->id,
            'naziv' => 'OPG Pčelica', 'mjesto' => 'Bilje', 'zupanija' => 'Osječko-baranjska', 'opis' => str_repeat('Med iz Baranje. ', 5),
            'oznake' => ['Med'], 'telefon' => '098 111 2222', 'adresa' => 'Kralja Zvonimira 1, Bilje', 'lat' => 45.6, 'lng' => 18.74,
        ]);
    }

    public function test_dodavanje_proizvoda_i_prikaz_s_prodavacem(): void
    {
        $u = User::factory()->create();
        $p = $this->gospodarstvo($u);

        $this->actingAs($u)->post(route('trznica.moji.spremi'), ['naziv' => 'Bagremov med', 'vrsta' => 'Med', 'cijena' => '9,50', 'jedinica' => 'staklenka', 'dostupno' => '1'])
            ->assertRedirect(route('trznica.moji'))->assertSessionHasNoErrors();
        $pr = Proizvod::first();
        $this->assertEquals(9.5, (float) $pr->cijena);
        auth()->logout();

        $this->get(route('trznica'))->assertOk()->assertSee('Bagremov med')->assertSee('9,50 € / staklenka')->assertSee('OPG Pčelica');
        $oglas = $this->get(route('trznica.proizvod', $pr))->assertOk()
            ->assertSee('Prodaje')->assertSee('OPG Pčelica')->assertSee('Kontakt prodavača')->assertSee('Kralja Zvonimira 1, Bilje')
            ->assertSee('Svi oglasi ovog prodavača (1)')->assertSee(route('trznica', ['prodavac' => $p->slug]), false);
        $this->assertStringNotContainsString('111 2222', $oglas->getContent());   // telefon samo na klik

        // svi oglasi jednog prodavača
        $drugi = $this->gospodarstvo(User::factory()->create());
        Proizvod::create(['prezentacija_id' => $drugi->id, 'naziv' => 'Lipov med', 'vrsta' => 'Med', 'cijena' => 8, 'jedinica' => 'kg']);
        $this->get(route('trznica', ['prodavac' => $p->slug]))->assertOk()->assertSee('Bagremov med')->assertDontSee('Lipov med')->assertSee('Svi oglasi ovog prodavača');
        $this->get(route('gospodarstvo', $p->slug))->assertSee('Proizvodi')->assertSee('Bagremov med');
        $this->get('/sitemap.xml')->assertSee(route('trznica.proizvod', $pr), false);
    }

    public function test_nije_javno_bez_odobrenja_ili_kad_je_sakriveno(): void
    {
        $u = User::factory()->create();
        $p = $this->gospodarstvo($u, odobreno: false);
        $pr = Proizvod::create(['prezentacija_id' => $p->id, 'naziv' => 'Sir', 'vrsta' => 'Mlijeko i sir', 'cijena' => 12, 'jedinica' => 'kg']);
        $this->get(route('trznica.proizvod', $pr))->assertNotFound();
        $this->get(route('trznica'))->assertDontSee('>Sir<', false);

        $p->update(['odobreno_at' => now()]);
        $this->get(route('trznica.proizvod', $pr))->assertOk();
        $this->actingAs($u)->post(route('trznica.moji.dostupnost', $pr));
        auth()->logout();
        $this->get(route('trznica.proizvod', $pr))->assertNotFound();
    }

    public function test_bez_prezentacije_nema_oglasa_i_tudji_se_ne_dira(): void
    {
        $bez = User::factory()->create();
        $this->actingAs($bez)->get(route('trznica.moji'))->assertOk()->assertSee('Za Tržnicu prvo predstavite gospodarstvo');
        $this->actingAs($bez)->post(route('trznica.moji.spremi'), ['naziv' => 'X', 'vrsta' => 'Med', 'cijena' => 1, 'jedinica' => 'kg'])->assertNotFound();

        $vlasnik = User::factory()->create();
        $pr = Proizvod::create(['prezentacija_id' => $this->gospodarstvo($vlasnik)->id, 'naziv' => 'Jaja', 'vrsta' => 'Jaja', 'cijena' => 3, 'jedinica' => 'kom']);
        $uljez = User::factory()->create();
        $this->gospodarstvo($uljez);
        $this->actingAs($uljez)->post(route('trznica.moji.azuriraj', $pr), ['naziv' => 'Hakirano', 'vrsta' => 'Jaja', 'cijena' => 0, 'jedinica' => 'kom'])->assertForbidden();
        $this->actingAs($uljez)->delete(route('trznica.moji.obrisi', $pr))->assertForbidden();
        $this->assertSame('Jaja', $pr->fresh()->naziv);
    }

    public function test_kvacica_na_registraciji_vodi_na_obrazac_jednom(): void
    {
        \Illuminate\Support\Facades\Http::fake(['challenges.cloudflare.com/*' => \Illuminate\Support\Facades\Http::response(['success' => true])]);
        $odgovor = $this->post(route('register'), [
            'naziv_gospodarstva' => 'OPG Med', 'name' => 'Ivo Ivić', 'email' => 'ivo@example.com', 'password' => 'Lozinka123!', 'password_confirmation' => 'Lozinka123!',
            'zeli_prezentaciju' => '1', 'cf-turnstile-response' => 'test',
        ]);
        $u = User::where('email', 'ivo@example.com')->first();
        $this->assertNotNull($u, json_encode(session('errors')?->getMessages()));
        $this->assertTrue($u->zeli_prezentaciju);
        $odgovor->assertRedirect(route('prezentacija.uvod', absolute: false));

        $this->get(route('prezentacija.uvod'))->assertOk()->assertSee('Predstavite gospodarstvo')->assertSee('name="natrag"', false);
        $this->assertSame(route('dashboard', absolute: false), $u->fresh()->pocetnaStranica());   // nudi se samo jednom
        $this->get(route('dashboard'))->assertSee('Dovršite predstavljanje gospodarstva');
        $this->post(route('profile.prezentacija.odustani'));
        $this->assertFalse($u->fresh()->zeli_prezentaciju);
    }

    public function test_admin_poziva_na_popis_jednom(): void
    {
        config(['app.admin_emails' => ['admin@agro-log.app']]);
        $admin = User::factory()->create(['email' => 'admin@agro-log.app']);
        $u = User::factory()->create();
        $this->actingAs($admin)->post(route('admin.korisnik.pozovi-popis', $u))->assertRedirect();
        Mail::assertSent(PreporukaMail::class, fn ($m) => $m->hasTo($u->email) && str_contains($m->predmet, 'Predstavite'));
        $this->assertNotNull($u->fresh()->prezentacija_pozvan_at);
        $this->actingAs($admin)->post(route('admin.korisnik.pozovi-popis', $u));
        Mail::assertSentCount(1);
        $this->actingAs(User::factory()->create())->post(route('admin.korisnik.pozovi-popis', $u))->assertForbidden();
    }
}
