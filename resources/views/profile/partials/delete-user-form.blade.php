<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Brisanje računa
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Nakon brisanja računa, svi njegovi resursi i podaci bit će trajno izbrisani. Prije brisanja računa preuzmite sve podatke ili informacije koje želite zadržati.
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
                Nakon brisanja računa, svi njegovi resursi i podaci bit će trajno izbrisani. Unesite svoju lozinku kako biste potvrdili da želite trajno izbrisati svoj račun.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Lozinka" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="Lozinka"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
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
