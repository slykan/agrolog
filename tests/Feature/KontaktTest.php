<?php

namespace Tests\Feature;

use App\Http\Controllers\KontaktController;
use App\Mail\KontaktMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class KontaktTest extends TestCase
{
    use RefreshDatabase;

    private const OIB = '69435151530';   // ispravna kontrolna znamenka

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('kontakt:' . sha1('127.0.0.1'));
        RateLimiter::clear('kontakt-kopija:' . sha1('ana@firma.example'));
    }

    private function podaci(array $p = []): array
    {
        return $p + ['tema' => 'oglasavanje', 'ime' => 'Ana Horvat', 'tvrtka' => 'Gnojivo d.o.o.', 'oib' => self::OIB, 'email' => 'ana@firma.example',
            'poruka' => "Zanima nas oglašavanje.\nJavite cijene.", 'obrazac_od' => Crypt::encryptString((string) (time() - 30))];
    }

    public function test_obrazac_se_prikazuje_i_tema_iz_oglasa_je_odabrana(): void
    {
        $this->get('/kontakt')->assertOk()->assertSee('Pošaljite nam poruku')->assertSee('Oglašavanje')->assertSee('Nadogradnja aplikacije')->assertSee('Opća pitanja')->assertSee('Pošalji kopiju poruke i meni');
        $this->get('/kontakt?tema=oglasavanje')->assertSee('<option value="oglasavanje" selected', false);
        $this->get('/kontakt?tema=zlo')->assertDontSee(' selected', false);
    }

    public function test_slanje_nama_s_odgovorom_na_posiljatelja(): void
    {
        Mail::fake();
        $this->post('/kontakt', $this->podaci())->assertRedirect(route('contact') . '#poruka')->assertSessionHas('kontakt_poslano');

        Mail::assertSent(KontaktMail::class, 1);
        Mail::assertSent(KontaktMail::class, fn ($m) => $m->hasTo(KontaktController::PRIMATELJ) && $m->hasReplyTo('ana@firma.example') && ! $m->kopija
            && $m->hasSubject('[Oglašavanje] Ana Horvat, Gnojivo d.o.o.'));
    }

    public function test_kopija_i_ogranicenje_kopija(): void
    {
        Mail::fake();
        $this->post('/kontakt', $this->podaci(['kopija' => '1']));
        Mail::assertSent(KontaktMail::class, 2);
        Mail::assertSent(KontaktMail::class, fn ($m) => $m->hasTo('ana@firma.example') && $m->kopija && $m->hasReplyTo(KontaktController::PRIMATELJ));

        $this->post('/kontakt', $this->podaci(['kopija' => '1']));
        $this->post('/kontakt', $this->podaci(['kopija' => '1']));   // treća kopija na istu adresu u satu se ne šalje
        Mail::assertSent(KontaktMail::class, fn ($m) => true);
        $this->assertCount(2, Mail::sent(KontaktMail::class, fn ($m) => $m->kopija));
        $this->assertCount(3, Mail::sent(KontaktMail::class, fn ($m) => ! $m->kopija));
    }

    public function test_mail_je_siguran_i_ima_sve_podatke(): void
    {
        $p = ['tema' => 'opce', 'tema_naziv' => 'Opća pitanja', 'ime' => '<script>x</script>', 'tvrtka' => null, 'oib' => self::OIB, 'email' => 'ana@firma.example', 'poruka' => "Red <b>1</b>\nRed 2"];
        $html = (new KontaktMail($p, false))->render();
        $this->assertStringNotContainsString('<script>x', $html);
        $this->assertStringNotContainsString('<b>1</b>', $html);
        $this->assertStringContainsString('Red &lt;b&gt;1&lt;/b&gt;<br />', $html);
        $this->assertStringContainsString(self::OIB, $html);
        $this->assertStringNotContainsString('>Tvrtka<', $html);
        $this->assertStringContainsString('Odgovori', $html);
        $this->assertStringContainsString(now()->format('d.m.Y.'), $html);

        $kopija = (new KontaktMail($p, true))->render();
        $this->assertStringContainsString('ovo je kopija vaše poruke', $kopija);
        $this->assertStringNotContainsString('Odgovori', $kopija);
    }

    public function test_provjere_polja(): void
    {
        Mail::fake();
        $this->from('/kontakt')->post('/kontakt', $this->podaci(['tema' => 'nesto', 'ime' => '', 'email' => 'nije-mail', 'poruka' => 'kratko', 'oib' => '12345678901']))
            ->assertSessionHasErrors(['tema' => 'Odaberite temu s popisa.', 'ime' => 'Upišite ime i prezime.', 'email' => 'Upišite ispravnu e-mail adresu.',
                'poruka' => 'Poruka je prekratka, upišite barem 10 znakova.', 'oib']);
        Mail::assertNothingSent();

        // tvrtka i OIB nisu obvezni
        $this->post('/kontakt', $this->podaci(['tvrtka' => '', 'oib' => '']))->assertSessionHas('kontakt_poslano');
        Mail::assertSent(KontaktMail::class, 1);

        $this->assertTrue(KontaktController::oibIspravan(self::OIB));
        $this->assertFalse(KontaktController::oibIspravan('69435151531'));
        $this->assertFalse(KontaktController::oibIspravan('abc'));
    }

    public function test_zastita_od_spama(): void
    {
        Mail::fake();

        // skriveno polje: izgleda kao uspjeh, ništa se ne šalje
        $this->post('/kontakt', $this->podaci(['web_stranica' => 'http://spam.example']))->assertSessionHas('kontakt_poslano');
        Mail::assertNothingSent();

        // prebrzo ili bez tokena
        $this->post('/kontakt', $this->podaci(['obrazac_od' => Crypt::encryptString((string) time())]))->assertSessionHas('kontakt_greska');
        $this->post('/kontakt', $this->podaci(['obrazac_od' => 'krivo']))->assertSessionHas('kontakt_greska');
        Mail::assertNothingSent();

        // previše poveznica
        $this->from('/kontakt')->post('/kontakt', $this->podaci(['poruka' => 'http://a.hr http://b.hr http://c.hr http://d.hr']))->assertSessionHasErrors('poruka');
        Mail::assertNothingSent();

        // najviše 5 poruka s iste adrese u 10 minuta
        for ($i = 0; $i < 6; $i++) {
            $r = $this->post('/kontakt', $this->podaci());
        }
        $r->assertSessionHas('kontakt_greska');
        Mail::assertSent(KontaktMail::class, 5);
    }

    public function test_pad_slanja_cuva_upisano_i_javlja_gresku(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP'));
        $this->post('/kontakt', $this->podaci())->assertSessionHas('kontakt_greska')->assertSessionHasInput('poruka');
    }

    public function test_stranica_oglasavanje_i_povratak_na_nju(): void
    {
        Mail::fake();
        $this->get('/oglasavanje')->assertOk()->assertSee('Cijena na upit')->assertSee('Sponzorirani članak')->assertSee('2172 × 242')->assertSee('<option value="oglasavanje" selected', false)
            ->assertDontSee('Gospodarstava s parcelama');   // premalo za objavu
        $this->get('/kontakt')->assertSee(route('oglasavanje'));
        $this->get('/sitemap.xml')->assertSee(route('oglasavanje'));

        $this->post('/kontakt', $this->podaci(['izvor' => 'oglasavanje']))->assertRedirect(route('oglasavanje') . '#poruka');
        $this->post('/kontakt', $this->podaci(['izvor' => 'oglasavanje', 'poruka' => 'x']))->assertRedirect(route('oglasavanje') . '#poruka')->assertSessionHasErrors('poruka');
        $this->post('/kontakt', $this->podaci(['izvor' => 'https://evil.example']))->assertRedirect(route('contact') . '#poruka');
    }

    public function test_brojke_se_vide_tek_kad_ih_je_dovoljno(): void
    {
        foreach (User::factory()->count(20)->create() as $i => $u) {
            \App\Models\Parcela::create(['user_id' => $u->id, 'arkod_broj' => (string) (1000 + $i), 'povrsina_ha' => 10]);
        }
        $this->get('/oglasavanje')->assertSee('Gospodarstava s parcelama')->assertSee('200')->assertDontSee('Prikaza bannera');
        \DB::table('oglas_statistika')->insert(['oglas_id' => \App\Models\Oglas::first()->id, 'mjesto' => 'naslovna', 'datum' => now()->toDateString(), 'prikazi' => 1500, 'klikovi' => 3]);
        $this->get('/oglasavanje')->assertSee('Prikaza bannera')->assertSee('1.500');
    }

    public function test_poruke_su_na_hrvatskom(): void
    {
        $this->assertSame('hr', app()->getLocale());
        $this->from('/register')->post('/register', ['email' => 'nije'])->assertSessionHasErrors();
        $greske = session('errors')->getBag('default')->all();
        foreach ($greske as $g) {
            $this->assertDoesNotMatchRegularExpression('/\b(field|must|required)\b/i', $g);
        }
        $v = \Illuminate\Support\Facades\Validator::make(['email' => 'nije', 'password' => ''], ['email' => 'email', 'password' => 'required']);
        $this->assertSame('Polje e-mail adresa mora biti ispravna e-mail adresa.', $v->errors()->first('email'));
        $this->assertSame('Polje lozinka je obavezno.', $v->errors()->first('password'));

        $this->from('/login')->post('/login', ['email' => 'nema@example.com', 'password' => 'x'])->assertSessionHasErrors(['email' => 'E-mail adresa ili lozinka nisu točni.']);
        $this->get('/login')->assertSee('Zaboravljena lozinka?')->assertSee('Zapamti me')->assertDontSee('Remember me');
        $this->get('/register')->assertSee('Već imate račun?')->assertDontSee('Already registered?');
    }

    public function test_prijavljenom_su_podaci_vec_upisani(): void
    {
        $u = User::factory()->create(['name' => 'Ivo Ratar', 'email' => 'ivo@example.com', 'naziv_gospodarstva' => 'OPG Ratar']);
        $this->actingAs($u)->get('/kontakt')->assertSee('value="Ivo Ratar"', false)->assertSee('value="ivo@example.com"', false)->assertSee('value="OPG Ratar"', false);
    }
}
