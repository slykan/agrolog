<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Evidencija prskanja</h2>
    </x-slot>
    <div class="py-3 sm:py-6">
        {{-- tablica evidencije je široka: puna širina ekrana --}}
        <div class="mx-auto px-2 sm:px-4 lg:px-6">
            <div class="bg-white shadow rounded-lg p-6">
                <livewire:gorivo-unos />
                <livewire:prskanje-table />
            </div>
            {{-- prijedlozi za trgovački naziv: sredstva koja je korisnik već koristio + registar FIS-a --}}
            @php
                $vlastita = \App\Models\Prskanje::where('user_id', auth()->id())->whereNotNull('trgovacki_naziv_sredstva')
                    ->distinct()->pluck('trgovacki_naziv_sredstva');
                $registar = \App\Models\SzbSredstvo::orderByDesc('aktivno')->orderBy('naziv')->get(['naziv', 'registracijski_broj', 'aktivno']);
                $uRegistru = $registar->pluck('naziv')->map(fn ($n) => mb_strtoupper($n))->flip();
            @endphp
            <datalist id="szb-sredstva">
                @foreach($vlastita->reject(fn ($n) => isset($uRegistru[mb_strtoupper(trim($n))])) as $naziv)
                    <option value="{{ $naziv }}">
                @endforeach
                @foreach($registar as $s)
                    <option value="{{ $s->naziv }}" label="{{ $s->registracijski_broj }}{{ $s->aktivno ? '' : ' (neaktivno)' }}">
                @endforeach
            </datalist>
        </div>
    </div>
</x-app-layout>
