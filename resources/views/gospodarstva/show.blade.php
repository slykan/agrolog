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
            <div class="rounded-2xl border border-gray-200 p-5 space-y-4" x-data="{ k: null, greska: false, ucitava: false,
                    async prikazi() { this.ucitava = true; try { const r = await fetch(@js(route('gospodarstvo.telefon', $p->slug)), { headers: { Accept: 'application/json' } }); if (! r.ok) throw 0; this.k = await r.json(); } catch (e) { this.greska = true; } this.ucitava = false; } }">
                <h2 class="font-bold text-gray-900">Kontakt</h2>
                <div>
                    <button type="button" x-show="! k" @click="prikazi()" :disabled="ucitava" class="btn-primary w-full">📞 Prikaži broj</button>
                    <template x-if="k">
                        <div class="space-y-2">
                            <a :href="'tel:' + k.tel" class="block text-xl font-bold text-gray-900 hover:text-green-800" x-text="k.telefon"></a>
                            <div class="flex gap-2">
                                <a x-show="k.whatsapp" :href="k.whatsapp" target="_blank" rel="noopener" class="flex-1 rounded-lg bg-[#25D366] px-3 py-2 text-center text-sm font-semibold text-white">WhatsApp</a>
                                <a x-show="k.viber" :href="k.viber" class="flex-1 rounded-lg bg-[#7360F2] px-3 py-2 text-center text-sm font-semibold text-white">Viber</a>
                            </div>
                        </div>
                    </template>
                    <p x-show="greska" x-cloak class="mt-2 text-sm text-red-700">Broj se trenutno ne može prikazati. Pokušajte za minutu.</p>
                </div>
                @if($p->email)
                    <p class="text-sm"><span class="text-gray-500">E-mail:</span> <a href="mailto:{{ $p->email }}" class="text-green-700 underline break-all">{{ $p->email }}</a></p>
                @endif
                @if($p->adresa)
                    <p class="text-sm"><span class="text-gray-500">Adresa:</span> {{ $p->adresa }}</p>
                @endif
                @if($p->kucni_prag)
                    <div class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-900">
                        <strong>Prodaja na kućnom pragu</strong>
                        @if($p->radno_vrijeme)<span class="block">{{ $p->radno_vrijeme }}</span>@endif
                    </div>
                @endif
                @php $veze = array_filter(['Web' => $p->web, 'Facebook' => $p->facebook, 'Instagram' => $p->instagram]); @endphp
                @if($veze)
                    <div class="flex flex-wrap gap-3 text-sm">
                        @foreach($veze as $naziv => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener nofollow ugc" class="text-green-700 underline">{{ $naziv }}</a>
                        @endforeach
                    </div>
                @endif
            </div>

            @if($p->lat !== null)
                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
                <div class="mt-4 rounded-2xl overflow-hidden border border-gray-200 isolate">
                    <div id="karta-gospodarstva" class="h-56" role="region" aria-label="Približna lokacija"></div>
                </div>
                <p class="mt-1 text-xs text-gray-500">Približna lokacija (oko 1 km).</p>
                <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
                <script>
                    (function () {
                        const t = [{{ (float) $p->lat }}, {{ (float) $p->lng }}];
                        const map = L.map('karta-gospodarstva', { scrollWheelZoom: false }).setView(t, 11);
                        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 13, attribution: '&copy; OpenStreetMap' }).addTo(map);
                        L.circle(t, { radius: 1200, color: '#033b2c', weight: 2, fillColor: '#86bc25', fillOpacity: .3 }).addTo(map);
                    })();
                </script>
            @endif
        </aside>
    </div>

    <p class="mt-12 text-xs text-gray-400">Podatke je objavilo gospodarstvo i za njih odgovara. AgroLog ne posreduje u prodaji. <a href="{{ route('contact') }}" class="underline">Prijavite netočan ili neprimjeren sadržaj</a>.</p>
@endsection
