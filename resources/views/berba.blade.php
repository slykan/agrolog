<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Berba i žetva</h2>
    </x-slot>
    <div class="py-3 sm:py-6">
        <div class="max-w-6xl mx-auto px-2 sm:px-4 lg:px-6">
            <div class="bg-white shadow rounded-lg p-4 sm:p-6">
                <livewire:berba-table />
            </div>
        </div>
    </div>
</x-app-layout>
