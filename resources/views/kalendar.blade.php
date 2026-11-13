<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kalendar</h2>
    </x-slot>
    <div class="py-3 sm:py-6">
        <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6">
            <div class="bg-white shadow rounded-lg p-3 sm:p-6">
                <livewire:gorivo-unos />
                <livewire:kalendar-pregled />
            </div>
        </div>
    </div>
</x-app-layout>
