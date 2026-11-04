@php
    $vrijeme = fn ($t) => $t ? \Illuminate\Support\Carbon::parse($t)->timezone('Europe/Zagreb')->format('d.m.Y. H:i') : '—';
    $ha = fn ($v) => number_format((float) $v, 2, ',', '.') . ' ha';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $user->name }}</h2>
            <a href="{{ route('admin.korisnici') }}" class="text-sm text-green-700 hover:underline">&larr; Svi korisnici</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Podaci --}}
                <div class="bg-white shadow rounded-lg p-6 lg:col-span-1">
                    <h3 class="font-semibold text-gray-800 mb-3">Podaci</h3>
                    <dl class="text-sm space-y-2">
                        @foreach ([
                            'E-mail' => $user->email,
                            'Prijava' => $user->google_id ? 'Google' : 'e-mail i lozinka',
                            'E-mail potvrđen' => $user->email_verified_at ? $vrijeme($user->email_verified_at) : 'ne',
                            'Gospodarstvo' => $user->naziv_gospodarstva ?: '—',
                            'MIPG' => $user->mipg ?: '—',
                            'OIB' => $user->oib ?: '—',
                            'Registriran' => $vrijeme($user->created_at),
                            'Zadnja aktivnost' => $vrijeme($user->last_seen_at),
                            'Zadnji unos' => $vrijeme($zadnjiZapis),
                        ] as $k => $v)
                            <div class="flex justify-between gap-4 border-b border-gray-100 pb-1.5">
                                <dt class="text-gray-500">{{ $k }}</dt>
                                <dd class="text-gray-800 text-right break-all">{{ $v }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                {{-- Brojke --}}
                <div class="lg:col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-4 content-start">
                    @foreach ([
                        ['Parcela', $user->parcele_count, $ha($parcele->sum('povrsina_ha'))],
                        ['Kultura', $user->kulture_count, $parcele->flatMap->kulture->whereNull('zavrsena_at')->count() . ' aktivnih'],
                        ['Gnojidbi', $user->gnojidbe_count, 'zapisa'],
                        ['Prskanja', $user->prskanja_count, 'zapisa'],
                    ] as [$naslov, $broj, $opis])
                        <div class="bg-white shadow-sm rounded-xl p-4">
                            <div class="text-xs text-gray-500">{{ $naslov }}</div>
                            <div class="text-2xl font-bold text-gray-800 mt-1">{{ $broj }}</div>
                            <div class="text-xs text-gray-400 mt-1">{{ $opis }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Parcele i kulture --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-3">Parcele i kulture</h3>
                @forelse ($parcele as $p)
                    <div class="py-2 border-b border-gray-100 last:border-0 text-sm">
                        <span class="font-semibold text-gray-800">ARKOD {{ $p->arkod_broj }}</span>
                        <span class="text-gray-500">· {{ $ha($p->povrsina_ha) }}</span>
                        <div class="text-gray-600 mt-0.5">
                            @forelse ($p->kulture as $kult)
                                <span class="inline-block mr-3 {{ $kult->zavrsena_at ? 'text-gray-400' : '' }}">
                                    {{ $kult->naziv }} ({{ $kult->godina }}, {{ $ha($kult->posadjena_povrsina_ha) }}){{ $kult->zavrsena_at ? ' — završena' : '' }}
                                </span>
                            @empty
                                <span class="text-gray-400">bez kultura</span>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Korisnik još nije unio parcele.</p>
                @endforelse
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800 mb-3">Zadnja prskanja</h3>
                    @forelse ($prskanja as $pr)
                        <div class="py-1.5 border-b border-gray-100 last:border-0 text-sm flex justify-between gap-3">
                            <span>{{ $pr->trgovacki_naziv_sredstva ?: '—' }} <span class="text-gray-400">· {{ $pr->kultura?->naziv }}{{ $pr->kultura?->parcela ? ', ARKOD ' . $pr->kultura->parcela->arkod_broj : '' }}</span></span>
                            <span class="text-gray-500 whitespace-nowrap">{{ $pr->datum_tretiranja?->format('d.m.Y.') }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Nema zapisa.</p>
                    @endforelse
                </div>
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="font-semibold text-gray-800 mb-3">Zadnje gnojidbe</h3>
                    @forelse ($gnojidbe as $g)
                        <div class="py-1.5 border-b border-gray-100 last:border-0 text-sm flex justify-between gap-3">
                            <span>{{ $g->tip_gnojiva ?: '—' }} <span class="text-gray-400">· {{ $g->kultura?->naziv }}{{ $g->kultura?->parcela ? ', ARKOD ' . $g->kultura->parcela->arkod_broj : '' }}</span></span>
                            <span class="text-gray-500 whitespace-nowrap">{{ $g->datum ? \Illuminate\Support\Carbon::parse($g->datum)->format('d.m.Y.') : '' }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Nema zapisa.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
