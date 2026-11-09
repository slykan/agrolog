<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\MobileGoogleLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Laravel\Socialite\Facades\Socialite;

class MobileGoogleController extends Controller
{
    public function prepare(Request $request)
    {
        // Generated here, never accepted from a URL: binds the handoff to this WebView session.
        $state = bin2hex(random_bytes(32));
        $request->session()->put('mobile_google_pending', [
            'state' => $state,
            'expires_at' => now()->addMinutes(10)->timestamp,
        ]);
        $launch = URL::temporarySignedRoute('auth.mobile.google.launch', now()->addMinutes(10), [
            'state' => $state,
            'savjetnik' => $request->boolean('savjetnik') ? 1 : 0,
            'prezentacija' => $request->boolean('prezentacija') ? 1 : 0,
        ]);

        return response()->view('auth.mobile-google-prepare', compact('launch'))
            ->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function launch(Request $request)
    {
        $data = $request->validate(['state' => ['required', 'regex:/\A[a-f0-9]{64}\z/']]);
        $request->session()->put('mobile_google_browser_state', $data['state']);
        $request->session()->put('registracija_savjetnik', $request->boolean('savjetnik'));
        $request->session()->put('registracija_prezentacija', $request->boolean('prezentacija'));

        return Socialite::driver('google')->redirect();
    }

    public function bridge(Request $request)
    {
        abort_unless($this->pending($request), 419, 'Prijava je istekla. Ponovno odaberite Google prijavu.');

        return response()->view('auth.mobile-google-bridge')
            ->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function complete(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'regex:/\A[a-f0-9]{64}\z/'],
            'state' => ['required', 'regex:/\A[a-f0-9]{64}\z/'],
        ]);
        $pending = $this->pending($request);
        abort_unless($pending && hash_equals($pending['state'], $data['state']), 419,
            'Ova prijava ne pripada aplikaciji koja ju je pokrenula.');
        $user = MobileGoogleLogin::redeem($data['code'], $data['state']);
        abort_unless($user, 419, 'Prijava je istekla ili je već iskorištena. Pokušajte ponovno.');

        $request->session()->forget(['mobile_google_pending', 'url.intended']);
        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect($user->pocetnaStranica())->header('Cache-Control', 'no-store');
    }

    private function pending(Request $request): ?array
    {
        $pending = $request->session()->get('mobile_google_pending');

        return is_array($pending) && ($pending['expires_at'] ?? 0) > now()->timestamp
            && isset($pending['state']) ? $pending : null;
    }
}
