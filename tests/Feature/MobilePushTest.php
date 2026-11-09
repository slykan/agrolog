<?php

namespace Tests\Feature;

use App\Models\MobilePushDevice;
use App\Models\Obavijest;
use App\Models\User;
use App\Support\MobilePush;
use App\Support\WebPush;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MobilePushTest extends TestCase
{
    use RefreshDatabase;

    private ?string $credentialsFile = null;
    private string $publicKey = '';

    protected function tearDown(): void
    {
        if ($this->credentialsFile) unlink($this->credentialsFile);
        parent::tearDown();
    }

    private function configure(): void
    {
        $cnf = tempnam(sys_get_temp_dir(), 'agro-cnf');
        file_put_contents($cnf, "[req]\ndistinguished_name=r\n[r]\n");
        try {
            $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA, 'config' => $cnf]);
            openssl_pkey_export($key, $pem, null, ['config' => $cnf]);
            $this->publicKey = openssl_pkey_get_details($key)['key'];
        } finally { unlink($cnf); }
        $this->credentialsFile = tempnam(sys_get_temp_dir(), 'agro-fcm');
        file_put_contents($this->credentialsFile, json_encode([
            'type' => 'service_account', 'client_email' => 'test@agrolog-test.iam.gserviceaccount.com', 'private_key' => $pem,
        ]));
        config(['services.firebase.project_id' => 'agrolog-test', 'services.firebase.credentials' => $this->credentialsFile]);
    }

    private function device(User $user, string $token = 'test-token-long-enough', string $session = 'another-session'): MobilePushDevice
    {
        return MobilePushDevice::create(['user_id' => $user->id, 'token' => $token,
            'token_hash' => hash('sha256', $token), 'session_hash' => hash('sha256', $session)]);
    }

    public function test_registration_authentication_token_reassignment_and_encryption(): void
    {
        $token = 'a-valid-looking-token-with-at-least-20-characters';
        $this->postJson(route('push.mobile.register'), compact('token'))->assertUnauthorized();
        $first = User::factory()->create();
        $second = User::factory()->create();
        $this->actingAs($first)->postJson(route('push.mobile.register'), compact('token'))->assertOk();
        $this->actingAs($second)->postJson(route('push.mobile.register'), compact('token'))->assertOk();
        $this->assertDatabaseCount('mobile_push_devices', 1);
        $device = MobilePushDevice::sole();
        $this->assertSame($second->id, $device->user_id);
        $this->assertSame($token, $device->token);
        $this->assertNotSame($token, DB::table('mobile_push_devices')->value('token'));
        $this->assertArrayNotHasKey('token', $device->toArray());
        $second->forceFill(['push_obavijesti' => false])->save();
        $this->actingAs($second)->postJson(route('push.mobile.register'), compact('token'))->assertForbidden();
    }

    public function test_rotation_replaces_old_token_for_same_session(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson(route('push.mobile.register'), ['token' => str_repeat('a', 30)])->assertOk();
        $this->withCredentials()->withCookie(config('session.cookie'), $response->getCookie(config('session.cookie'))->getValue());
        $this->postJson(route('push.mobile.register'), ['token' => str_repeat('b', 30)])->assertOk();
        $this->assertDatabaseCount('mobile_push_devices', 1);
        $this->assertSame(str_repeat('b', 30), MobilePushDevice::sole()->token);
    }

    public function test_logout_removes_only_current_session_device(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson(route('push.mobile.register'), ['token' => str_repeat('a', 30)])->assertOk();
        $this->withCookie(config('session.cookie'), $response->getCookie(config('session.cookie'))->getValue());
        $other = $this->device($user, str_repeat('b', 30));
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->assertDatabaseCount('mobile_push_devices', 1);
        $this->assertSame($other->id, MobilePushDevice::sole()->id);
    }

    public function test_existing_dispatch_sends_signed_fcm_request_with_user_and_internal_target(): void
    {
        $this->configure();
        $user = User::factory()->create();
        $device = $this->device($user);
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'fake-access-token']),
            'fcm.googleapis.com/*' => Http::response(['name' => 'projects/agrolog-test/messages/test']),
        ]);
        $this->assertSame(1, WebPush::korisniku($user, 'Naslov', 'Tekst', 'https://evil.test/'));
        Http::assertSent(function ($request) use ($device, $user) {
            return $request->url() === 'https://fcm.googleapis.com/v1/projects/agrolog-test/messages:send'
                && $request['message']['token'] === $device->token
                && $request['message']['data']['user_id'] === (string) $user->id
                && $request['message']['data']['url'] === rtrim(config('app.url'), '/').'/dashboard'
                && ! isset($request['message']['notification'])
                && $request->hasHeader('Authorization', 'Bearer fake-access-token');
        });
        Http::assertSent(function ($request) {
            if ($request->url() !== 'https://oauth2.googleapis.com/token') return false;
            [$header, $payload, $signature] = explode('.', $request['assertion']);
            $signature = base64_decode(strtr($signature, '-_', '+/'));
            return openssl_verify($header.'.'.$payload, $signature, $this->publicKey, OPENSSL_ALGO_SHA256) === 1;
        });
    }

    public function test_disabled_or_unconfigured_push_makes_no_network_requests(): void
    {
        Http::preventStrayRequests();
        $user = User::factory()->create();
        $this->device($user);
        config(['services.firebase.project_id' => null]);
        $this->assertSame(0, MobilePush::korisniku($user, 'N', 'T', route('dashboard')));
        $this->configure();
        $user->forceFill(['push_obavijesti' => false])->save();
        $this->assertSame(0, WebPush::korisniku($user, 'N', 'T', route('dashboard')));
        Http::assertNothingSent();
    }

    public function test_fcm_unregistered_removes_device_but_project_errors_do_not(): void
    {
        $this->configure();
        $user = User::factory()->create();
        $device = $this->device($user);
        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'fake-access-token']),
            'fcm.googleapis.com/*' => Http::response(['error' => ['status' => 'NOT_FOUND']], 404),
        ]);
        $this->assertSame(0, MobilePush::korisniku($user, 'N', 'T', route('dashboard')));
        $this->assertModelExists($device);
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::fake(['fcm.googleapis.com/*' => Http::response(['error' => ['details' => [[
            '@type' => 'type.googleapis.com/google.firebase.fcm.v1.FcmError', 'errorCode' => 'UNREGISTERED',
        ]]]], 404)]);
        $this->assertSame(0, MobilePush::korisniku($user, 'N', 'T', route('dashboard')));
        $this->assertDatabaseCount('mobile_push_devices', 0);
    }

    public function test_mobile_only_user_is_included_in_existing_notification_recipients(): void
    {
        $user = User::factory()->create(['obavijesti_odjava_at' => now()]);
        $this->device($user);
        $this->assertTrue(Obavijest::primateljiSvihKanala()->whereKey($user->id)->exists());
        $user->forceFill(['push_obavijesti' => false])->save();
        $this->assertFalse(Obavijest::primateljiSvihKanala()->whereKey($user->id)->exists());
    }

    public function test_native_profile_has_mobile_test_and_does_not_show_browser_permission_instructions(): void
    {
        $this->actingAs(User::factory()->create())->withHeader('User-Agent', 'AgroLogNative/0.4.0 Android')
            ->get('/profile')->assertOk()->assertSee('Obavijesti na mobitelu')
            ->assertSee(route('push.mobile.test'))->assertDontSee('Ovaj preglednik ne podržava push obavijesti.');
    }
}
