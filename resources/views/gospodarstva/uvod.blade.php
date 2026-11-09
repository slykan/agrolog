<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Predstavite gospodarstvo</h2>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-3xl mx-auto px-3 sm:px-6 lg:px-8 space-y-4">
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-900">
                Dobrodošli! Na registraciji ste označili da želite predstaviti gospodarstvo. Ispunite podatke ispod i pošaljite ih na pregled.
                Možete i kasnije, u <a href="{{ route('profile.edit') }}#prezentacija" class="underline">Profilu</a>.
            </div>
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg" id="prezentacija">
                @include('profile.partials.prezentacija', ['uvod' => true])
            </div>
            <div class="text-center">
                <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 underline">Kasnije, idi na početnu</a>
            </div>
        </div>
    </div>
    @include('partials.karta')
</x-app-layout>
