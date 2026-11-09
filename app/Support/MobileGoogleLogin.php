<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class MobileGoogleLogin
{
    public static function issue(User $user, string $state): string
    {
        $code = bin2hex(random_bytes(32));
        DB::table('mobile_google_logins')->where('expires_at', '<=', now())->delete();
        DB::table('mobile_google_logins')->insert([
            'code_hash' => hash('sha256', $code),
            'state_hash' => hash('sha256', $state),
            'user_id' => $user->id,
            'expires_at' => now()->addMinute(),
        ]);

        return $code;
    }

    public static function redeem(string $code, string $state): ?User
    {
        $query = DB::table('mobile_google_logins')
            ->where('code_hash', hash('sha256', $code))
            ->where('state_hash', hash('sha256', $state))
            ->where('expires_at', '>', now());
        $login = $query->first();
        if (! $login) {
            return null;
        }
        // Conditional delete is atomic: even concurrent requests can consume a code only once.
        if ($query->delete() !== 1) {
            return null;
        }

        return User::find($login->user_id);
    }
}
