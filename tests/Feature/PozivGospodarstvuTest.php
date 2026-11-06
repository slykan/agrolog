<?php

namespace Tests\Feature;

use App\Mail\PozivGospodarstvuMail;
use App\Mail\PozivPrihvacenMail;
use App\Models\PozivGospodarstvu;
use App\Models\Pristup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PozivGospodarstvuTest extends TestCase
{
    use RefreshDatabase;

    private function savjetnik(array $podaci = []): User
    {
        return User::factory()->create(['name' => 'Ana Agronom', 'email' => 'ana@zadruga.hr', 'je_savjetnik' => true] + $podaci);
    }

    private function poziv(User $savjetnik, string $email = 'horvat@example.com', string $status = 'poslano'): PozivGospodarstvu
    {
        return PozivGospodarstvu::create(['savjetnik_id' => $savjetnik->id, 'email' => $email, 'token' => PozivGospodarstvu::noviToken(), 'status' => $status, 'poslano_at' => now()]);
    }

    public function test_registracija_s_kvacicom_vodi_na_klijente_s_pozivom(): void
    {
        Mail::fake();
        Http::fake(['*' => Http::response(['success' => true])]);

        $this->post('/register', [
            'naziv_gospodarstva' => 'Agro savjet d.o.o.', 'name' => 'Ana Agronom', 'email' => 'ana@zadruga.hr',
            'password' => 'lozinka-duga-123', 'password_confirmation' => 'lozinka-duga-123', 'je_savjetnik' => '1',
        ])->assertRedirect(route('klijenti', absolute: false));

        $this->assertTrue(User::where('email', 'ana@zadruga.hr')->first()->je_savjetnik);
        $this->get('/klijenti')->assertOk()->assertSee('Pozovite svoje poljoprivrednike')->assertSee('E-mail poljoprivrednika');
    }

    public function test_bez_kvacice_nema_poziva_i_slanje_je_zabranjeno(): void
    {
        Mail::fake();
        $korisnik = User::factory()->create();

        $this->actingAs($korisnik)->get('/klijenti')->assertOk()->assertDontSee('E-mail poljoprivrednika');
        $this->actingAs($korisnik)->post(route('klijenti.pozivi.posalji'), ['email' => 'x@example.com'])->assertForbidden();
        Mail::assertNothingSent();
        $this->assertSame(0, PozivGospodarstvu::count());
    }

    public function test_kvacica_u_profilu_se_sprema_i_gasi(): void
    {
        $k = User::factory()->create(['naziv_gospodarstva' => 'OPG']);
        $polja = ['name' => $k->name, 'email' => $k->email, 'naziv_gospodarstva' => 'OPG'];

        $this->actingAs($k)->patch('/profile', $polja + ['je_savjetnik' => '1'])->assertRedirect();
        $this->assertTrue($k->fresh()->je_savjetnik);
        $this->actingAs($k)->patch('/profile', $polja + ['je_savjetnik' => '0'])->assertRedirect();
        $this->assertFalse($k->fresh()->je_savjetnik);
    }

    public function test_poziv_sam_po_sebi_ne_daje_pristup(): void
    {
        Mail::fake();
        $s = $this->savjetnik();

        $this->actingAs($s)->post(route('klijenti.pozivi.posalji'), ['email' => 'Horvat@Example.com'])->assertRedirect(route('klijenti'));

        $p = PozivGospodarstvu::firstOrFail();
        $this->assertSame('horvat@example.com', $p->email);
        $this->assertSame(0, Pristup::count());
        Mail::assertSent(PozivGospodarstvuMail::class, fn ($m) => $m->hasTo('horvat@example.com'));

        $html = (new PozivGospodarstvuMail($p->load('savjetnik')))->render();
        $this->assertStringContainsString('Ana Agronom vas poziva na suradnju', $html);
        $this->assertStringContainsString(route('poziv.gospodarstvo', $p->token), $html);
        $this->actingAs($s)->get('/klijenti')->assertSee('horvat@example.com')->assertSee('čeka odgovor');
    }

    public function test_prihvacanje_trazi_izbor_prava_i_stvara_pristup(): void
    {
        Mail::fake();
        $s = $this->savjetnik();
        $vlasnik = User::factory()->create(['naziv_gospodarstva' => 'OPG Horvat', 'email' => 'horvat@example.com']);
        $p = $this->poziv($s);

        $this->actingAs($vlasnik)->get(route('poziv.gospodarstvo', $p->token))->assertOk()->assertSee('Prihvati poziv')->assertSee('OPG Horvat');
        $this->actingAs($vlasnik)->post(route('poziv.gospodarstvo.prihvati', $p->token))->assertSessionHasErrors('prava');
        $this->assertSame(0, Pristup::count());

        $this->actingAs($vlasnik)->post(route('poziv.gospodarstvo.prihvati', $p->token), ['prava' => 'pregled'])->assertRedirect(route('dashboard'));

        $pristup = Pristup::firstOrFail();
        $this->assertSame([$vlasnik->id, $s->id, 'pregled', 'prihvaceno'], [$pristup->vlasnik_id, $pristup->savjetnik_id, $pristup->prava, $pristup->status]);
        $this->assertSame('prihvaceno', $p->fresh()->status);
        Mail::assertSent(PozivPrihvacenMail::class, fn ($m) => $m->hasTo('ana@zadruga.hr'));
        $this->assertStringContainsString('OPG Horvat', (new PozivPrihvacenMail($pristup->load('vlasnik', 'savjetnik')))->render());

        // gospodarstvo je na popisu, a poziv više ne stoji među poslanima
        $this->actingAs($s)->get('/klijenti')->assertSee('OPG Horvat')->assertDontSee('čeka odgovor');
    }

    public function test_savjetnik_ne_moze_sam_prihvatiti_ni_nacrtati_pristup(): void
    {
        $s = $this->savjetnik();
        $p = $this->poziv($s);

        $this->actingAs($s)->get(route('poziv.gospodarstvo', $p->token))->assertOk()->assertDontSee('Prihvati poziv');
        $this->actingAs($s)->post(route('poziv.gospodarstvo.prihvati', $p->token), ['prava' => 'unos'])->assertForbidden();
        $this->assertSame(0, Pristup::count());
    }

    public function test_prihvacanje_dok_savjetnik_radi_kod_klijenta_ne_otvara_klijentove_podatke(): void
    {
        $treci = $this->savjetnik();
        $ana = User::factory()->create(['email' => 'druga@zadruga.hr', 'je_savjetnik' => true]);
        $klijent = User::factory()->create(['naziv_gospodarstva' => 'OPG Klijent']);
        $pristup = Pristup::create(['vlasnik_id' => $klijent->id, 'email' => $ana->email, 'savjetnik_id' => $ana->id, 'prava' => 'unos', 'token' => Pristup::noviToken(), 'status' => 'prihvaceno', 'prihvaceno_at' => now()]);
        $p = $this->poziv($treci, $ana->email);

        $this->actingAs($ana)->post(route('klijenti.otvori', $pristup));
        $this->get(route('poziv.gospodarstvo', $p->token))->assertOk()->assertSee('Natrag na svoje')->assertDontSee('Prihvati poziv');
        $this->post(route('poziv.gospodarstvo.prihvati', $p->token), ['prava' => 'unos'])->assertRedirect(route('dashboard'));

        $this->assertSame('poslano', $p->fresh()->status);
        $this->assertFalse(Pristup::where('savjetnik_id', $treci->id)->exists());
    }

    public function test_odbijanje_radi_bez_racuna_i_blokira_novi_poziv(): void
    {
        Mail::fake();
        $s = $this->savjetnik();
        $p = $this->poziv($s);

        $this->post(route('poziv.gospodarstvo.odbij', $p->token))->assertRedirect(route('poziv.gospodarstvo', $p->token));
        $this->assertSame('odbijeno', $p->fresh()->status);
        $this->get(route('poziv.gospodarstvo', $p->token))->assertOk()->assertSee('Poziv je odbijen');

        $this->actingAs($s)->post(route('klijenti.pozivi.posalji'), ['email' => 'horvat@example.com'])->assertSessionHasErrors('email', null, 'pozivGospodarstvu');
        $this->actingAs($s)->post(route('klijenti.pozivi.ponovno', $p))->assertRedirect(route('klijenti'));
        Mail::assertNothingSent();

        // i odbijeni poziv se više ne može prihvatiti
        $vlasnik = User::factory()->create();
        $this->actingAs($vlasnik)->post(route('poziv.gospodarstvo.prihvati', $p->token), ['prava' => 'unos'])->assertNotFound();
    }

    public function test_povuceni_poziv_ne_vrijedi(): void
    {
        $s = $this->savjetnik();
        $p = $this->poziv($s);
        $stari = $p->token;

        $this->actingAs($s)->delete(route('klijenti.pozivi.povuci', $p))->assertRedirect(route('klijenti'));

        $this->assertSame('povuceno', $p->fresh()->status);
        $this->get(route('poziv.gospodarstvo', $stari))->assertOk()->assertSee('Poziv više ne vrijedi');
    }

    public function test_tudji_poziv_se_ne_moze_ni_povuci_ni_ponovno_poslati(): void
    {
        Mail::fake();
        $p = $this->poziv($this->savjetnik());
        $p->update(['poslano_at' => now()->subDays(2)]);
        $drugi = User::factory()->create(['je_savjetnik' => true]);

        $this->actingAs($drugi)->delete(route('klijenti.pozivi.povuci', $p))->assertNotFound();
        $this->actingAs($drugi)->post(route('klijenti.pozivi.ponovno', $p))->assertNotFound();
        $this->assertSame('poslano', $p->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_ogranicenja_slanja(): void
    {
        Mail::fake();
        $s = $this->savjetnik();
        $p = $this->poziv($s);

        // isti dan se ne šalje ponovno, a nakon dana da, najviše tri puta
        $this->actingAs($s)->post(route('klijenti.pozivi.ponovno', $p));
        Mail::assertNothingSent();
        $p->update(['poslano_at' => now()->subDays(2)]);
        $this->actingAs($s)->post(route('klijenti.pozivi.ponovno', $p));
        Mail::assertSent(PozivGospodarstvuMail::class, 1);
        $this->assertSame(2, $p->fresh()->broj_slanja);
        $p->update(['poslano_at' => now()->subDays(2), 'broj_slanja' => PozivGospodarstvu::MAX_SLANJA]);
        $this->actingAs($s)->post(route('klijenti.pozivi.ponovno', $p));
        Mail::assertSent(PozivGospodarstvuMail::class, 1);

        // isti e-mail dok čeka odgovor
        $this->actingAs($s)->post(route('klijenti.pozivi.posalji'), ['email' => 'horvat@example.com'])->assertSessionHasErrors('email', null, 'pozivGospodarstvu');

        // dnevni limit
        for ($i = 0; $i < 20; $i++) {
            RateLimiter::hit('pozivi-gospodarstvima:' . $s->id, 86400);
        }
        $this->actingAs($s)->post(route('klijenti.pozivi.posalji'), ['email' => 'novi@example.com'])->assertSessionHasErrors('email', null, 'pozivGospodarstvu');
        $this->assertFalse(PozivGospodarstvu::where('email', 'novi@example.com')->exists());
    }

    public function test_postojeci_klijent_i_vlastita_adresa_se_ne_mogu_pozvati(): void
    {
        Mail::fake();
        $s = $this->savjetnik();
        $v = User::factory()->create(['email' => 'horvat@example.com']);
        Pristup::create(['vlasnik_id' => $v->id, 'email' => $s->email, 'savjetnik_id' => $s->id, 'prava' => 'unos', 'token' => Pristup::noviToken(), 'status' => 'prihvaceno', 'prihvaceno_at' => now()]);

        $this->actingAs($s)->post(route('klijenti.pozivi.posalji'), ['email' => 'horvat@example.com'])->assertSessionHasErrors('email', null, 'pozivGospodarstvu');
        $this->actingAs($s)->post(route('klijenti.pozivi.posalji'), ['email' => 'ana@zadruga.hr'])->assertSessionHasErrors('email', null, 'pozivGospodarstvu');
        Mail::assertNothingSent();
    }

    public function test_prihvacanje_ne_stvara_dvostruki_pristup_uz_postojeci_poziv_gospodarstva(): void
    {
        Mail::fake();
        $s = $this->savjetnik();
        $v = User::factory()->create(['email' => 'horvat@example.com']);
        $postojeci = Pristup::create(['vlasnik_id' => $v->id, 'email' => 'ana@zadruga.hr', 'prava' => 'unos', 'token' => Pristup::noviToken(), 'status' => 'poslano']);
        $p = $this->poziv($s);

        $this->actingAs($v)->post(route('poziv.gospodarstvo.prihvati', $p->token), ['prava' => 'pregled']);

        $this->assertSame(1, Pristup::count());
        $this->assertSame(['prihvaceno', $s->id, 'pregled'], [$postojeci->fresh()->status, $postojeci->fresh()->savjetnik_id, $postojeci->fresh()->prava]);
    }

    public function test_upute_opisuju_poziv_savjetnika(): void
    {
        $this->get(route('docs'))->assertOk()->assertSee('savjetnik-poziv', false)->assertSee('pozovite svoje poljoprivrednike');
    }
}
