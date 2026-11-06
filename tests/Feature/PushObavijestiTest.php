<?php

namespace Tests\Feature;

use App\Jobs\PosaljiObavijest;
use App\Mail\ObavijestMail;
use App\Mail\PodsjetnikRokovaMail;
use App\Models\Obavijest;
use App\Models\Pristup;
use App\Models\PushPretplata;
use App\Models\Skladiste as SkladisteModel;
use App\Models\SkladisteUlaz;
use App\Models\Stroj;
use App\Models\StrojRok;
use App\Models\User;
use App\Support\Mehanizacija;
use App\Support\PushUpozorenja;
use App\Support\WebPush;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpZahtjev;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PushObavijestiTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = 'https://fcm.googleapis.com/fcm/send/abc';

    /** Par kakav bi imao preglednik: privatni ključ i podaci pretplate. */
    private function ua(): array
    {
        $cnf = tempnam(sys_get_temp_dir(), 'agro');
        file_put_contents($cnf, "[req]\ndistinguished_name=r\n[r]\n");
        $k = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC, 'config' => $cnf]);
        @unlink($cnf);
        $d = openssl_pkey_get_details($k)['ec'];

        return [$k, "\x04" . str_pad($d['x'], 32, "\0", STR_PAD_LEFT) . str_pad($d['y'], 32, "\0", STR_PAD_LEFT), random_bytes(16)];
    }

    private function pretplata(User $u): PushPretplata
    {
        [, $javni, $auth] = $this->ua();

        return PushPretplata::create(['user_id' => $u->id, 'endpoint' => self::ENDPOINT . $u->id, 'endpoint_hash' => hash('sha256', self::ENDPOINT . $u->id),
            'p256dh' => WebPush::b64($javni), 'auth' => WebPush::b64($auth)]);
    }

    public function test_sifriranje_se_desifrira_kao_u_pregledniku(): void
    {
        [$uaKljuc, $uaJavni, $tajna] = $this->ua();
        $poruka = '{"naslov":"Čćžšđ"}';
        $t = WebPush::sifriraj($poruka, WebPush::b64($uaJavni), WebPush::b64($tajna));

        // zaglavlje po RFC 8188
        $sol = substr($t, 0, 16);
        $this->assertSame(4096, unpack('N', substr($t, 16, 4))[1]);
        $this->assertSame(65, ord($t[20]));
        $asJavni = substr($t, 21, 65);
        $zapis = substr($t, 86);

        // strana preglednika, neovisno o WebPush klasi
        $pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode(hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200') . $asJavni), 64, "\n") . "-----END PUBLIC KEY-----\n";
        $ecdh = openssl_pkey_derive(openssl_pkey_get_public($pem), $uaKljuc, 32);
        $ikm = hash_hkdf('sha256', $ecdh, 32, "WebPush: info\0" . $uaJavni . $asJavni, $tajna);
        $cek = hash_hkdf('sha256', $ikm, 16, "Content-Encoding: aes128gcm\0", $sol);
        $nonce = hash_hkdf('sha256', $ikm, 12, "Content-Encoding: nonce\0", $sol);
        $o = openssl_decrypt(substr($zapis, 0, -16), 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, substr($zapis, -16));

        $this->assertSame($poruka . "\x02", $o);
    }

    public function test_vapid_potpis_je_valjan(): void
    {
        $z = WebPush::vapid(self::ENDPOINT);
        $this->assertMatchesRegularExpression('/^vapid t=[\w-]+\.[\w-]+\.[\w-]+, k=[\w-]+$/', $z);
        preg_match('/t=([\w-]+)\.([\w-]+)\.([\w-]+), k=([\w-]+)/', $z, $m);
        $this->assertSame('https://fcm.googleapis.com', json_decode(WebPush::b64d($m[2]), true)['aud']);
        $this->assertSame(WebPush::javniKljuc(), $m[4]);

        $potpis = WebPush::b64d($m[3]);
        $this->assertSame(64, strlen($potpis));
        $int = fn ($b) => ($b = ltrim($b, "\0")) === '' ? "\x02\x01\0" : "\x02" . chr(strlen(ord($b[0]) > 127 ? "\0$b" : $b)) . (ord($b[0]) > 127 ? "\0$b" : $b);
        $niz = $int(substr($potpis, 0, 32)) . $int(substr($potpis, 32));
        $pem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode(hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200') . WebPush::b64d($m[4])), 64, "\n") . "-----END PUBLIC KEY-----\n";
        $this->assertSame(1, openssl_verify($m[1] . '.' . $m[2], "\x30" . chr(strlen($niz)) . $niz, $pem, OPENSSL_ALGO_SHA256));
    }

    public function test_pretplata_samo_na_poznate_servise_i_prelazi_na_novog_korisnika(): void
    {
        $this->assertTrue(WebPush::dozvoljenEndpoint('https://wns2-db5p.notify.windows.com/w/?token=x'));
        $this->assertTrue(WebPush::dozvoljenEndpoint('https://web.push.apple.com/abc'));
        $this->assertFalse(WebPush::dozvoljenEndpoint('http://fcm.googleapis.com/x'));
        $this->assertFalse(WebPush::dozvoljenEndpoint('https://169.254.169.254/latest'));
        $this->assertFalse(WebPush::dozvoljenEndpoint('https://fcm.googleapis.com.evil.com/x'));
        $this->assertFalse(WebPush::dozvoljenEndpoint('https://fcm.googleapis.com:8443/x'));

        [$prvi, $drugi] = [User::factory()->create(), User::factory()->create()];
        $podaci = ['endpoint' => self::ENDPOINT, 'keys' => ['p256dh' => 'a', 'auth' => 'b']];
        $this->actingAs($prvi)->postJson(route('push.spremi'), ['endpoint' => 'https://evil.example/x'] + $podaci)->assertStatus(422);
        $this->assertSame(0, PushPretplata::count());

        $this->actingAs($prvi)->postJson(route('push.spremi'), $podaci)->assertOk();
        $this->actingAs($drugi)->postJson(route('push.spremi'), $podaci)->assertOk();
        $this->assertSame(1, PushPretplata::count());
        $this->assertSame($drugi->id, PushPretplata::first()->user_id);

        // tuđi uređaj se ne može ukloniti
        $this->actingAs($prvi)->deleteJson(route('push.ukloni'), ['endpoint' => self::ENDPOINT])->assertOk();
        $this->assertSame(1, PushPretplata::count());
        $this->actingAs($drugi)->deleteJson(route('push.ukloni'), ['endpoint' => self::ENDPOINT])->assertOk();
        $this->assertSame(0, PushPretplata::count());
    }

    public function test_zahtjev_prema_servisu_i_brisanje_istekle_pretplate(): void
    {
        $u = User::factory()->create();
        $p = $this->pretplata($u);
        Http::fake(['*' => Http::response('', 201)]);
        $this->assertSame(1, WebPush::korisniku($u, 'Naslov', 'Tekst', 'https://agro-log.app/skladiste'));
        Http::assertSent(fn (HttpZahtjev $r) => $r->url() === $p->endpoint && $r->hasHeader('Content-Encoding', 'aes128gcm') && $r->hasHeader('TTL', '86400')
            && str_starts_with($r->header('Authorization')[0], 'vapid t=') && $r->method() === 'POST');

        // isključen push: ništa se ne šalje
        $u->forceFill(['push_obavijesti' => false])->save();
        Http::fake(fn () => $this->fail('Ne smije slati'));
        $this->assertSame(0, WebPush::korisniku($u->fresh(), 'N', 'T', 'https://agro-log.app'));
    }

    public function test_servis_javi_410_pa_se_pretplata_brise_a_greska_ne_rusi_slanje(): void
    {
        $u = User::factory()->create();
        $this->pretplata($u);
        Http::fake(['*' => Http::response('', 410)]);
        $this->assertSame(0, WebPush::korisniku($u, 'N', 'T', 'https://agro-log.app'));
        $this->assertSame(0, PushPretplata::count());

        $this->pretplata($u);
        Http::fake(['*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('nema veze')]);
        $this->assertSame(0, WebPush::korisniku($u, 'N', 'T', 'https://agro-log.app'));
        $this->assertSame(1, PushPretplata::count());
    }

    public function test_postavke_zadano_ukljuceno_i_gase_se_odvojeno(): void
    {
        $u = User::factory()->create();
        $this->assertTrue($u->fresh()->push_obavijesti);
        $this->assertNull($u->fresh()->obavijesti_odjava_at);
        $this->actingAs($u)->get('/profile')->assertOk()->assertSee('Push obavijesti')->assertSee('Mail obavijesti');
        $this->get('/dashboard')->assertSee('name="agro-push"', false)->assertSee('Uključite obavijesti na ovom uređaju');

        // ugasi mail, push ostaje
        $this->patch(route('profile.obavijesti'), ['push' => '1'])->assertRedirect();
        $this->assertNotNull($u->fresh()->obavijesti_odjava_at);
        $this->assertTrue($u->fresh()->push_obavijesti);

        // ugasi push, mail opet upali
        $this->patch(route('profile.obavijesti'), ['mail' => '1'])->assertRedirect();
        $this->assertNull($u->fresh()->obavijesti_odjava_at);
        $this->assertFalse($u->fresh()->push_obavijesti);
        $this->get('/dashboard')->assertDontSee('name="agro-push"', false);
    }

    public function test_ranije_odjavljeni_od_mailova_ostaje_odjavljen(): void
    {
        $u = User::factory()->create();
        $kada = now()->subMonth()->startOfSecond();
        $u->forceFill(['obavijesti_odjava_at' => $kada])->save();
        $this->actingAs($u)->patch(route('profile.obavijesti'), ['push' => '1']);
        $this->assertEquals($kada, $u->fresh()->obavijesti_odjava_at);   // spremanje ne pomiče datum odjave
    }

    public function test_rokovi_postuju_oba_prekidaca(): void
    {
        Mail::fake();
        Http::fake(['*' => Http::response('', 201)]);
        $s = fn ($u) => StrojRok::create(['user_id' => $u->id, 'stroj_id' => Stroj::create(['user_id' => $u->id, 'vrsta' => 'traktor', 'naziv' => 'IMT 539'])->id,
            'vrsta' => 'servis', 'datum_isteka' => now()->addDays(5), 'podsjetnik_dana' => 30]);

        $oba = User::factory()->create();
        $s($oba);
        $this->pretplata($oba);
        $samoPush = User::factory()->create();
        $samoPush->forceFill(['obavijesti_odjava_at' => now()])->save();
        $s($samoPush);
        $this->pretplata($samoPush);
        $nista = User::factory()->create();
        $nista->forceFill(['obavijesti_odjava_at' => now(), 'push_obavijesti' => false])->save();
        $rokNista = $s($nista);
        $this->pretplata($nista);

        $this->assertSame(1, Mehanizacija::posaljiPodsjetnike());
        Mail::assertSent(PodsjetnikRokovaMail::class, 1);
        Mail::assertSent(PodsjetnikRokovaMail::class, fn ($m) => $m->hasTo($oba->email));
        Http::assertSentCount(2);
        Http::assertNotSent(fn (HttpZahtjev $r) => $r->url() === self::ENDPOINT . $nista->id);
        $this->assertNotNull($rokNista->fresh()->podsjetnik_poslan_at);   // ne gomilaju se za kasnije

        // sutra se ne ponavlja
        Http::fake(fn () => $this->fail('Ne smije ponavljati'));
        $this->assertSame(0, Mehanizacija::posaljiPodsjetnike());
    }

    public function test_obavijest_iz_admina_ide_na_oba_kanala(): void
    {
        Mail::fake();
        Http::fake(['*' => Http::response('', 201)]);
        $o = Obavijest::create(['predmet' => 'P', 'naslov' => 'Novo', 'tekst' => '<b>Tekst</b>', 'user_id' => User::factory()->create()->id]);
        $u = User::factory()->create();
        $this->pretplata($u);
        (new PosaljiObavijest($o->id, $u->id))->handle();
        Mail::assertSent(ObavijestMail::class, 1);
        Http::assertSentCount(1);

        // odjavljen od mailova, ali ima push
        $u->forceFill(['obavijesti_odjava_at' => now()])->save();
        (new PosaljiObavijest($o->id, $u->id))->handle();
        Mail::assertSent(ObavijestMail::class, 1);
        Http::assertSentCount(2);

        $admin = User::factory()->create(['email' => 'slynetwork@gmail.com']);
        $this->assertTrue(Obavijest::primateljiSvihKanala()->whereKey($u->id)->exists());
        $bez = User::factory()->create();
        $bez->forceFill(['obavijesti_odjava_at' => now()])->save();
        $this->assertFalse(Obavijest::primateljiSvihKanala()->whereKey($bez->id)->exists());
        $this->assertTrue(Obavijest::primateljiSvihKanala()->whereKey($admin->id)->exists());
    }

    public function test_zvono_javlja_samo_nove_stavke_jednom(): void
    {
        Http::fake(['*' => Http::response('', 201)]);
        $u = User::factory()->create();
        $this->pretplata($u);
        $s = SkladisteModel::create(['user_id' => $u->id, 'naziv' => 'Glavno', 'zadano' => true]);
        SkladisteUlaz::create(['user_id' => $u->id, 'skladiste_id' => $s->id, 'vrsta' => 'szb', 'naziv' => 'Amistar', 'datum' => '2026-01-01', 'kolicina' => 0, 'jedinica' => 'L']);

        $this->assertSame(1, PushUpozorenja::posalji());
        Http::assertSentCount(1);
        $this->assertSame(0, PushUpozorenja::posalji());   // isto stanje: ne ponavlja se
        Http::assertSentCount(1);

        // popunjeno pa opet pri kraju: javlja se ponovno
        $ulaz = SkladisteUlaz::first();
        $ulaz->update(['kolicina' => 100]);
        $u = $u->fresh();
        $this->assertSame(0, PushUpozorenja::posalji());
        $this->assertSame(0, \DB::table('push_poslano')->count());
        $ulaz->update(['kolicina' => 0]);
        $this->assertSame(1, PushUpozorenja::posalji());
    }

    public function test_savjetnik_ne_moze_pretplatiti_uredjaj_na_tudje_gospodarstvo(): void
    {
        $vlasnik = User::factory()->create();
        $savjetnik = User::factory()->create();
        $p = Pristup::create(['vlasnik_id' => $vlasnik->id, 'email' => $savjetnik->email, 'savjetnik_id' => $savjetnik->id,
            'prava' => 'unos', 'token' => Pristup::noviToken(), 'status' => 'prihvaceno']);
        $this->actingAs($savjetnik)->post(route('klijenti.otvori', $p));
        $this->get('/dashboard')->assertOk()->assertDontSee('name="agro-push"', false);
        $this->actingAs($savjetnik)->postJson(route('push.spremi'), ['endpoint' => self::ENDPOINT, 'keys' => ['p256dh' => 'a', 'auth' => 'b']]);
        $this->assertSame(0, PushPretplata::count());
    }
}
