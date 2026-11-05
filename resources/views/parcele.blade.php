<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Parcele &amp; Kulture</h2>
    </x-slot>
    <div class="py-3 sm:py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('partials.karta')
            <div class="bg-white shadow rounded-lg p-6">
                <livewire:parcela-manager />
            </div>
            {{-- izvan Livewire komponente da se ~1400 naziva ne šalje pri svakom osvježavanju --}}
            <datalist id="eppo-popis">
                @foreach(array_keys(\App\Support\SzbPopisi::eppo()) as $usjev)
                    <option value="{{ $usjev }}">
                @endforeach
            </datalist>
        </div>
    </div>
</x-app-layout>
