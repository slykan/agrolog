<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\DobrodoslicaMail;
use App\Mail\NoviKorisnikMail;
use App\Models\User;
use App\Support\MobileGoogleLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect()
    {
        session()->forget('mobile_google_browser_state');
        // kvačica "agronom ili savjetnik" s registracije; vrijedi samo za novi račun
        session(['registracija_savjetnik' => request()->boolean('savjetnik'), 'registracija_prezentacija' => request()->boolean('prezentacija')]);

        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $mobileState = session()->pull('mobile_google_browser_state');
        if (request()->has('error')) {
            if ($mobileState) {
                return response()->view('auth.mobile-google-finish', ['code' => null, 'state' => null])
                    ->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
            }
            return redirect()->route('login')->withErrors(['email' => 'Google prijava nije dovršena. Pokušajte ponovno.']);
        }
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            if ($mobileState) {
                return response()->view('auth.mobile-google-finish', ['code' => null, 'state' => null])
                    ->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
            }
            return redirect()->route('login')->withErrors(['email' => 'Google prijava je istekla. Pokušajte ponovno.']);
        }

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
                    'je_savjetnik'        => $savjetnik = (bool) session()->pull('registracija_savjetnik', false),
                    'zeli_prezentaciju'   => ! $savjetnik && (bool) session()->pull('registracija_prezentacija', false),
                ]);

                try {
                    Mail::to('slynetwork@gmail.com')->send(new NoviKorisnikMail($user));
                } catch (\Exception $e) {
                    // Ne blokiramo login ako mail ne prođe
                }

                // Dobrodošlica korisniku
                try {
                    Mail::to($user->email)->send(new DobrodoslicaMail($user));
                } catch (\Exception $e) {
                    report($e);
                }
            }
        }

        if ($mobileState) {
            $code = MobileGoogleLogin::issue($user, $mobileState);

            return response()->view('auth.mobile-google-finish', ['code' => $code, 'state' => $mobileState])
                ->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->intended($user->pocetnaStranica());
    }
}
