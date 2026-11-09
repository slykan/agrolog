@extends('layouts.public', [
    'title' => 'Gospodarstva',
    'description' => 'Popis obiteljskih gospodarstava i proizvođača: voće, povrće, vino, med, maslinovo ulje i ostalo, po mjestu i županiji, s prodajom na kućnom pragu.',
    'active' => 'gospodarstva',
    'ads' => false,
])

@section('content')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">

    <span class="inline-block text-xs font-semibold uppercase tracking-widest text-green-600 bg-green-100 px-3 py-1 rounded-full mb-4">Gospodarstva</span>
    <h1 class="text-4xl font-bold text-gray-900 mb-3">Kupite izravno od proizvođača</h1>
    <p class="text-lg text-gray-500 mb-6">Obiteljska gospodarstva koja koriste AgroLog. Potražite po mjestu ili onome što nude i javite im se izravno.</p>
    @include('partials.kupujmo-hrvatsko', ['tekst' => 'Upoznajte obitelji koje stoje iza hrane na vašem stolu. Svako gospodarstvo s popisa ima ime, adresu i kontakt, a mnoga prodaju i na kućnom pragu.'])

    {{-- filteri --}}
    <form method="get" action="{{ route('gospodarstva') }}" class="rounded-2xl border border-gray-200 bg-gray-50 p-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <input type="search" name="q" value="{{ $q }}" placeholder="Naziv, mjesto ili proizvod" aria-label="Pretraga" class="form-input w-full sm:col-span-2 lg:col-span-1">
        <select name="oznaka" aria-label="Što nude" class="form-input w-full">
            <option value="">Sve što nude</option>
            @foreach(\App\Models\Prezentacija::OZNAKE as $o)
                <option value="{{ $o }}" @selected($oznaka === $o)>{{ $o }}</option>
            @endforeach
        </select>
        <select name="zupanija" aria-label="Županija" class="form-input w-full">
            <option value="">Sve županije</option>
            @foreach(\App\Models\Prezentacija::ZUPANIJE as $z)
                <option value="{{ $z }}" @selected($zupanija === $z)>{{ $z }}</option>
            @endforeach
        </select>
        <div class="flex items-center justify-between gap-3">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="kucni_prag" value="1" @checked($prag) class="rounded border-gray-300 text-green-700"> Kućni prag</label>
            <button class="btn-primary">Traži</button>
        </div>
        @if($filtrirano)
            <a href="{{ route('gospodarstva') }}" class="text-sm text-green-700 underline sm:col-span-2 lg:col-span-4">Poništi filtere</a>
        @endif
    </form>

    {{-- karta --}}
    @if($karta->isNotEmpty())
        <div class="mt-6 rounded-2xl overflow-hidden border border-gray-200 isolate">
            <div id="karta-gospodarstava" class="h-72 sm:h-96" role="region" aria-label="Karta gospodarstava"></div>
        </div>
    @endif

    {{-- kartice --}}
    @if($gospodarstva->isEmpty())
        <div class="mt-10 text-center text-gray-500">
            <p>{{ $filtrirano ? 'Nema gospodarstava za odabrane filtere.' : 'Popis se tek puni.' }}</p>
            <p class="text-sm mt-2">Imate gospodarstvo? <a href="{{ route('register') }}" class="text-green-700 underline">Otvorite besplatan AgroLog račun</a> i u profilu uključite <strong>Prezentaciju na AgroLogu</strong>.</p>
        </div>
    @else
        <div class="mt-8 grid gap-5 sm:grid-cols-2">
            @foreach($gospodarstva as $g)
                {{-- kartica: cijela vodi na gospodarstvo, a donji red na njegove proizvode (zasebne poveznice, ne ugniježđene) --}}
                <div class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200 hover:border-green-400 hover:shadow-md transition">
                    <a href="{{ route('gospodarstvo', $g->slug) }}" class="block flex-1">
                    @if($g->slika)
                        <img src="{{ $g->slikaUrl() }}" alt="{{ $g->naziv }}" loading="lazy" class="h-44 w-full object-cover">
                    @else
                        <div class="h-24 bg-gradient-to-br from-green-700 to-green-950 flex items-end p-4"><span class="text-white/80 text-sm">{{ $g->zupanija }}</span></div>
                    @endif
                    <div class="p-5">
                        <h2 class="text-lg font-bold text-gray-900 group-hover:text-green-800">{{ $g->naziv }}</h2>
                        <p class="text-sm text-gray-500">{{ $g->mjesto }} · {{ $g->zupanija }}@if($g->kucni_prag) · <span class="text-green-800 font-medium">kućni prag</span>@endif</p>
                        <p class="mt-2 text-sm text-gray-600 line-clamp-3">{{ $g->opis }}</p>
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach(array_slice($g->oznake ?? [], 0, 5) as $o)
                                <span class="rounded-full bg-green-50 px-2.5 py-0.5 text-xs text-green-900">{{ $o }}</span>
                            @endforeach
                        </div>
                    </div>
                    </a>
                    @if($g->proizvodi_count)
                        <a href="{{ route('trznica', ['prodavac' => $g->slug]) }}" class="flex items-center justify-between border-t border-gray-100 px-5 py-3 text-sm font-semibold text-green-800 hover:bg-green-50">
                            <span>Proizvodi na Tržnici ({{ $g->proizvodi_count }})</span><span aria-hidden="true">→</span>
                        </a>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="mt-8">{{ $gospodarstva->links() }}</div>
    @endif

    <div class="mt-12 rounded-2xl bg-green-50 border border-green-200 p-6">
        <h2 class="text-lg font-bold text-gray-900">Imate gospodarstvo?</h2>
        <p class="mt-1 text-gray-600">Predstavite se besplatno. U AgroLog profilu uključite <strong>Prezentaciju na AgroLogu</strong>, opišite što nudite i dodajte sliku. Prvu objavu pregledamo prije nego što postane javna.</p>
        <a href="{{ auth()->check() ? route('profile.edit') . '#prezentacija' : route('register') }}" class="inline-block mt-4 btn-primary">{{ auth()->check() ? 'Uredi prezentaciju' : 'Otvori besplatan račun' }}</a>
    </div>

    @if($karta->isNotEmpty())
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            (function () {
                const tocke = @json($karta);
                const map = L.map('karta-gospodarstava', { scrollWheelZoom: false });
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
                const e = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
                const granice = [];
                tocke.forEach(t => {
                    L.circleMarker([t.lat, t.lng], { radius: 9, color: '#033b2c', weight: 2, fillColor: '#86bc25', fillOpacity: .9 })
                        .addTo(map).bindPopup('<a href="' + e(t.u) + '" style="font-weight:700;color:#19653a">' + e(t.n) + '</a><br>' + e(t.m));
                    granice.push([t.lat, t.lng]);
                });
                if (granice.length === 1) map.setView(granice[0], 11); else map.fitBounds(granice, { padding: [30, 30], maxZoom: 12 });
            })();
        </script>
    @endif
@endsection
