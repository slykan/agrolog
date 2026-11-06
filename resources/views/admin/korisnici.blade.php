@php
    $vrijeme = fn ($t) => $t ? $t->timezone('Europe/Zagreb')->format('d.m.Y. H:i') : '—';
    $sortLink = function (string $key, string $label) use ($sort, $dir, $q) {
        $novi = $sort === $key && $dir === 'desc' ? 'asc' : 'desc';
        $strelica = $sort === $key ? ($dir === 'desc' ? ' ↓' : ' ↑') : '';
        $url = route('admin.korisnici', array_filter(['q' => $q, 'sort' => $key, 'dir' => $novi]));
        return '<a href="' . e($url) . '" class="hover:text-green-700">' . e($label) . $strelica . '</a>';
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin &mdash; korisnici</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.oglasi') }}" class="btn-secondary">Oglasi</a>
                <a href="{{ route('admin.obavijesti') }}" class="btn-primary">&#9993; Obavijest svima</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if(session('poruka'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('poruka') }}</div>
            @endif

            {{-- Brojke --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach ([
                    ['Korisnika', $statistika['korisnika'], '+' . $statistika['novih_7'] . ' u 7 dana · +' . $statistika['novih_30'] . ' u 30'],
                    ['Aktivnih (30 dana)', $statistika['aktivnih_30'], 'prijavljeni u zadnjih 30 dana'],
                    ['S unesenim parcelama', $statistika['s_podacima'], $statistika['korisnika'] ? round($statistika['s_podacima'] / $statistika['korisnika'] * 100) . ' % korisnika' : ''],
                    ['Parcela', $statistika['parcela'], number_format($statistika['hektara'], 2, ',', '.') . ' ha ukupno'],
                    ['Gnojidbi', $statistika['gnojidbi'], 'zapisa'],
                    ['Prskanja', $statistika['prskanja'], 'zapisa'],
                ] as [$naslov, $broj, $opis])
                    <div class="bg-white shadow-sm rounded-xl p-4">
                        <div class="text-xs text-gray-500">{{ $naslov }}</div>
                        <div class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($broj, 0, ',', '.') }}</div>
                        <div class="text-xs text-gray-400 mt-1">{{ $opis }}</div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white shadow rounded-lg p-4 sm:p-6">
                <form method="GET" class="flex flex-wrap gap-2 mb-4">
                    <input type="search" name="q" value="{{ $q }}" placeholder="Ime, e-mail, gospodarstvo, MIPG, OIB…"
                           class="flex-1 min-w-[220px] rounded-md border-gray-300 text-sm focus:border-green-500 focus:ring-green-500">
                    <input type="hidden" name="sort" value="{{ $sort }}">
                    <input type="hidden" name="dir" value="{{ $dir }}">
                    <button class="px-4 py-2 rounded-md bg-green-700 text-white text-sm font-semibold hover:bg-green-800">Traži</button>
                    @if ($q !== '')
                        <a href="{{ route('admin.korisnici') }}" class="px-4 py-2 rounded-md border text-sm text-gray-600 hover:bg-gray-50">Očisti</a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-gray-500 border-b">
                                <th class="py-2 pr-4">{!! $sortLink('ime', 'Korisnik') !!}</th>
                                <th class="py-2 pr-4">Gospodarstvo</th>
                                <th class="py-2 pr-4">{!! $sortLink('registracija', 'Registriran') !!}</th>
                                <th class="py-2 pr-4">{!! $sortLink('aktivnost', 'Zadnja aktivnost') !!}</th>
                                <th class="py-2 pr-4 text-right">{!! $sortLink('parcele', 'Parcele') !!}</th>
                                <th class="py-2 pr-4 text-right">Kulture</th>
                                <th class="py-2 pr-4 text-right">{!! $sortLink('zapisi', 'Zapisi') !!}</th>
                                <th class="py-2 pr-4 text-center" title="Prima obavijesti mailom (Admin → Obavijesti)">Obavijesti</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($korisnici as $k)
                                <tr class="hover:bg-green-50/50 cursor-pointer" onclick="window.location='{{ route('admin.korisnik', $k) }}'">
                                    <td class="py-2.5 pr-4">
                                        <a href="{{ route('admin.korisnik', $k) }}" class="font-semibold text-gray-800 hover:text-green-700">{{ $k->name }}</a>
                                        <div class="text-xs text-gray-500">
                                            {{ $k->email }}
                                            @if ($k->google_id) <span class="ml-1 px-1.5 rounded bg-blue-50 text-blue-700">Google</span> @endif
                                        </div>
                                    </td>
                                    <td class="py-2.5 pr-4 text-gray-600">
                                        {{ $k->naziv_gospodarstva ?: '—' }}
                                        @if ($k->mipg) <div class="text-xs text-gray-400">MIPG {{ $k->mipg }}</div> @endif
                                    </td>
                                    <td class="py-2.5 pr-4 text-gray-600 whitespace-nowrap">{{ $vrijeme($k->created_at) }}</td>
                                    <td class="py-2.5 pr-4 text-gray-600 whitespace-nowrap">{{ $vrijeme($k->last_seen_at) }}</td>
                                    <td class="py-2.5 pr-4 text-right whitespace-nowrap">
                                        {{ $k->parcele_count }}
                                        @if ($k->parcele_sum_povrsina_ha) <div class="text-xs text-gray-400">{{ number_format($k->parcele_sum_povrsina_ha, 2, ',', '.') }} ha</div> @endif
                                    </td>
                                    <td class="py-2.5 pr-4 text-right">{{ $k->kulture_count }}</td>
                                    <td class="py-2.5 pr-4 text-right whitespace-nowrap">
                                        {{ $k->zapisi_count }}
                                        <div class="text-xs text-gray-400">{{ $k->gnojidbe_count }} gn · {{ $k->prskanja_count }} pr</div>
                                    </td>
                                    <td class="py-2.5 pr-4 text-center" onclick="event.stopPropagation()">
                                        <form method="POST" action="{{ route('admin.korisnik.obavijesti', $k) }}">
                                            @csrf
                                            <label class="inline-flex items-center gap-1 cursor-pointer" title="{{ $k->obavijesti_odjava_at ? 'Odjavljen ' . $k->obavijesti_odjava_at->timezone('Europe/Zagreb')->format('d.m.Y.') . ' — klikom ponovno uključite' : 'Prima obavijesti — klikom isključite' }}">
                                                <input type="checkbox" class="rounded text-green-600" @checked(! $k->obavijesti_odjava_at) onchange="this.form.submit()">
                                            </label>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="py-8 text-center text-gray-500">Nema korisnika.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $korisnici->links() }}</div>
                <p class="mt-2 text-xs text-gray-400">Zadnja aktivnost se bilježi od 4.11.2026.; za ranije korisnike je prazna dok se ponovno ne prijave.</p>
            </div>
        </div>
    </div>
</x-app-layout>
