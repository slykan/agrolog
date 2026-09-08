<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Podaci o profilu
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Ažurirajte podatke svog profila i email adresu.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="naziv_gospodarstva" value="Naziv gospodarstva" />
            <x-text-input id="naziv_gospodarstva" name="naziv_gospodarstva" type="text" class="mt-1 block w-full" :value="old('naziv_gospodarstva', $user->naziv_gospodarstva)" required />
            <x-input-error class="mt-2" :messages="$errors->get('naziv_gospodarstva')" />
        </div>

        <div>
            <x-input-label for="mipg" value="MIPG broj" />
            <x-text-input id="mipg" name="mipg" type="text" class="mt-1 block w-full" :value="old('mipg', $user->mipg)" />
            <x-input-error class="mt-2" :messages="$errors->get('mipg')" />
        </div>

        <div>
            <x-input-label for="oib" value="OIB" />
            <x-text-input id="oib" name="oib" type="text" class="mt-1 block w-full" :value="old('oib', $user->oib)" />
            <x-input-error class="mt-2" :messages="$errors->get('oib')" />
        </div>

        <div>
            <x-input-label for="name" value="Ime i prezime" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        Vaša email adresa nije potvrđena.

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Kliknite ovdje za ponovno slanje verifikacijskog emaila.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            Novi verifikacijski link je poslan na vašu email adresu.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Spremi</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >Spremljeno.</p>
            @endif
        </div>
    </form>
</section>
