<?php

namespace App\Support;

use App\Models\PushPretplata;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Web push bez dodatnih paketa: šifriranje po RFC 8291 (aes128gcm) i VAPID po RFC 8292, samo s OpenSSL-om.
 * VAPID ključevi nastaju pri prvoj upotrebi i čuvaju se u storage/app/private/vapid.json (izvan gita, ostaju kroz deploy).
 */
class WebPush
{
    /** Samo poznati push servisi preglednika: endpoint šalje klijent, pa server ne smije zvati proizvoljne adrese. */
    private const SERVISI = ['fcm.googleapis.com', 'updates.push.services.mozilla.com', 'push.services.mozilla.com', 'web.push.apple.com'];
    private const SERVISI_SUFIKS = ['.notify.windows.com', '.push.apple.com', '.push.services.mozilla.com'];

    /** Prefiks DER zapisa javnog P-256 ključa (SubjectPublicKeyInfo) ispred 65 bajtova ključa. */
    private const SPKI_P256 = '3059301306072a8648ce3d020106082a8648ce3d030107034200';

    private static ?array $kljucevi = null;

    public static function b64(string $s): string
    {
        return rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
    }

    public static function b64d(string $s): string
    {
        return (string) base64_decode(strtr($s, '-_', '+/'), true);
    }

    public static function dozvoljenEndpoint(string $url): bool
    {
        $d = parse_url($url);
        if (($d['scheme'] ?? '') !== 'https' || empty($d['host']) || isset($d['user']) || isset($d['port'])) {
            return false;
        }
        $host = strtolower($d['host']);
        if (in_array($host, self::SERVISI, true)) {
            return true;
        }
        foreach (self::SERVISI_SUFIKS as $s) {
            if (str_ends_with($host, $s)) {
                return true;
            }
        }

        return false;
    }

