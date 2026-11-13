<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Trošak goriva</h2>
        <p class="mt-1 text-sm text-gray-600">Nakon prskanja ili gnojidbe AgroLog pita koliko ste goriva potrošili taj dan i dijeli trošak po parcelama. Vidi se samo u Troškovima, ne u evidenciji ni izvozu.</p>
    </header>

    <form method="post" action="{{ route('profile.gorivo') }}" class="mt-4 space-y-3">
        @csrf
        @method('patch')
        <label class="flex items-start gap-3">
            <input type="checkbox" name="prati" value="1" @checked($user->prati_gorivo) onchange="this.form.submit()" class="mt-1 rounded text-green-600">
            <span><span class="font-medium text-gray-800">Praćenje troška goriva</span>
                <span class="block text-sm text-gray-600">Isključeno: nema pitanja za gorivo. Već upisano gorivo ostaje u Troškovima, a dodati ga možete i tamo.</span></span>
        </label>
        <noscript><button class="btn-xs-green">Spremi</button></noscript>
        @if(session('status') === 'gorivo-spremljeno')
            <p class="text-sm text-green-700">Spremljeno.</p>
        @endif
    </form>
</section>
