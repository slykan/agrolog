<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Skladište</h2>
        <p class="mt-1 text-sm text-gray-600">
            Pratite zalihe sredstava i gnojiva: upišete nabavu, a potrošnja iz prskanja i gnojidbe oduzima se sama.
            Neobavezno. Kad je isključeno, skladište se nigdje ne prikazuje, a upisani podaci ostaju spremljeni.
        </p>
    </header>

    <form method="post" action="{{ route('profile.skladiste') }}" class="mt-6 space-y-4">
        @csrf
        @method('patch')
        <input type="hidden" name="koristi_skladiste" value="0">
        <label class="flex items-center gap-3">
            <input type="checkbox" name="koristi_skladiste" value="1" class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                   @checked($user->koristi_skladiste) onchange="this.form.submit()">
            <span class="text-sm text-gray-800">Koristim skladište</span>
        </label>
        <noscript><x-primary-button>Spremi</x-primary-button></noscript>
        @if (session('status') === 'skladiste-updated')
            <p class="text-sm text-green-700">
                {{ $user->koristi_skladiste ? 'Skladište je uključeno.' : 'Skladište je isključeno.' }}
                @if($user->koristi_skladiste)<a href="{{ route('skladiste') }}" class="underline">Otvori skladište →</a>@endif
            </p>
        @endif
    </form>
</section>