    /** Novi P-256 par: [privatni PEM, javni 65 bajtova]. Prazna konfiguracija jer OpenSSL na Windowsu inače ne nađe svoju. */
    private static function noviPar(): array
    {
        $cnf = tempnam(sys_get_temp_dir(), 'agro');
        file_put_contents($cnf, "[req]\ndistinguished_name=r\n[r]\n");
        try {
            $k = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC, 'config' => $cnf]);
            if (! $k || ! openssl_pkey_export($k, $pem, null, ['config' => $cnf])) {
                throw new \RuntimeException('OpenSSL: ključ nije izrađen');
            }
        } finally {
            @unlink($cnf);
        }
        $d = openssl_pkey_get_details($k)['ec'];

        return [$pem, "\x04" . str_pad($d['x'], 32, "\0", STR_PAD_LEFT) . str_pad($d['y'], 32, "\0", STR_PAD_LEFT)];
    }

    /** @return array{privatni: string, javni: string} javni je base64url */
    public static function kljucevi(): array
    {
        if (self::$kljucevi) {
            return self::$kljucevi;
        }
        $put = storage_path('app/private/vapid.json');
        if (! is_dir(dirname($put))) {
            mkdir(dirname($put), 0750, true);
        }
        $f = fopen($put, 'c+');
        flock($f, LOCK_EX);
        $sadrzaj = stream_get_contents($f);
        $k = $sadrzaj ? json_decode($sadrzaj, true) : null;
        if (! $k) {
            [$pem, $javni] = self::noviPar();
            $k = ['privatni' => $pem, 'javni' => self::b64($javni)];
            ftruncate($f, 0);
            rewind($f);
            fwrite($f, json_encode($k));
            @chmod($put, 0600);
        }
        flock($f, LOCK_UN);
        fclose($f);

        return self::$kljucevi = $k;
    }

    public static function javniKljuc(): ?string
    {
        try {
            return self::kljucevi()['javni'];
        } catch (\Throwable $e) {
            Log::warning('Web push: ključevi nisu dostupni: ' . $e->getMessage());

            return null;
        }
    }

    /** Sadržaj zahtjeva po RFC 8291: zaglavlje (sol, veličina zapisa, naš javni ključ) i šifrirani zapis. */
    public static function sifriraj(string $poruka, string $p256dh, string $auth): string
    {
        $uaJavni = self::b64d($p256dh);
        $tajna = self::b64d($auth);
        if (strlen($uaJavni) !== 65 || strlen($tajna) < 16) {
            throw new \InvalidArgumentException('Neispravna pretplata');
        }
        [$pem, $asJavni] = self::noviPar();
        $uaPem = "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode(hex2bin(self::SPKI_P256) . $uaJavni), 64, "\n") . "-----END PUBLIC KEY-----\n";
        $ecdh = openssl_pkey_derive(openssl_pkey_get_public($uaPem), openssl_pkey_get_private($pem), 32);
        if ($ecdh === false) {
            throw new \RuntimeException('OpenSSL: ECDH nije uspio');
        }

        $ikm = hash_hkdf('sha256', $ecdh, 32, "WebPush: info\0" . $uaJavni . $asJavni, $tajna);
        $sol = random_bytes(16);
        $cek = hash_hkdf('sha256', $ikm, 16, "Content-Encoding: aes128gcm\0", $sol);
        $nonce = hash_hkdf('sha256', $ikm, 12, "Content-Encoding: nonce\0", $sol);
        $sifrirano = openssl_encrypt($poruka . "\x02", 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $oznaka, '', 16);

        return $sol . pack('N', 4096) . chr(65) . $asJavni . $sifrirano . $oznaka;
    }

    /** VAPID zaglavlje za push servis: JWT potpisan ES256, vrijedi 12 sati. */
    public static function vapid(string $endpoint): string
    {
        $k = self::kljucevi();
        $d = parse_url($endpoint);
        $kontakt = config('mail.from.address') ?: 'info@agro-log.app';
        $jwt = self::b64(json_encode(['typ' => 'JWT', 'alg' => 'ES256'])) . '.'
            . self::b64(json_encode(['aud' => $d['scheme'] . '://' . $d['host'], 'exp' => time() + 43200, 'sub' => 'mailto:' . $kontakt]));
        if (! openssl_sign($jwt, $der, $k['privatni'], OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('OpenSSL: potpis nije uspio');
        }

        return 'vapid t=' . $jwt . '.' . self::b64(self::derURaw($der)) . ', k=' . $k['javni'];
    }

    /** ES256 potpis iz DER zapisa u 64 bajta (r || s), kako traži JWT. */
    private static function derURaw(string $der): string
    {
        $i = 3;   // 0x30, duljina, 0x02
        $rLen = ord($der[$i]);
        $r = substr($der, $i + 1, $rLen);
        $i += $rLen + 2;   // ... 0x02
        $sLen = ord($der[$i]);
        $s = substr($der, $i + 1, $sLen);

        return str_pad(ltrim($r, "\0"), 32, "\0", STR_PAD_LEFT) . str_pad(ltrim($s, "\0"), 32, "\0", STR_PAD_LEFT);
    }

    /** Šalje jednom uređaju. Pretplata koju servis više ne poznaje se briše. Nikad ne baca grešku pozivatelju. */
    public static function posalji(PushPretplata $p, string $naslov, string $tekst, string $url): bool
    {
        try {
            $tijelo = self::sifriraj(json_encode(['naslov' => $naslov, 'tekst' => $tekst, 'url' => $url], JSON_UNESCAPED_UNICODE), $p->p256dh, $p->auth);
            $odgovor = Http::timeout(10)->withHeaders([
                'Authorization' => self::vapid($p->endpoint), 'TTL' => '86400', 'Urgency' => 'normal', 'Content-Encoding' => 'aes128gcm',
            ])->withBody($tijelo, 'application/octet-stream')->post($p->endpoint);

            if (in_array($odgovor->status(), [404, 410], true)) {
                $p->delete();

                return false;
            }
            if (! $odgovor->successful()) {
                Log::warning('Web push: servis je vratio ' . $odgovor->status());
            }

            return $odgovor->successful();
        } catch (\Throwable $e) {
            Log::warning('Web push: ' . $e->getMessage());

            return false;
        }
    }

    /** Svim uređajima korisnika, ako ima uključene push obavijesti. Vraća broj uređaja kojima je stiglo. */
    public static function korisniku(User $user, string $naslov, string $tekst, string $url): int
    {
        if (! $user->push_obavijesti) {
            return 0;
        }
        $n = 0;
        foreach (PushPretplata::where('user_id', $user->id)->get() as $p) {
            $n += self::posalji($p, $naslov, $tekst, $url) ? 1 : 0;
        }

        return $n;
    }
}
