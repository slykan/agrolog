<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\DobrodoslicaMail;
use App\Mail\NoviKorisnikMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Cloudflare Turnstile provjera
        $turnstileResponse = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret'   => config('services.turnstile.secret'),
            'response' => $request->input('cf-turnstile-response'),
            'remoteip' => $request->ip(),
        ]);

        if (! $turnstileResponse->json('success')) {
            throw ValidationException::withMessages([
                'cf-turnstile-response' => 'CAPTCHA provjera nije uspjela. Pokušajte ponovo.',
            ]);
        }

        $request->validate([
            'naziv_gospodarstva' => ['required', 'string', 'max:255'],
            'name'               => ['required', 'string', 'max:255'],
            'email'              => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password'           => ['required', 'confirmed', Rules\Password::defaults()],
            'mipg'               => ['nullable', 'string', 'max:50'],
            'oib'                => ['nullable', 'string', 'max:11'],
            'je_savjetnik'       => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'naziv_gospodarstva' => $request->naziv_gospodarstva,
            'name'               => $request->name,
            'email'              => $request->email,
            'password'           => Hash::make($request->password),
            'mipg'               => $request->mipg,
            'oib'                => $request->oib,
            'je_savjetnik'       => $request->boolean('je_savjetnik'),
        ]);

        event(new Registered($user));
        Auth::login($user);

        // Obavijest adminu
        try {
            Mail::to('slynetwork@gmail.com')->send(new NoviKorisnikMail($user));
        } catch (\Exception $e) {
            // Ne blokiramo registraciju ako mail ne prođe
        }

        // Dobrodošlica korisniku
        try {
            Mail::to($user->email)->send(new DobrodoslicaMail($user));
        } catch (\Exception $e) {
            report($e);
        }

        // nakon registracije s poveznice iz poziva: natrag na poziv
        return redirect()->intended($user->pocetnaStranica());
    }
}
