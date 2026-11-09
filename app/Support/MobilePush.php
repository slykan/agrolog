<?php

namespace App\Support;

use App\Models\MobilePushDevice;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MobilePush
{
    public static function configured(): bool
    {
        return (bool) config('services.firebase.project_id')
            && is_file((string) config('services.firebase.credentials'));
    }

    public static function korisniku(User $user, string $naslov, string $tekst, string $url): int
    {
        if (! $user->push_obavijesti || ! self::configured()) {
            return 0;
        }
        $sent = 0;
        foreach ($user->mobilePushDevices()->get() as $device) {
            $sent += self::send($device, $naslov, $tekst, $url) ? 1 : 0;
        }

        return $sent;
    }

    private static function accessToken(): string
    {
        $credentials = json_decode(file_get_contents(config('services.firebase.credentials')), true, flags: JSON_THROW_ON_ERROR);
        $email = $credentials['client_email'] ?? '';
        $key = $credentials['private_key'] ?? '';
        if (($credentials['type'] ?? '') !== 'service_account' || ! str_ends_with($email, '.gserviceaccount.com')) {
            throw new \RuntimeException('Invalid Firebase service account');
        }
        $cacheKey = 'firebase-access-'.hash('sha256', $email.$key);

        return Cache::remember($cacheKey, 3000, function () use ($email, $key) {
            $encode = fn ($value) => rtrim(strtr(base64_encode(json_encode($value, JSON_THROW_ON_ERROR)), '+/', '-_'), '=');
            $jwt = $encode(['alg' => 'RS256', 'typ' => 'JWT']).'.'.$encode([
                'iss' => $email, 'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token', 'iat' => time(), 'exp' => time() + 3600,
            ]);
            if (! openssl_sign($jwt, $signature, $key, OPENSSL_ALGO_SHA256)) {
                throw new \RuntimeException('Firebase JWT signing failed');
            }
            $jwt .= '.'.rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
            $response = Http::timeout(10)->asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $jwt,
            ]);
            if (! $response->successful() || ! is_string($response->json('access_token'))) {
                throw new \RuntimeException('Firebase authentication failed');
            }

            return $response->json('access_token');
        });
    }

    private static function send(MobilePushDevice $device, string $title, string $body, string $url): bool
    {
        try {
            $project = config('services.firebase.project_id');
            if (! preg_match('/\A[a-z][a-z0-9-]{4,61}[a-z0-9]\z/', $project)) {
                return false;
            }
            // Only app links, never arbitrary origins, are put into a notification.
            $origin = rtrim(config('app.url'), '/');
            if (! str_starts_with($url, $origin.'/')) {
                $url = $origin.'/dashboard';
            }
            $response = Http::timeout(10)->withToken(self::accessToken())->post(
                'https://fcm.googleapis.com/v1/projects/'.$project.'/messages:send', ['message' => [
                    'token' => $device->token,
                    // Data-only allows the app to discard pending messages after logout/account changes.
                    'data' => ['user_id' => (string) $device->user_id, 'naslov' => Str::limit($title, 100),
                        'tekst' => Str::limit(strip_tags($body), 350), 'url' => $url],
                    'android' => ['priority' => 'high', 'ttl' => '86400s'],
                ]]);
            $unregistered = collect($response->json('error.details', []))->contains(
                fn ($detail) => ($detail['@type'] ?? '') === 'type.googleapis.com/google.firebase.fcm.v1.FcmError'
                    && ($detail['errorCode'] ?? '') === 'UNREGISTERED');
            if ($unregistered) {
                $device->delete();
            } elseif (! $response->successful()) {
                Log::warning('Mobile push delivery failed', ['status' => $response->status()]);
            }

            return $response->successful();
        } catch (\Throwable $e) {
            // Do not log credentials, tokens, payloads or upstream exception bodies.
            Log::warning('Mobile push could not be sent', ['error_type' => $e::class]);

            return false;
        }
    }
}
