<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Evidencija gnojidbe</h2>
    </x-slot>
    <div class="py-6">
        {{-- tablica evidencije je široka: puna širina ekrana --}}
        <div class="mx-auto px-2 sm:px-4 lg:px-6">
            <div class="bg-white shadow rounded-lg p-6">
                <livewire:gnojidba-table />
            </div>
        </div>
    </div>
</x-app-layout>
