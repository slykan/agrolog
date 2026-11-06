<?php

namespace Tests\Feature;

use App\Jobs\PosaljiObavijestPreporuke;
use App\Livewire\GnojidbaTable;
use App\Livewire\PrskanjeTable;
use App\Mail\PreporukaMail;
use App\Models\Gnojidba;
use App\Models\Kultura;
use App\Models\Parcela;
use App\Models\Preporuka;
use App\Models\Prskanje;
use App\Models\Pristup;
use App\Models\SzbSredstvo;
use App\Models\User;
use App\Support\Upozorenja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class PreporukeTest extends TestCase
{
    use RefreshDatabase;

    private function klijent(User $s, string $naziv, string $kultura, string $prava = 'pregled'): array
    {
        $v = User::factory()->create(['naziv_gospodarstva' => $naziv]);
        Pristup::create(['vlasnik_id' => $v->id, 'email' => $s->email, 'savjetnik_id' => $s->id, 'prava' => $prava, 'token' => Pristup::noviToken(), 'status' => 'prihvaceno', 'prihvaceno_at' => now()]);

        return [$v, $this->kultura($v, $kultura)];
    }

    private function kultura(User $v, string $naziv): Kultura
    {
        $p = Parcela::create(['user_id' => $v->id, 'arkod_broj' => (string) random_int(100000, 999999), 'povrsina_ha' => 3]);

        return Kultura::create(['user_id' => $v->id, 'parcela_id' => $p->id, 'naziv' => $naziv, 'posadjena_povrsina_ha' => 2.5, 'godina' => date('Y')]);
    }

    private function savjetnik(): User
    {
        return User::factory()->create(['name' => 'Ana Agronom', 'email' => 'ana@zadruga.hr', 'je_savjetnik' => true]);
    }

    private function podaci(array $kulture, array $viska = []): array
    {
        return ['vrsta' => 'gnojidba', 'kultura_ids' => array_map(fn ($k) => $k->id, $kulture), 'sredstvo' => 'KAN', 'doza' => 200, 'jedinica' => 'kg/ha', 'potvrda' => 1] + $viska;
    }

    private function preporuka(User $s, User $v, Kultura $k, array $viska = []): Preporuka
    {
        return Preporuka::create($viska + ['paket' => 'p1', 'savjetnik_id' => $s->id, 'savjetnik_ime' => $s->name, 'vlasnik_id' => $v->id, 'kultura_id' => $k->id, 'vrsta' => 'prskanje',
            'kultura_naziv' => $k->naziv, 'parcela_oznaka' => 'ARKOD 1', 'sredstvo' => 'Roundup', 'doza' => 2, 'jedinica' => 'l/ha']);
    }

    public function test_slanje_svima_s_kulturom_preskace_ostale_i_javlja_jednom_po_gospodarstvu(): void
    {
        Queue::fake();
        $s = $this->savjetnik();
        [$v1, $k1] = $this->klijent($s, 'OPG Horvat', 'Pšenica');
        $k1b = $this->kultura($v1, 'Pšenica');
        [$v2, $k2] = $this->klijent($s, 'OPG Kovač', 'Pšenica');
        [$v3] = $this->klijent($s, 'OPG Bez', 'Kukuruz');

        $this->actingAs($s)->get(route('klijenti.preporuke'))->assertOk()->assertSee('Nova preporuka');
        $this->actingAs($s)->post(route('klijenti.preporuke.posalji'), $this->podaci([$k1, $k1b, $k2]))->assertRedirect(route('klijenti.preporuke'));

        $this->assertSame(3, Preporuka::count());
        $this->assertSame(0, Preporuka::where('vlasnik_id', $v3->id)->count());
        $this->assertSame(1, Preporuka::pluck('paket')->unique()->count());
        Queue::assertPushed(PosaljiObavijestPreporuke::class, 2);
        Queue::assertPushed(PosaljiObavijestPreporuke::class, fn ($j) => $j->userId === $v1->id && count($j->stavke) === 2);
        // slanje ne dira evidenciju
        $this->assertSame(0, Gnojidba::count());
    }

    public function test_prvo_ide_pregled_a_salje_se_tek_nakon_potvrde(): void
    {
        Queue::fake();
        $s = $this->savjetnik();
        [, $k] = $this->klijent($s, 'OPG Horvat', 'Pšenica');
        $podaci = $this->podaci([$k]);
        unset($podaci['potvrda']);

        $this->actingAs($s)->post(route('klijenti.preporuke.posalji'), $podaci)->assertOk()->assertSee('Pregled prije slanja')->assertSee('OPG Horvat');

        $this->assertSame(0, Preporuka::count());
        Queue::assertNothingPushed();
    }

    public function test_pregled_upozorava_ako_sredstvo_nije_registrirano_ili_ne_postoji(): void
    {
        $s = $this->savjetnik();
        [, $k] = $this->klijent($s, 'OPG Horvat', 'Pšenica');
        $sr = SzbSredstvo::create(['fis_id' => 1, 'naziv' => 'SAMO VINOVA', 'registracijski_broj' => 'X', 'aktivno' => true]);
        $sr->forceFill(['detalji' => [['kultura' => 'Vinova loza', 'karenca_dana' => 10, 'karenca' => '10 dana', 'mala_namjena' => false, 'max' => null, 'primjene' => []]], 'detalji_at' => now()])->save();

        $this->actingAs($s)->post(route('klijenti.preporuke.posalji'), ['vrsta' => 'prskanje', 'kultura_ids' => [$k->id], 'sredstvo' => 'SAMO VINOVA'])
            ->assertOk()->assertSee('nije registriran za kulturu');
        $this->actingAs($s)->post(route('klijenti.preporuke.posalji'), ['vrsta' => 'prskanje', 'kultura_ids' => [$k->id], 'sredstvo' => 'Izmisljeno'])
            ->assertOk()->assertSee('nije pronađeno u registru');
    }

    public function test_ne_moze_se_poslati_tudjem_gospodarstvu_ili_bez_pristupa(): void
    {
        Queue::fake();
        $s = $this->savjetnik();
        [, $moja] = $this->klijent($s, 'OPG Moj', 'Pšenica');
        $tudji = User::factory()->create();
        $tudja = $this->kultura($tudji, 'Pšenica');

        // tuđa kultura i tuđe gospodarstvo tiho otpadaju, a sam se ne može slati nikome
        $this->actingAs($s)->post(route('klijenti.preporuke.posalji'), $this->podaci([$tudja]))->assertSessionHasErrors('kultura_ids');
        $this->actingAs($s)->post(route('klijenti.preporuke.posalji'), $this->podaci([$tudja, $moja]))->assertRedirect();
        $this->assertSame([$moja->id], Preporuka::pluck('kultura_id')->all());
        $this->actingAs($s)->post(route('klijenti.preporuke.posalji'), ['vrsta' => 'poruka', 'vlasnik_ids' => [$tudji->id], 'napomena' => 'Bok', 'potvrda' => 1])->assertSessionHasErrors('kultura_ids');

        $this->actingAs(User::factory()->create())->post(route('klijenti.preporuke.posalji'), $this->podaci([$moja]))->assertForbidden();
    }

    public function test_opca_poruka_ide_svima_bez_kulture_i_bez_unosa(): void
    {
        Queue::fake();
        $s = $this->savjetnik();
        [$v1] = $this->klijent($s, 'OPG A', 'Pšenica');
        [$v2] = $this->klijent($s, 'OPG B', 'Kukuruz');

        $this->actingAs($s)->post(route('klijenti.preporuke.posalji'), ['vrsta' => 'poruka', 'vlasnik_ids' => [$v1->id, $v2->id], 'napomena' => 'Sutra jak vjetar', 'sredstvo' => '', 'kultura_ids' => [], 'potvrda' => 1])
            ->assertRedirect(route('klijenti.preporuke'));

        $this->assertSame(2, Preporuka::where('vrsta', 'poruka')->whereNull('kultura_id')->count());
        Queue::assertPushed(PosaljiObavijestPreporuke::class, 2);
        $this->actingAs($v1)->get(route('preporuke.index'))->assertOk()->assertSee('Sutra jak vjetar')->assertSee('Primljeno')->assertDontSee('Ne prihvaćam');
    }

    public function test_prihvacanje_ne_upisuje_u_evidenciju_a_unos_zatvara_preporuku(): void
    {
        Queue::fake();
        $s = $this->savjetnik();
        [$v, $k] = $this->klijent($s, 'OPG Horvat', 'Pšenica');
        $p = $this->preporuka($s, $v, $k);

        $this->actingAs($v)->get(route('preporuke.index'))->assertOk()->assertSee('Prihvaćam')->assertDontSee('unesi u evidenciju');
        $this->actingAs($v)->post(route('preporuke.odgovor', $p), ['radnja' => 'prihvati'])->assertRedirect();
        $this->assertSame('prihvaceno', $p->fresh()->status);
        $this->assertSame(0, Prskanje::count());
        Queue::assertPushed(PosaljiObavijestPreporuke::class, fn ($j) => $j->userId === $s->id && str_contains($j->predmet, 'prihvaćeno'));
        $this->actingAs($v)->get(route('preporuke.index'))->assertSee('unesi u evidenciju');

        // unos je popunjen, a preporuka je otvorena dok se ne spremi
        $this->actingAs($v);
        request()->merge(['preporuka' => $p->id]);
        $lw = Livewire::withQueryParams(['preporuka' => $p->id])->test(PrskanjeTable::class)
            ->assertSet('showForm', true)->assertSet('form.kultura_id', $k->id)->assertSet('form.trgovacki_naziv_sredstva', 'Roundup')
            ->assertSet('form.kolicina_sredstva_l_ha', 2.0)->assertSet('form.tretirana_povrsina_ha', '2.50');
        $this->assertSame('prihvaceno', $p->fresh()->status);

        $lw->set('form.datum_tretiranja', date('Y-m-d'))->set('form.kolicina_sredstva_l_ha', 1.5)->call('saveNew')->assertHasNoErrors();

        $zapis = Prskanje::firstOrFail();
        $this->assertEquals(1.5, (float) $zapis->kolicina_sredstva_l_ha);   // vrijedi ono što je stvarno upisano
        $this->assertSame('napravljeno', $p->fresh()->status);
        $this->assertSame($zapis->id, $p->fresh()->zapis_id);
    }

    public function test_gnojidba_se_popunjava_iz_preporuke(): void
    {
        Queue::fake();
        $s = $this->savjetnik();
        [$v, $k] = $this->klijent($s, 'OPG Horvat', 'Pšenica');
        $p = $this->preporuka($s, $v, $k, ['vrsta' => 'gnojidba', 'sredstvo' => 'KAN', 'doza' => 200, 'jedinica' => 'kg/ha']);

        $this->actingAs($v);
        Livewire::withQueryParams(['preporuka' => $p->id])->test(GnojidbaTable::class)
            ->assertSet('form.kultura_id', $k->id)->assertSet('form.tip_gnojiva', 'KAN')->assertSet('form.kolicina_kg_ha', 200.0)
            ->call('saveNew')->assertHasNoErrors();

        $this->assertSame(1, Gnojidba::count());
        $this->assertSame('napravljeno', $p->fresh()->status);
    }

    public function test_tudja_ili_zatvorena_preporuka_ne_popunjava_unos(): void
    {
        $s = $this->savjetnik();
        [$v, $k] = $this->klijent($s, 'OPG Horvat', 'Pšenica');
        $tudja = $this->preporuka($s, $v, $k);
        $drugi = User::factory()->create();

        $this->actingAs($drugi);
        Livewire::withQueryParams(['preporuka' => $tudja->id])->test(PrskanjeTable::class)->assertSet('preporukaId', null)->assertSet('showForm', false);
        $this->actingAs($drugi)->post(route('preporuke.odgovor', $tudja), ['radnja' => 'prihvati'])->assertNotFound();

        $tudja->update(['status' => 'odbijeno']);
        $this->actingAs($v);
        Livewire::withQueryParams(['preporuka' => $tudja->id])->test(PrskanjeTable::class)->assertSet('preporukaId', null);
    }

    public function test_pitanje_i_odgovor_savjetnika(): void
    {
        Queue::fake();
        $s = $this->savjetnik();
        [$v, $k] = $this->klijent($s, 'OPG Horvat', 'Pšenica');
        $p = $this->preporuka($s, $v, $k);

        $this->actingAs($v)->post(route('preporuke.odgovor', $p), ['radnja' => 'pitanje'])->assertSessionHasErrors('tekst');
        $this->actingAs($v)->post(route('preporuke.odgovor', $p), ['radnja' => 'pitanje', 'tekst' => 'Može li sutra?']);
        $this->assertSame('pitanje', $p->fresh()->status);

        // odgovara samo onaj koji je poslao
        $this->actingAs(User::factory()->create())->post(route('klijenti.preporuke.odgovori', $p), ['odgovor' => 'x'])->assertNotFound();
        $this->actingAs($s)->get(route('klijenti.preporuke'))->assertSee('Može li sutra?');
        $this->actingAs($s)->post(route('klijenti.preporuke.odgovori', $p), ['odgovor' => 'Može.'])->assertRedirect();

        $this->assertSame('poslano', $p->fresh()->status);
        Queue::assertPushed(PosaljiObavijestPreporuke::class, fn ($j) => $j->userId === $v->id && str_contains($j->stavke[0], 'Može.'));
        $this->actingAs($v)->get(route('preporuke.index'))->assertSee('Može.');
    }

    public function test_nedozvoljeni_prijelazi_i_povlacenje(): void
    {
        Queue::fake();
        $s = $this->savjetnik();
        [$v, $k] = $this->klijent($s, 'OPG Horvat', 'Pšenica');
        $p = $this->preporuka($s, $v, $k);

        $this->actingAs($v)->post(route('preporuke.odgovor', $p), ['radnja' => 'nije'])->assertNotFound();
        $this->actingAs($s)->post(route('klijenti.preporuke.povuci', $p))->assertRedirect();
        $this->assertSame('povuceno', $p->fresh()->status);
        $this->actingAs($v)->post(route('preporuke.odgovor', $p), ['radnja' => 'prihvati'])->assertNotFound();
        $this->actingAs($v)->get(route('preporuke.index'))->assertDontSee('Prihvaćam');
    }

    public function test_zvono_i_obavijesti_prema_postavkama(): void
    {
        Mail::fake();
        $s = $this->savjetnik();
        [$v, $k] = $this->klijent($s, 'OPG Horvat', 'Pšenica');
        $p = $this->preporuka($s, $v, $k);

        $this->assertTrue(Upozorenja::za($v->fresh())->contains(fn ($u) => $u['kljuc'] === 'preporuka-' . $p->id));

        // preporuka se ne broji kao nešto što traži pažnju u registru klijenta
        $k->forceFill(['eppo_kod' => 'TRZAX'])->save();
        $this->actingAs($s)->get(route('klijenti'))->assertOk()->assertSee('sve u redu')->assertSee('Pošalji preporuku');

        (new PosaljiObavijestPreporuke($v->id, 'P', 'N', 'U', ['S'], route('preporuke.index'), 'G', $s->email))->handle();
        Mail::assertSent(PreporukaMail::class, fn ($m) => $m->hasTo($v->email));
        $this->assertStringContainsString('Tim AgroLog', (new PreporukaMail('P', 'N', 'U', ['S'], 'https://x.example', 'G'))->render());

        Mail::fake();
        $v->forceFill(['obavijesti_odjava_at' => now()])->save();
        (new PosaljiObavijestPreporuke($v->id, 'P', 'N', 'U', ['S'], route('preporuke.index'), 'G'))->handle();
        Mail::assertNothingSent();
    }

    public function test_savjetnik_u_tudjem_gospodarstvu_ne_moze_odgovarati_ni_vidjeti_u_zvonu(): void
    {
        Queue::fake();
        $s = $this->savjetnik();
        [$v, $k] = $this->klijent($s, 'OPG Horvat', 'Pšenica');
        $p = $this->preporuka($s, $v, $k);
        $pristup = Pristup::where('vlasnik_id', $v->id)->firstOrFail();

        $this->actingAs($s)->post(route('klijenti.otvori', $pristup));
        $this->post(route('preporuke.odgovor', $p), ['radnja' => 'prihvati'])->assertRedirect(route('dashboard'));
        $this->assertSame('poslano', $p->fresh()->status);
        $this->get(route('preporuke.index'))->assertRedirect(route('dashboard'));

        // i sa "samo pregled" može poslati preporuku drugom klijentu iz tuđeg gospodarstva
        [, $k2] = $this->klijent($s, 'OPG Drugi', 'Kukuruz');
        $this->post(route('klijenti.preporuke.posalji'), $this->podaci([$k2]))->assertRedirect(route('klijenti.preporuke'));
        $this->assertSame(2, Preporuka::count());
    }

    public function test_dnevni_limit_slanja(): void
    {
        Queue::fake();
        $s = $this->savjetnik();
        [, $k] = $this->klijent($s, 'OPG Horvat', 'Pšenica');
        for ($i = 0; $i < 30; $i++) {
            RateLimiter::hit('preporuke:' . $s->id, 86400);
        }

        $this->actingAs($s)->post(route('klijenti.preporuke.posalji'), $this->podaci([$k]))->assertRedirect(route('klijenti.preporuke'));
        $this->assertSame(0, Preporuka::count());
    }

    public function test_novost_postoji_i_prva_je_na_popisu(): void
    {
        $this->assertSame('preporuke-savjetnika', \App\Http\Controllers\SadrzajController::novosti()[0]['slug']);
        $this->get(route('novost', 'preporuke-savjetnika'))->assertOk()->assertSee('Evidencija ostaje točna');
    }

    public function test_upute_opisuju_preporuke(): void
    {
        $this->get(route('docs'))->assertOk()->assertSee('id="preporuke"', false)->assertSee('href="#preporuke"', false)->assertSee('Napravljeno, unesi u evidenciju');
    }
}
