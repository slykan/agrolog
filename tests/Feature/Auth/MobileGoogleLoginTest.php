<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Support\MobileGoogleLogin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class MobileGoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    private function pending(string $state): array
    {
        return ['mobile_google_pending' => ['state' => $state, 'expires_at' => now()->addMinutes(10)->timestamp]];
    }

    private function mockGoogle(User $user): void
    {
        $google = (new GoogleUser)->map([
            'id' => $user->google_id, 'email' => $user->email, 'name' => $user->name, 'avatar' => null,
        ]);
        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($google);
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);
    }

    public function test_prepare_generates_its_own_state_and_signed_browser_url(): void
    {
        $response = $this->get('/auth/mobile/google?state=attacker&savjetnik=1');
        $response->assertOk()->assertViewIs('auth.mobile-google-prepare');
        $state = session('mobile_google_pending.state');
        $this->assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $state);
        $this->assertNotSame('attacker', $state);
        $url = $response->viewData('launch');
        $this->assertStringContainsString('signature=', $url);
        $this->assertStringContainsString('savjetnik=1', $url);
        $this->get(str_replace('savjetnik=1', 'savjetnik=0', $url))->assertForbidden();
    }

    public function test_launch_uses_existing_google_provider_and_preserves_mobile_state(): void
    {
        $state = bin2hex(random_bytes(32));
        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('redirect')->once()->andReturn(redirect('https://accounts.google.com/'));
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);
        $url = URL::temporarySignedRoute('auth.mobile.google.launch', now()->addMinutes(10), [
            'state' => $state, 'savjetnik' => 1,
        ]);
        $this->get($url)->assertRedirect('https://accounts.google.com/')
            ->assertSessionHas('mobile_google_browser_state', $state)->assertSessionHas('registracija_savjetnik', true);
    }

    public function test_mobile_google_callback_issues_short_lived_handoff_without_logging_browser_in(): void
    {
        $user = User::factory()->create(['google_id' => 'google-123']);
        $this->mockGoogle($user);
        $state = bin2hex(random_bytes(32));
        $response = $this->withSession(['mobile_google_browser_state' => $state])->get('/auth/google/callback');
        $response->assertOk()->assertViewIs('auth.mobile-google-finish')->assertSee('Vrati se u AgroLog');
        $code = $response->viewData('code');
        $this->assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $code);
        $this->assertGuest();
        $this->assertDatabaseHas('mobile_google_logins', [
            'code_hash' => hash('sha256', $code), 'state_hash' => hash('sha256', $state), 'user_id' => $user->id,
        ]);
        $this->assertDatabaseMissing('mobile_google_logins', ['code_hash' => $code]);
        $response->assertSessionMissing('mobile_google_browser_state');
    }

    public function test_original_web_google_login_still_logs_in_and_redirects(): void
    {
        $user = User::factory()->create(['google_id' => 'google-web']);
        $this->mockGoogle($user);
        $this->get('/auth/google/callback')->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('mobile_google_logins', 0);
    }

    public function test_mobile_google_links_existing_email_without_creating_duplicate_account(): void
    {
        $user = User::factory()->create(['google_id' => null]);
        $googleIdentity = clone $user;
        $googleIdentity->google_id = 'new-google-id';
        $this->mockGoogle($googleIdentity);
        $this->withSession(['mobile_google_browser_state' => bin2hex(random_bytes(32))])
            ->get('/auth/google/callback')->assertOk()->assertViewIs('auth.mobile-google-finish');
        $this->assertDatabaseCount('users', 1);
        $this->assertSame('new-google-id', $user->fresh()->google_id);
        $this->assertGuest();
    }

    public function test_new_google_user_can_register_as_advisor_and_return_to_app(): void
    {
        Mail::fake();
        $identity = new User(['name' => 'Google korisnik', 'email' => 'novi@example.test', 'google_id' => 'new-id']);
        $this->mockGoogle($identity);
        $state = bin2hex(random_bytes(32));
        $response = $this->withSession(['mobile_google_browser_state' => $state, 'registracija_savjetnik' => true])
            ->get('/auth/google/callback')->assertOk();
        $user = User::where('email', $identity->email)->sole();
        $this->assertTrue($user->je_savjetnik);
        $this->assertGuest();
        $this->withSession($this->pending($state))->post('/auth/mobile/google/complete', [
            'code' => $response->viewData('code'), 'state' => $state,
        ])->assertRedirect('/klijenti');
        $this->assertAuthenticatedAs($user);
    }

    public function test_complete_requires_original_session_and_consumes_code_only_once(): void
    {
        $user = User::factory()->create();
        $state = bin2hex(random_bytes(32));
        $code = MobileGoogleLogin::issue($user, $state);
        $this->post('/auth/mobile/google/complete', compact('code', 'state'))->assertStatus(419);
        $this->assertDatabaseCount('mobile_google_logins', 1);
        $this->withSession($this->pending(bin2hex(random_bytes(32))))
            ->post('/auth/mobile/google/complete', compact('code', 'state'))->assertStatus(419);
        $this->assertGuest();
        $this->withSession($this->pending($state))->post('/auth/mobile/google/complete', compact('code', 'state'))
            ->assertRedirect('/dashboard')->assertSessionMissing('mobile_google_pending');
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('mobile_google_logins', 0);
        $this->post('/logout');
        $this->withSession($this->pending($state))->post('/auth/mobile/google/complete', compact('code', 'state'))->assertStatus(419);
        $this->assertGuest();
    }

    public function test_expired_code_and_expired_session_are_rejected(): void
    {
        $state = bin2hex(random_bytes(32));
        $code = MobileGoogleLogin::issue(User::factory()->create(), $state);
        $this->travel(61)->seconds();
        $this->withSession($this->pending($state))->post('/auth/mobile/google/complete', compact('code', 'state'))->assertStatus(419);
        $this->assertGuest();
        $code = MobileGoogleLogin::issue(User::factory()->create(), $state);
        $this->withSession(['mobile_google_pending' => ['state' => $state, 'expires_at' => now()->subSecond()->timestamp]])
            ->post('/auth/mobile/google/complete', compact('code', 'state'))->assertStatus(419);
        $this->assertGuest();
    }

    public function test_bad_code_does_not_consume_valid_handoff(): void
    {
        $state = bin2hex(random_bytes(32));
        MobileGoogleLogin::issue(User::factory()->create(), $state);
        $this->withSession($this->pending($state))->post('/auth/mobile/google/complete', [
            'code' => str_repeat('0', 64), 'state' => $state,
        ])->assertStatus(419);
        $this->assertDatabaseCount('mobile_google_logins', 1);
        $this->assertGuest();
    }

    public function test_bridge_requires_pending_session_and_keeps_code_out_of_http_urls(): void
    {
        $this->get('/auth/mobile/google/return')->assertStatus(419);
        $this->withSession($this->pending(bin2hex(random_bytes(32))))->get('/auth/mobile/google/return')
            ->assertOk()->assertSee('name="_token"', false)->assertSee('location.hash', false);
    }

    public function test_cancelled_or_invalid_google_state_does_not_issue_login(): void
    {
        $this->withSession(['mobile_google_browser_state' => bin2hex(random_bytes(32))])
            ->get('/auth/google/callback?error=access_denied')->assertOk()->assertViewHas('code', null);
        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('user')->once()->andThrow(new InvalidStateException);
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);
        $this->withSession(['mobile_google_browser_state' => bin2hex(random_bytes(32))])
            ->get('/auth/google/callback')->assertOk()->assertViewHas('code', null);
        $this->assertGuest();
        $this->assertSame(0, DB::table('mobile_google_logins')->count());
    }
}
