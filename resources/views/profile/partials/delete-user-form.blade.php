<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Brisanje računa
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Nakon brisanja računa svi vaši podaci bit će trajno izbrisani, i to se ne može poništiti. Prije toga preuzmite evidenciju pod <a href="{{ route('izvjestaji') }}" class="underline">Izvještaji</a>, jer je propisi traže da je čuvate. <a href="{{ route('brisanje') }}" class="underline" target="_blank">Što se točno briše</a>
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Obriši račun</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">
                Jeste li sigurni da želite obrisati svoj račun?
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Svi vaši podaci bit će trajno izbrisani. {{ auth()->user()->google_id ? 'Za potvrdu upišite svoju e-mail adresu.' : 'Za potvrdu unesite svoju lozinku.' }}
            </p>

            <div class="mt-6">
                @if(auth()->user()->google_id)
                    {{-- upišite svoju e-mail adresu --}}
                    <x-input-label for="potvrda_email" value="E-mail adresa računa" class="sr-only" />
                    <x-text-input id="potvrda_email" name="potvrda_email" type="email" class="mt-1 block w-3/4" placeholder="E-mail adresa računa" />
                    <x-input-error :messages="$errors->userDeletion->get('potvrda_email')" class="mt-2" />
                @else
                    <x-input-label for="password" value="Lozinka" class="sr-only" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-3/4" placeholder="Lozinka" />
                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                @endif
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Odustani
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    Obriši račun
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
