<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AplikacijaPwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_i_service_worker(): void
    {
        $m = json_decode(file_get_contents(public_path('manifest.webmanifest')), true);
        $this->assertSame('standalone', $m['display']);
        $this->assertStringStartsWith('/dashboard', $m['start_url']);
        foreach ($m['icons'] as $ikona) {
            $this->assertFileExists(public_path(ltrim($ikona['src'], '/')));
        }
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('offline.html'));
    }

    public function test_stranice_imaju_manifest_a_aplikacija_traku_za_instalaciju(): void
    {
        $this->get('/')->assertOk()->assertSee('rel="manifest"', false)->assertDontSee('Instalirajte agro-log.app');
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertOk()
            ->assertSee('rel="manifest"', false)->assertSee('Instalirajte agro-log.app');
    }
}
