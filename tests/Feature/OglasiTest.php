<?php

namespace Tests\Feature;

use App\Http\Controllers\SadrzajController;
use App\Models\Oglas;
use App\Models\Pristup;
use App\Models\User;
use App\Support\Oglasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class OglasiTest extends TestCase
{
    use RefreshDatabase;

    private const CHROME = 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Chrome/130.0 Mobile Safari/537.36';

    private array $preneseno = [];

    protected function tearDown(): void
    {
        foreach (Oglas::query()->get() as $o) {
            Oglasi::obrisiSliku($o->slika);
            Oglasi::obrisiSliku($o->slika_mobitel);
        }
        parent::tearDown();
    }

    private function rezervni(): Oglas
    {
        return Oglas::where('rezervni', true)->firstOrFail();
    }

    private function oglas(array $p = []): Oglas
    {
        return Oglas::create($p + ['naziv' => 'Gnojivo d.o.o.', 'slika' => 'images/oglasi/oglasavanje.webp', 'slika_w' => 2172, 'slika_h' => 242, 'url' => 'https://gnojivo.example/'])->fresh();
    }

    private function red(string $mjesto)
    {
        return DB::table('oglas_statistika')->where('mjesto', $mjesto)->first();
    }

    public function test_banner_je_jednom_na_svakom_mjestu_s_oznakom(): void
    {
        $o = $this->rezervni();
        $vodic = SadrzajController::vodici()[0]['slug'];
        $novost = SadrzajController::novosti()[0]['slug'];
        foreach (['/' => 'naslovna', '/vodici' => 'vodici', "/vodici/$vodic" => 'vodici', '/novosti' => 'novosti', "/novosti/$novost" => 'novosti'] as $put => $mjesto) {
            $r = $this->get($put)->assertOk();
            $this->assertSame(1, substr_count($r->getContent(), 'data-oglas-prikaz='), $put);
            $r->assertSee(route('oglas.klik', [$o->id, $mjesto]))->assertSee('rel="sponsored nofollow"', false)->assertSee('>Oglas</span>', false);
        }
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertOk()->assertSee(route('oglas.klik', [$o->id, 'dashboard']));
    }

    public function test_nema_ga_gdje_smeta(): void
    {
        $u = User::factory()->create();
        foreach (['/prskanje', '/gnojidba', '/berba', '/parcele', '/uvjeti-koristenja', '/kontakt', '/alati'] as $put) {
            $this->actingAs($u)->get($put)->assertOk()->assertDontSee('data-oglas-prikaz', false);
        }
    }

    public function test_datumi_kvacice_i_rezervni(): void
    {
        $this->travelTo('2026-11-06 10:00');
        $r = $this->rezervni();
        $this->assertSame($r->id, Oglasi::za('naslovna')->id);   // nema drugih

        $o = $this->oglas(['od' => '2026-11-10', 'do' => '2026-11-20', 'na_dashboardu' => false]);
        $this->assertSame('zakazan', $o->stanje);
        $this->assertSame($r->id, Oglasi::za('naslovna')->id);   // još nije počeo

        $this->travelTo('2026-11-10 00:30');
        $this->assertSame($o->id, Oglasi::za('naslovna')->id);   // prvi dan, istiskuje rezervni
        $this->assertSame($o->id, Oglasi::za('vodici')->id);
        $this->assertSame($o->id, Oglasi::za('novosti')->id);
        $this->assertSame($r->id, Oglasi::za('dashboard')->id);  // nema kvačicu

        $this->travelTo('2026-11-20 23:30');
        $this->assertSame($o->id, Oglasi::za('naslovna')->id);   // zadnji dan se računa
        $this->travelTo('2026-11-21 00:30');
        $this->assertSame('istekao', $o->fresh()->stanje);
        $this->assertSame($r->id, Oglasi::za('naslovna')->id);

        $this->travelTo('2026-11-15 10:00');
        $o->update(['aktivan' => false]);
        $this->assertSame('ugašen', $o->fresh()->stanje);
        $this->assertSame($r->id, Oglasi::za('naslovna')->id);

        // bez ikakvog oglasa stranica radi
        Oglas::query()->delete();
        $this->assertNull(Oglasi::za('naslovna'));
        $this->get('/')->assertOk()->assertDontSee('data-oglas-prikaz', false);
    }

    public function test_vise_oglasa_se_izmjenjuje_pri_ucitavanju(): void
    {
        $a = $this->oglas(['naziv' => 'A']);
        $b = $this->oglas(['naziv' => 'B']);
        $vidjeno = collect(range(1, 60))->map(fn () => Oglasi::za('naslovna')->id)->unique()->sort()->values()->all();
        $this->assertSame([$a->id, $b->id], $vidjeno);   // rezervni se ne vidi uz prave oglase
    }

    public function test_savjetnik_u_tudjem_gospodarstvu_ne_vidi_oglas_i_brojanje_nije_blokirano(): void
    {
        $vlasnik = User::factory()->create();
        $savjetnik = User::factory()->create();
        $p = Pristup::create(['vlasnik_id' => $vlasnik->id, 'email' => $savjetnik->email, 'savjetnik_id' => $savjetnik->id,
            'prava' => 'pregled', 'token' => Pristup::noviToken(), 'status' => 'prihvaceno']);
        $this->actingAs($savjetnik)->post(route('klijenti.otvori', $p));
        $this->get('/dashboard')->assertOk()->assertDontSee('data-oglas-prikaz', false);
        $this->withHeader('User-Agent', self::CHROME)->post(route('oglas.prikaz', [$this->rezervni()->id, 'dashboard']))->assertNoContent();
        $this->assertSame(1, $this->red('dashboard')->prikazi);
    }

    public function test_brojanje_prikaza_i_klikova_po_mjestu_i_danu(): void
    {
        $o = $this->rezervni();
        $this->withHeader('User-Agent', self::CHROME);
        $this->post(route('oglas.prikaz', [$o->id, 'naslovna']))->assertNoContent();
        $this->post(route('oglas.prikaz', [$o->id, 'naslovna']))->assertNoContent();
        $this->post(route('oglas.prikaz', [$o->id, 'vodici']))->assertNoContent();
        $this->get(route('oglas.klik', [$o->id, 'naslovna']))->assertRedirect(route('contact'));

        $this->assertSame(2, $this->red('naslovna')->prikazi);
        $this->assertSame(1, $this->red('naslovna')->klikovi);
        $this->assertSame(1, $this->red('vodici')->prikazi);
        $this->assertSame(0, $this->red('vodici')->klikovi);

        $this->travel(1)->days();
        $this->post(route('oglas.prikaz', [$o->id, 'naslovna']));
        $this->assertSame(3, DB::table('oglas_statistika')->count());

        // poveznica oglašivača
        $g = $this->oglas();
        $this->get(route('oglas.klik', [$g->id, 'dashboard']))->assertRedirect('https://gnojivo.example/');
    }

    public function test_ne_broje_se_botovi_ni_nepoznate_vrijednosti(): void
    {
        $o = $this->rezervni();
        $this->assertTrue(Oglasi::jeBot('Mozilla/5.0 (compatible; Googlebot/2.1)'));
        $this->assertTrue(Oglasi::jeBot(null));
        $this->assertFalse(Oglasi::jeBot(self::CHROME));

        $this->withHeader('User-Agent', 'Googlebot/2.1');
        $this->post(route('oglas.prikaz', [$o->id, 'naslovna']))->assertNoContent();
        $this->get(route('oglas.klik', [$o->id, 'naslovna']))->assertRedirect(route('contact'));
        $this->assertSame(0, DB::table('oglas_statistika')->count());

        $this->withHeader('User-Agent', self::CHROME);
        $this->post("/oglas/{$o->id}/nepostojece/prikaz")->assertNotFound();
        $this->post('/oglas/99999/naslovna/prikaz')->assertNotFound();
        $this->get('/oglas/99999/naslovna/klik')->assertNotFound();
    }

    public function test_klik_uvijek_vodi_dalje_i_kad_je_brojanje_ograniceno(): void
    {
        $o = $this->rezervni();
        $this->withHeader('User-Agent', self::CHROME);
        RateLimiter::clear('oglas-klik:' . sha1('127.0.0.1'));
        for ($i = 0; $i < 22; $i++) {
            $this->get(route('oglas.klik', [$o->id, 'dashboard']))->assertRedirect(route('contact'));
        }
        $this->assertSame(20, $this->red('dashboard')->klikovi);
    }

    public function test_admin_dodaje_uredjuje_i_brise_oglas(): void
    {
        $admin = User::factory()->create(['email' => 'slynetwork@gmail.com']);
        $this->actingAs($admin)->get('/admin/oglasi')->assertOk()->assertSee('Oglašavajte se u AgroLogu')->assertSee('rezervni');

        $this->post(route('admin.oglasi.spremi'), [
            'naziv' => 'Gnojivo d.o.o.', 'url' => 'https://gnojivo.example', 'od' => '2026-11-10', 'do' => '2026-11-20',
            'na_naslovnoj' => '1', 'na_clancima' => '1', 'aktivan' => '1',
            'slika' => UploadedFile::fake()->image('b.png', 1200, 150), 'slika_mobitel' => UploadedFile::fake()->image('m.jpg', 1080, 400),
        ])->assertRedirect(route('admin.oglasi'));

        $o = Oglas::where('naziv', 'Gnojivo d.o.o.')->firstOrFail();
        $this->assertTrue($o->na_naslovnoj);
        $this->assertFalse($o->na_dashboardu);
        $this->assertTrue($o->na_clancima);
        $this->assertSame([1200, 150], [$o->slika_w, $o->slika_h]);
        $this->assertStringStartsWith(Oglasi::DIR . '/', $o->slika);
        $this->assertFileExists(public_path($o->slika));
        $this->get('/admin/oglasi')->assertSee('od 10.11.2026.')->assertSee('do 20.11.2026.')->assertSee('Naslovna, Vodiči i novosti');

        // uređivanje bez nove slike ne dira sliku
        $slika = $o->slika;
        $mob = $o->slika_mobitel;
        $this->post(route('admin.oglasi.azuriraj', $o), ['naziv' => 'Novo ime', 'url' => '/kontakt', 'aktivan' => '1', 'makni_mobitel' => '1'])->assertRedirect();
        $o->refresh();
        $this->assertSame('Novo ime', $o->naziv);
        $this->assertSame($slika, $o->slika);
        $this->assertNull($o->slika_mobitel);
        $this->assertFileDoesNotExist(public_path($mob));

        $this->delete(route('admin.oglasi.obrisi', $o))->assertRedirect();
        $this->assertNull($o->fresh());
        $this->assertFileDoesNotExist(public_path($slika));
    }

    public function test_provjere_unosa_i_samo_admin(): void
    {
        $admin = User::factory()->create(['email' => 'slynetwork@gmail.com']);
        $this->actingAs($admin)->from('/admin/oglasi')->post(route('admin.oglasi.spremi'), ['naziv' => 'X', 'url' => 'javascript:alert(1)', 'od' => '2026-11-20', 'do' => '2026-11-10',
            'slika' => UploadedFile::fake()->create('zlo.php', 10, 'text/plain')])
            ->assertSessionHasErrors(['url', 'do', 'slika']);
        $this->from('/admin/oglasi')->post(route('admin.oglasi.spremi'), ['naziv' => 'X', 'url' => '//evil.example', 'slika' => UploadedFile::fake()->create('a.svg', 5, 'image/svg+xml')])
            ->assertSessionHasErrors(['url', 'slika']);
        $this->assertSame(1, Oglas::count());

        // obrisana slika iz aplikacije ostaje na disku
        $r = $this->rezervni();
        $this->delete(route('admin.oglasi.obrisi', $r));
        $this->assertFileExists(public_path('images/oglasi/oglasavanje.webp'));

        $u = User::factory()->create();
        $this->flushSession();
        $this->actingAs($u)->get('/admin/oglasi')->assertForbidden();
        $this->post(route('admin.oglasi.spremi'), ['naziv' => 'X', 'url' => '/kontakt'])->assertForbidden();
    }

    public function test_pravila_spominju_oglase(): void
    {
        $this->get('/kolacici')->assertSee('Naši oglasi')->assertDontSee('Oglasa nema unutar aplikacije');
        $this->get('/uvjeti-koristenja')->assertSee('oglasi');
        $this->get('/politika-privatnosti')->assertSee('zbirno');
    }
}
