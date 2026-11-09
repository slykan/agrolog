<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin &mdash; gospodarstva na popisu</h2>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('gospodarstva') }}" target="_blank" class="text-sm text-green-700 hover:underline">Javni popis &rarr;</a>
                <a href="{{ route('admin.korisnici') }}" class="text-sm text-green-700 hover:underline">Korisnici &rarr;</a>
            </div>
        </div>
    </x-slot>

    @php
        $boje = ['javno' => 'bg-green-100 text-green-800', 'na_pregledu' => 'bg-amber-100 text-amber-800', 'odbijeno' => 'bg-red-100 text-red-800'];
        $nazivi = ['javno' => 'javno', 'na_pregledu' => 'čeka odobrenje', 'odbijeno' => 'odbijeno / skriveno'];
    @endphp

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8 space-y-4">
            @if(session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            @forelse($sve as $p)
                @php $s = $p->status(); @endphp
                <div class="bg-white shadow-sm rounded-xl p-5 flex flex-col gap-4 sm:flex-row" x-data="{ odbij: false }">
                    @if($p->slika)
                        <img src="{{ $p->slikaUrl() }}" alt="" class="h-28 w-full sm:w-40 rounded-lg object-cover border border-gray-200 shrink-0">
                    @endif
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-bold text-gray-900">{{ $p->naziv }}</h3>
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $boje[$s] ?? 'bg-gray-100 text-gray-700' }}">{{ $nazivi[$s] ?? $s }}</span>
                        </div>
                        <p class="text-sm text-gray-500">{{ $p->mjesto }} · {{ $p->zupanija }} · {{ $p->user?->name }} ({{ $p->user?->email }}) · izmijenjeno {{ $p->updated_at->format('d.m.Y. H:i') }}</p>
                        <p class="mt-2 text-sm text-gray-700 whitespace-pre-line break-words">{{ $p->opis }}</p>
                        <p class="mt-2 text-xs text-gray-500">{{ implode(', ', $p->oznake ?? []) }} · tel. {{ $p->telefon }}@if($p->web) · {{ $p->web }}@endif @if($p->facebook) · {{ $p->facebook }}@endif @if($p->instagram) · {{ $p->instagram }}@endif</p>
                        @if($p->odbijeno_razlog)<p class="mt-1 text-xs text-red-700">Razlog: {{ $p->odbijeno_razlog }}</p>@endif

                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            @if($s !== 'javno')
                                <form method="post" action="{{ route('admin.gospodarstva.odobri', $p) }}">@csrf<button class="btn-xs-green">Odobri i objavi</button></form>
                            @else
                                <a href="{{ route('gospodarstvo', $p->slug) }}" target="_blank" class="btn-xs-gray">Otvori stranicu</a>
                            @endif
                            <button type="button" @click="odbij = ! odbij" class="btn-xs-red">{{ $s === 'javno' ? 'Skini s popisa' : 'Odbij' }}</button>
                        </div>
                        <form x-show="odbij" x-cloak method="post" action="{{ route('admin.gospodarstva.odbij', $p) }}" class="mt-3 flex flex-col gap-2 sm:flex-row">
                            @csrf
                            <input name="razlog" required minlength="5" maxlength="300" placeholder="Razlog koji dobiva vlasnik, npr. opis je prekratak" class="form-input flex-1">
                            <button class="btn-xs-red">Pošalji</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 py-10">Nijedno gospodarstvo još nije uključilo prezentaciju.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
