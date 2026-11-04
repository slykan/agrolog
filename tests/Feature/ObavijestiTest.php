<?php

namespace Tests\Feature;

use App\Jobs\PosaljiObavijest;
use App\Mail\ObavijestMail;
use App\Models\Obavijest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ObavijestiTest extends TestCase
{
    use RefreshDatabase;

    private array $podaci = [
        'predmet' => 'Novo u AgroLogu', 'naslov' => 'Parcele na karti',
        'tekst' => "Prvi odlomak.\n\nDrugi <b>odlomak</b>\ns novim redom.",
        'gumb_tekst' => 'Otvori AgroLog', 'gumb_url' => 'https://agro-log.app/novosti',
    ];

    private function admin(): User
    {
        return User::factory()->create(['email' => 'slynetwork@gmail.com']);
    }

    public function test_proba_ide_samo_adminu(): void
    {
        Mail::fake();
        Queue::fake();
        $admin = $this->admin();
        User::factory()->count(3)->create();

        $this->actingAs($admin)->post('/admin/obavijesti', $this->podaci + ['akcija' => 'proba'])->assertRedirect();

        Mail::assertSent(ObavijestMail::class, 1);
        Mail::assertSent(ObavijestMail::class, fn ($m) => $m->hasTo('slynetwork@gmail.com'));
        Queue::assertNothingPushed();
        $this->assertSame(0, Obavijest::count());
    }

    public function test_slanje_svima_osim_odjavljenih(): void
    {
        Queue::fake();
        $admin = $this->admin();
        User::factory()->count(10)->create();
        User::factory()->create(['obavijesti_odjava_at' => now()]);

        $this->actingAs($admin)->post('/admin/obavijesti', $this->podaci + ['akcija' => 'svima'])
            ->assertRedirect('/admin/obavijesti');

        $o = Obavijest::first();
        $this->assertSame(11, $o->primatelja);   // admin + 10, bez odjavljenog
        Queue::assertPushed(PosaljiObavijest::class, 11);
        // 8 u minuti: deveti mail ide minutu kasnije
        Queue::assertPushed(PosaljiObavijest::class, fn ($j) => $j->connection === 'database' && $j->delay !== null);
    }

    public function test_mail_izgleda_i_ima_odjavu(): void
    {
        $u = User::factory()->create(['naziv_gospodarstva' => 'OPG Ratar']);
        $html = (new ObavijestMail(new Obavijest($this->podaci), $u))->render();

        $this->assertStringContainsString('Pozdrav, <strong>OPG Ratar</strong>!', $html);
        $this->assertStringContainsString('Drugi &lt;b&gt;odlomak&lt;/b&gt;<br />', $html);   // escapano, novi red ostaje
        $this->assertStringContainsString('Otvori AgroLog', $html);
        $this->assertStringContainsString('/obavijesti/odjava/' . $u->id, $html);
    }

    public function test_odjava_potpisanim_linkom(): void
    {
        Mail::fake();
        $u = User::factory()->create();

        $this->get('/obavijesti/odjava/' . $u->id)->assertForbidden();   // bez potpisa
        $this->get(URL::signedRoute('obavijesti.odjava', ['user' => $u->id]))->assertOk()->assertSee('Odjavljeni ste');
        $this->assertNotNull($u->fresh()->obavijesti_odjava_at);

        // job ne šalje odjavljenom
        (new PosaljiObavijest(Obavijest::create($this->podaci)->id, $u->id))->handle();
        Mail::assertNothingSent();

        // jednoklik odjava (POST iz mail klijenta, bez CSRF tokena)
        $v = User::factory()->create();
        $this->post(URL::signedRoute('obavijesti.odjava', ['user' => $v->id]))->assertNoContent();
        $this->assertNotNull($v->fresh()->obavijesti_odjava_at);
    }

    public function test_samo_admin(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->get('/admin/obavijesti')->assertForbidden();
        $this->actingAs($u)->post('/admin/obavijesti', $this->podaci + ['akcija' => 'svima'])->assertForbidden();
        $this->actingAs($this->admin())->get('/admin/obavijesti')->assertOk()->assertSee('Pošalji svima');
    }
}
