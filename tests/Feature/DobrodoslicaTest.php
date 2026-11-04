<?php

namespace Tests\Feature;

use App\Mail\DobrodoslicaMail;
use App\Mail\NoviKorisnikMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DobrodoslicaTest extends TestCase
{
    use RefreshDatabase;

    public function test_registracija_salje_dobrodoslicu_korisniku_i_obavijest_adminu(): void
    {
        Mail::fake();
        Http::fake(["challenges.cloudflare.com/*" => Http::response(["success" => true])]); // Turnstile

        $this->post('/register', [
            'name' => 'Ivo Ratar',
            'email' => 'ivo@example.com',
            'naziv_gospodarstva' => 'OPG Ratar',
            'password' => 'lozinka-123',
            'password_confirmation' => 'lozinka-123',
        ]);

        $this->assertAuthenticated();
        Mail::assertSent(DobrodoslicaMail::class, fn ($m) => $m->hasTo('ivo@example.com'));
        Mail::assertSent(NoviKorisnikMail::class, fn ($m) => $m->hasTo('slynetwork@gmail.com'));
    }

    public function test_mail_dobrodoslice_se_iscrtava(): void
    {
        $u = User::factory()->create(['name' => 'Ivo Ratar', 'naziv_gospodarstva' => 'OPG Ratar', 'email' => 'ivo@example.com']);

        $html = (new DobrodoslicaMail($u))->render();

        $this->assertStringContainsString('Dobrodošli, OPG Ratar!', $html);
        $this->assertStringContainsString('ivo@example.com', $html);
        $this->assertStringContainsString('info@on-click.hr', $html);
        $this->assertStringContainsString(route('parcele'), $html);
    }
}
