<?php

namespace Tests\Feature;

use App\Mail\PozivSavjetnikuMail;
use App\Mail\SavjetnikPrihvatioMail;
use App\Models\Parcela;
use App\Models\Pristup;
use App\Models\PristupIzmjena;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SavjetnikTest extends TestCase
{
    use RefreshDatabase;

    private function vlasnikISavjetnik(string $prava = 'unos'): array
    {
        $vlasnik = User::factory()->create(['naziv_gospodarstva' => 'OPG Horvat', 'email' => 'horvat@example.com']);
        $savjetnik = User::factory()->create(['name' => 'Ana Savjetnik', 'email' => 'ana@zadruga.hr']);
        $pristup = Pristup::create(['vlasnik_id' => $vlasnik->id, 'email' => 'ana@zadruga.hr', 'savjetnik_id' => $savjetnik->id,
            'prava' => $prava, 'token' => Pristup::noviToken(), 'status' => 'prihvaceno', 'prihvaceno_at' => now()]);

        return [$vlasnik, $savjetnik, $pristup];
    }

    public function test_poziv_mailom_i_prihvacanje(): void
    {
        Mail::fake();
        $vlasnik = User::factory()->create(['naziv_gospodarstva' => 'OPG Horvat']);
        $savjetnik = User::factory()->create(['email' => 'ana@zadruga.hr', 'name' => 'Ana Savjetnik']);

        $this->actingAs($vlasnik)->post('/savjetnici', ['email' => 'Ana@Zadruga.hr', 'prava' => 'unos'])->assertRedirect();
        $p = Pristup::firstOrFail();
        $this->assertSame('ana@zadruga.hr', $p->email);
        $this->assertSame('poslano', $p->status);
        Mail::assertSent(PozivSavjetnikuMail::class, fn ($m) => $m->hasTo('ana@zadruga.hr'));

        // mail se lijepo iscrta
        $html = (new PozivSavjetnikuMail($p->load('vlasnik')))->render();
        $this->assertStringContainsString('OPG Horvat vas poziva kao savjetnika', $html);
        $this->assertStringContainsString(route('poziv.savjetnik', $p->token), $html);

        // neprijavljen: ponuda prijave; prijavljen: prihvaćanje
        auth()->logout();
        $this->get(route('poziv.savjetnik', $p->token))->assertOk()->assertSee('Registracija');
        $this->actingAs($savjetnik)->get(route('poziv.savjetnik', $p->token))->assertOk()->assertSee('Prihvati poziv');
        $this->actingAs($savjetnik)->post(route('poziv.prihvati', $p->token))->assertRedirect(route('klijenti'));
        $this->assertSame('prihvaceno', $p->fresh()->status);
        $this->assertSame($savjetnik->id, $p->fresh()->savjetnik_id);
        Mail::assertSent(SavjetnikPrihvatioMail::class, fn ($m) => $m->hasTo($vlasnik->email));

        $this->actingAs($savjetnik)->get('/klijenti')->assertOk()->assertSee('OPG Horvat');
    }

    public function test_ne_moze_pozvati_sebe_ni_prihvatiti_vlastiti_poziv(): void
    {
        $vlasnik = User::factory()->create(['email' => 'ja@example.com']);
        $this->actingAs($vlasnik)->post('/savjetnici', ['email' => 'ja@example.com', 'prava' => 'unos'])->assertSessionHasErrors('email', null, 'savjetnik');
        $p = Pristup::create(['vlasnik_id' => $vlasnik->id, 'email' => 'x@y.hr', 'prava' => 'unos', 'token' => 'tok', 'status' => 'poslano']);
        $this->actingAs($vlasnik)->post(route('poziv.prihvati', 'tok'))->assertForbidden();
        $this->assertSame('poslano', $p->fresh()->status);
    }

    public function test_rad_u_klijentovom_gospodarstvu_i_povijest_izmjena(): void
    {
        [$vlasnik, $savjetnik, $pristup] = $this->vlasnikISavjetnik('unos');
        $parcela = Parcela::create(['user_id' => $vlasnik->id, 'arkod_broj' => '1515096', 'povrsina_ha' => 10]);
        Parcela::create(['user_id' => $savjetnik->id, 'arkod_broj' => '9999999', 'povrsina_ha' => 1]);
        Http::fake(fn () => Http::response(file_get_contents(base_path('tests/fixtures/arkod-1515096.xml'))));

        $this->actingAs($savjetnik)->post(route('klijenti.otvori', $pristup))->assertRedirect(route('dashboard'));

        // vidi klijentove parcele i traku, ne svoje
        $this->get('/parcele')->assertOk()->assertSee('1515096')->assertDontSee('9999999')
            ->assertSee('Radite u gospodarstvu OPG Horvat')->assertSee('Natrag na svoje');

        // upis u klijentovom gospodarstvu (ARKOD oblik parcele) bilježi se u povijest
        $this->getJson(route('parcela.arkod', $parcela))->assertOk();
        $this->assertNotNull($parcela->fresh()->arkod_poligon);
        $iz = PristupIzmjena::firstOrFail();
        $this->assertSame($vlasnik->id, $iz->vlasnik_id);
        $this->assertSame('Ana Savjetnik', $iz->savjetnik_ime);
        $this->assertContains('parcele', $iz->tablice);

        // profil i admin nisu dostupni u klijentovom gospodarstvu
        $this->get('/profile')->assertRedirect(route('dashboard'));
        $this->get('/admin/korisnici')->assertRedirect(route('dashboard'));

        // natrag na svoje
        $this->post(route('klijenti.zatvori'))->assertRedirect(route('klijenti'));
        $this->get('/parcele')->assertSee('9999999')->assertDontSee('Radite u gospodarstvu');

        // gospodarstvo vidi povijest u profilu
        $this->flushSession();
        $this->actingAs($vlasnik)->get('/profile')->assertOk()->assertSee('Povijest izmjena savjetnika')->assertSee('Ana Savjetnik');
    }

    public function test_samo_pregled_blokira_upis(): void
    {
        [$vlasnik, $savjetnik, $pristup] = $this->vlasnikISavjetnik('pregled');
        $parcela = Parcela::create(['user_id' => $vlasnik->id, 'arkod_broj' => '1515096', 'povrsina_ha' => 10]);
        Http::fake(fn () => Http::response(file_get_contents(base_path('tests/fixtures/arkod-1515096.xml'))));

        $this->actingAs($savjetnik)->post(route('klijenti.otvori', $pristup));
        $this->get('/parcele')->assertOk()->assertSee('Samo pregled');
        $this->getJson(route('parcela.arkod', $parcela))->assertForbidden();
        $this->assertNull($parcela->fresh()->arkod_poligon);
        $this->assertSame(0, PristupIzmjena::count());
    }

    public function test_bez_pristupa_nema_tudjih_podataka(): void
    {
        [$vlasnik, $savjetnik, $pristup] = $this->vlasnikISavjetnik();
        Parcela::create(['user_id' => $vlasnik->id, 'arkod_broj' => '1515096', 'povrsina_ha' => 10]);
        $uljez = User::factory()->create();

        // tuđi pristup se ne može otvoriti
        $this->actingAs($uljez)->post(route('klijenti.otvori', $pristup))->assertNotFound();
        // ni podmetanjem u sesiju
        $this->actingAs($uljez)->withSession(['klijent_pristup_id' => $pristup->id])->get('/parcele')->assertDontSee('1515096');

        // opozvan pristup: savjetnik se vraća na svoje
        $this->actingAs($savjetnik)->post(route('klijenti.otvori', $pristup));
        $this->get('/parcele')->assertSee('1515096');
        $this->flushSession();   // vlasnik ima svoju sesiju
        $this->actingAs($vlasnik)->delete(route('savjetnici.ukini', $pristup))->assertRedirect();
        $this->withSession(['klijent_pristup_id' => $pristup->id, 'klijent_savjetnik_id' => $savjetnik->id]);
        $this->assertSame('opozvano', $pristup->fresh()->status);
        $this->actingAs($savjetnik)->get('/parcele')->assertDontSee('1515096')->assertDontSee('Radite u gospodarstvu');
    }

    public function test_savjetnik_u_klijentu_nikad_nije_admin(): void
    {
        $admin = User::factory()->create(['email' => 'slynetwork@gmail.com', 'naziv_gospodarstva' => 'Admin OPG']);
        $savjetnik = User::factory()->create();
        $p = Pristup::create(['vlasnik_id' => $admin->id, 'email' => $savjetnik->email, 'savjetnik_id' => $savjetnik->id,
            'prava' => 'unos', 'token' => Pristup::noviToken(), 'status' => 'prihvaceno']);
        $this->actingAs($savjetnik)->post(route('klijenti.otvori', $p));
        $this->get('/dashboard')->assertOk()->assertDontSee('Obavijesti korisnicima');
        $this->get('/admin/korisnici')->assertRedirect(route('dashboard'));
    }
}
