@extends('layouts.public', [
    'title' => $p->naziv . ', ' . $p->mjesto,
    'description' => \Illuminate\Support\Str::limit($p->naziv . ' iz mjesta ' . $p->mjesto . ': ' . implode(', ', $p->oznake ?? []) . '. ' . $p->opis, 155),
    'active' => 'gospodarstva',
    'ads' => false,
    'jsonLd' => array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => $p->naziv,
        'description' => \Illuminate\Support\Str::limit($p->opis, 300),
        'image' => $p->slikaUrl(),
        'url' => route('gospodarstvo', $p->slug),
        'address' => ['@type' => 'PostalAddress', 'addressLocality' => $p->mjesto, 'addressRegion' => $p->zupanija, 'addressCountry' => 'HR'],
    ]),
])

@section('content')
    <a href="{{ route('gospodarstva') }}" class="text-sm text-green-700 hover:underline">&larr; Sva gospodarstva</a>

    @if($p->slika)
        <img src="{{ $p->slikaUrl() }}" alt="{{ $p->naziv }}" class="mt-4 w-full max-h-[28rem] object-cover rounded-2xl border border-gray-200">
    @endif

    <div class="mt-6 flex flex-col gap-8 lg:flex-row">
        <div class="flex-1 min-w-0">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">{{ $p->naziv }}</h1>
            <p class="mt-1 text-gray-500">{{ $p->mjesto }} · {{ $p->zupanija }}</p>

            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($p->oznake ?? [] as $o)
                    <a href="{{ route('gospodarstva', ['oznaka' => $o]) }}" class="rounded-full bg-green-50 px-3 py-1 text-sm text-green-900 hover:bg-green-100">{{ $o }}</a>
                @endforeach
            </div>

            @if($evidencija)
                <p class="mt-4 inline-flex items-center gap-2 rounded-lg border border-green-200 bg-white px-3 py-1.5 text-sm text-green-900">
                    <img src="{{ asset('favicon-32x32.png') }}" alt="" class="h-4 w-4">
                    Vodi evidenciju prskanja i gnojidbe u AgroLogu
                </p>
            @endif

            <div class="mt-6 text-gray-700 leading-relaxed whitespace-pre-line break-words">{{ $p->opis }}</div>
        </div>

        {{-- kontakt --}}
        <aside class="lg:w-80 shrink-0">
            @include('gospodarstva._kontakt', ['p' => $p])

            @if($p->lat !== null)
                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
                <div class="mt-4 rounded-2xl overflow-hidden border border-gray-200 isolate">
                    <div id="karta-gospodarstva" class="h-56" role="region" aria-label="Lokacija gospodarstva"></div>
                </div>
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ (float) $p->lat }},{{ (float) $p->lng }}" target="_blank" rel="noopener" class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-green-700 underline">Upute za dolazak →</a>
                <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
                <script>
                    (function () {
                        const t = [{{ (float) $p->lat }}, {{ (float) $p->lng }}];
                        const map = L.map('karta-gospodarstva', { scrollWheelZoom: false }).setView(t, 15);
                        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(map);
                        L.circleMarker(t, { radius: 10, color: '#033b2c', weight: 3, fillColor: '#86bc25', fillOpacity: 1 }).addTo(map);
                    })();
                </script>
            @endif
        </aside>
    </div>

    @if($proizvodi->isNotEmpty())
        <section id="proizvodi" class="mt-12 scroll-mt-24">
            <div class="flex flex-wrap items-end justify-between gap-2">
                <h2 class="text-2xl font-bold text-gray-900">Proizvodi</h2>
                <a href="{{ route('trznica', ['prodavac' => $p->slug]) }}" class="text-sm font-medium text-green-700 underline">Svi oglasi na Tržnici →</a>
            </div>
            <div class="mt-4 grid gap-4 grid-cols-2 lg:grid-cols-3">
                @foreach($proizvodi->take(6) as $pr)
                    @php $pr->setRelation('prezentacija', $p); @endphp
                    @include('trznica._kartica', ['pr' => $pr, 'bezProdavaca' => true])
                @endforeach
            </div>
        </section>
    @endif

    <p class="mt-12 text-xs text-gray-400">Podatke je objavilo gospodarstvo i za njih odgovara. AgroLog ne posreduje u prodaji. <a href="{{ route('contact') }}" class="underline">Prijavite netočan ili neprimjeren sadržaj</a>.</p>
@endsection
