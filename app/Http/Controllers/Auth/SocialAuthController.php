<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\NoviKorisnikMail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::where('google_id', $googleUser->getId())->first();

        if (!$user) {
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar'    => $googleUser->getAvatar(),
                ]);
            } else {
                $user = User::create([
                    'name'                => $googleUser->getName(),
                    'email'               => $googleUser->getEmail(),
                    'google_id'           => $googleUser->getId(),
                    'avatar'              => $googleUser->getAvatar(),
                    'naziv_gospodarstva'  => '',
                    'password'            => bcrypt(Str::random(32)),
                ]);

                try {
                    Mail::to('slynetwork@gmail.com')->send(new NoviKorisnikMail($user));
                } catch (\Exception $e) {
                    // Ne blokiramo login ako mail ne prođe
                }
            }
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }
}
