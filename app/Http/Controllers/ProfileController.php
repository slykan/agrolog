<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /** Prekidači Push i Mail obavijesti. Datum ranije odjave od mailova se ne pomiče. */
    public function obavijesti(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->forceFill([
            'push_obavijesti' => $request->boolean('push'),
            'obavijesti_odjava_at' => $request->boolean('mail') ? null : ($user->obavijesti_odjava_at ?? now()),
        ])->save();

        return Redirect::to(route('profile.edit') . '#obavijesti')->with('status', 'obavijesti-spremljene');
    }

    /** Praćenje troška goriva: popup za gorivo nakon prskanja i gnojidbe. */
    public function gorivo(Request $request): RedirectResponse
    {
        $request->user()->forceFill(['prati_gorivo' => $request->boolean('prati'), 'gorivo_kasnije' => null])->save();

        return Redirect::to(route('profile.edit') . '#gorivo')->with('status', 'gorivo-spremljeno');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        // račun otvoren Googleom ima nasumičnu lozinku koju korisnik ne zna: potvrda upisom vlastitog maila
        if ($user->google_id && $request->has('potvrda_email')) {
            $request->validateWithBag('userDeletion', [
                'potvrda_email' => ['required', 'string', function ($polje, $v, $greska) use ($user) {
                    if (mb_strtolower(trim((string) $v)) !== mb_strtolower($user->email)) {
                        $greska('Upisana adresa nije e-mail vašeg računa.');
                    }
                }],
            ]);
        } else {
            $request->validateWithBag('userDeletion', [
                'password' => ['required', 'current_password'],
            ]);
        }

        Auth::logout();

        // javna slika gospodarstva leži u public/, pa je baza ne briše sama
        if ($pz = \App\Models\Prezentacija::where('user_id', $user->id)->first()) {
            \App\Models\Prezentacija::obrisiSliku($pz->slika);
            $pz->proizvodi()->pluck('slika')->each(fn ($s) => \App\Models\Prezentacija::obrisiSliku($s));
        }
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
