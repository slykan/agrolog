<?php

namespace Tests\Feature;

use App\Http\Controllers\PonudaController;
use App\Mail\PonudaOglasivacuMail;
use App\Models\PonudaOglasivacu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PonudeTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['email' => 'slynetwork@gmail.com']);
    }

    private function podaci(array $p = []): array
    {
        return $p + ['tvrtka' => 'Gnojivo d.o.o.', 'email' => 'Prodaja@Gnojivo.example', 'proizvodi' => 'gnojiva', 'predmet' => PonudaController::PREDMET,
            'tekst' => PonudaController::TEKST, 'akcija' => 'posalji'];
    }

    public function test_slanje_upisuje_u_zapisnik_s_datumom_i_vremenom(): void
    {
        Mail::fake();
        $this->travelTo('2026-11-06 14:30:00', );
        $this->actingAs($this->admin())->get('/admin/ponude')->assertOk()->assertSee('Još ništa nije poslano')->assertSee('{tvrtka}');

        $this->post('/admin/ponude', $this->podaci())->assertRedirect(route('admin.ponude'))->assertSessionHas('poruka');
        Mail::assertSent(PonudaOglasivacuMail::class, fn ($m) => $m->hasTo('prodaja@gnojivo.example') && $m->hasSubject(PonudaController::PREDMET)
            && str_contains($m->tekst, 'Mislim da bi Gnojivo d.o.o. bio') && str_contains($m->tekst, 'jer su gnojiva upravo') && ! str_contains($m->tekst, '{tvrtka}'));

        $z = PonudaOglasivacu::firstOrFail();
        $this->assertSame('prodaja@gnojivo.example', $z->email);
        // prikaz po našem vremenu
        $this->get('/admin/ponude')->assertSee('06.11.2026. 15:30')->assertSee('Gnojivo d.o.o.')->assertSee('prodaja@gnojivo.example');
    }

    public function test_proba_ide_samo_meni_i_ne_upisuje_se(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/ponude', $this->podaci(['akcija' => 'proba']))->assertRedirect(route('admin.ponude'));
        Mail::assertSent(PonudaOglasivacuMail::class, fn ($m) => $m->hasTo($admin->email));
        Mail::assertNotSent(PonudaOglasivacuMail::class, fn ($m) => $m->hasTo('prodaja@gnojivo.example'));
        $this->assertSame(0, PonudaOglasivacu::count());
        $this->assertSame('Gnojivo d.o.o.', session()->getOldInput('tvrtka'));   // unos ostaje za pravo slanje
    }

    public function test_upozorenje_kod_ponovnog_slanja(): void
    {
        Mail::fake();
        $this->actingAs($this->admin());
        $this->post('/admin/ponude', $this->podaci());
        $this->from('/admin/ponude')->post('/admin/ponude', $this->podaci())->assertRedirect('/admin/ponude')->assertSessionHas('vec_poslano');
        Mail::assertSent(PonudaOglasivacuMail::class, 1);
        $this->assertSame(1, PonudaOglasivacu::count());

        $this->post('/admin/ponude', $this->podaci(['ipak' => '1']));
        Mail::assertSent(PonudaOglasivacuMail::class, 2);
        $this->assertSame(2, PonudaOglasivacu::count());
    }

    public function test_ne_zeli_poruke_blokira_i_oznaku_i_ipak(): void
    {
        Mail::fake();
        $this->actingAs($this->admin());
        $this->post('/admin/ponude', $this->podaci());
        $z = PonudaOglasivacu::firstOrFail();
        $this->post(route('admin.ponude.ne-zeli', $z))->assertRedirect();

        $this->from('/admin/ponude')->post('/admin/ponude', $this->podaci(['ipak' => '1']))->assertSessionHasErrors('email');
        Mail::assertSent(PonudaOglasivacuMail::class, 1);
        $this->get('/admin/ponude')->assertSee('ne želi poruke');

        // greškom označeno: može se poništiti
        $this->post(route('admin.ponude.ne-zeli', $z));
        $this->post('/admin/ponude', $this->podaci(['ipak' => '1']));
        Mail::assertSent(PonudaOglasivacuMail::class, 2);
    }

    public function test_neuspjelo_slanje_se_ne_upisuje(): void
    {
        $this->actingAs($this->admin());
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP'));
        $this->from('/admin/ponude')->post('/admin/ponude', $this->podaci())->assertSessionHas('poruka_greska')->assertSessionHasInput('tvrtka');
        $this->assertSame(0, PonudaOglasivacu::count());
    }

    public function test_mail_ima_gumb_i_mogucnost_odbijanja_i_siguran_je(): void
    {
        $html = (new PonudaOglasivacuMail('Naslov', "Red <b>1</b>\nRed 2"))->render();
        $this->assertStringContainsString(route('oglasavanje'), $html);
        $this->assertStringContainsString('Ako ne želite ovakve poruke', $html);
        $this->assertStringContainsString(route('privacy'), $html);
        $this->assertStringNotContainsString('<b>1</b>', $html);
    }

    public function test_samo_admin(): void
    {
        Mail::fake();
        $this->actingAs(User::factory()->create());
        $this->get('/admin/ponude')->assertForbidden();
        $this->post('/admin/ponude', $this->podaci())->assertForbidden();
        Mail::assertNothingSent();
    }
}
