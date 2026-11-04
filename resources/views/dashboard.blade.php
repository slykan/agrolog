<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dobrodošli, {{ auth()->user()->naziv_gospodarstva ?: auth()->user()->name }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Što je novo: najnovija novost, može se zatvoriti (pamti se u pregledniku po novosti) --}}
            @php
                $novost = \App\Http\Controllers\SadrzajController::novosti()[0] ?? null;
            @endphp
            @if($novost)
                <div x-data="{
                        kljuc: 'agrolog-novost-{{ $novost['slug'] }}',
                        vidljivo: false,
                        init() { try { this.vidljivo = localStorage.getItem(this.kljuc) !== 'zatvoreno' } catch (e) { this.vidljivo = true } },
                        zatvori() { this.vidljivo = false; try { localStorage.setItem(this.kljuc, 'zatvoreno') } catch (e) {} },
                     }"
                     x-show="vidljivo" x-cloak
                     class="relative bg-green-600 text-white rounded-xl p-6 pr-12 shadow-sm">
                    <button type="button" @click="zatvori()"
                            class="absolute top-3 right-3 w-8 h-8 rounded-full flex items-center justify-center text-white/80 hover:text-white hover:bg-white/15 transition"
                            aria-label="Zatvori">&#10005;</button>
                    <p class="text-xs font-extrabold uppercase tracking-widest text-green-100 mb-1">Što je novo!</p>
                    <h3 class="text-lg font-bold mb-1">{{ $novost['title'] }}</h3>
                    <p class="text-sm text-green-50 leading-relaxed mb-4">EPPO kod uz svaku kulturu, fenofaza (BBCH) pri prskanju i izvoz evidencije izravno u Ministarstvovu Excel tablicu — obvezni format od 1.1.2027.</p>
                    <a href="{{ route('novost', $novost['slug']) }}" class="inline-flex items-center gap-1.5 bg-white text-green-700 hover:bg-green-50 font-semibold text-sm px-4 py-2 rounded-lg transition">Više <span aria-hidden="true">→</span></a>
                </div>
            @endif

            {{-- Pozdrav --}}
            <div class="bg-white shadow-sm rounded-xl p-8">
                <h3 class="text-lg font-semibold text-green-700 mb-2">AgroLog &mdash; evidencija poljoprivrednih radova</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    Ovdje vodite evidenciju gnojidbe i prskanja vaših parcela sukladno zakonskim obavezama.
                    Unesite ARCOD parcele i kulture, a zatim dodajte zapise o gnojidbi i primjeni sredstava za zaštitu bilja.
                    Svaku evidenciju možete filtrirati, urediti i ispisati kao službeni obrazac.
                </p>
            </div>

            {{-- Brze veze --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <a href="{{ route('parcele') }}" class="bg-white shadow-sm rounded-xl p-8 hover:shadow-md transition group border border-transparent hover:border-green-200">
                    <div class="text-4xl mb-4">&#127807;</div>
                    <h4 class="font-semibold text-gray-800 group-hover:text-green-700 transition mb-1">Parcele &amp; Kulture</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">Upravljajte ARCOD parcelama i kulturama koje na njima uzgajate.</p>
                </a>
                <a href="{{ route('gnojidba') }}" class="bg-white shadow-sm rounded-xl p-8 hover:shadow-md transition group border border-transparent hover:border-green-200">
                    <div class="text-4xl mb-4">&#129517;</div>
                    <h4 class="font-semibold text-gray-800 group-hover:text-green-700 transition mb-1">Evidencija gnojidbe</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">Unosite i pregledavate zapise o primjeni gnojiva po parcelama.</p>
                </a>
                <a href="{{ route('prskanje') }}" class="bg-white shadow-sm rounded-xl p-8 hover:shadow-md transition group border border-transparent hover:border-green-200">
                    <div class="text-4xl mb-4">&#128167;</div>
                    <h4 class="font-semibold text-gray-800 group-hover:text-green-700 transition mb-1">Evidencija prskanja</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">Vodite evidenciju o upotrebi sredstava za zaštitu bilja.</p>
                </a>
            </div>

            {{-- Parcele i kulture --}}
            @php
                $parcele = auth()->user()->parcele()->with(['kulture' => fn($q) => $q->orderByDesc('godina')->orderBy('naziv')])->orderBy('arkod_broj')->get();
            @endphp

            @if($parcele->isNotEmpty())
            <div class="bg-white shadow-sm rounded-xl p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700">Moje parcele</h3>
                    <a href="{{ route('parcele') }}" class="text-xs text-green-700 hover:underline">Upravljaj &rarr;</a>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs text-gray-400 border-b">
                            <th class="text-left pb-1 font-medium">ARKOD</th>
                            <th class="text-right pb-1 font-medium">Površina</th>
                            <th class="text-left pb-1 pl-6 font-medium">Kulture</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parcele as $parcela)
                        <tr class="border-b border-gray-50">
                            <td class="py-1.5 font-medium text-gray-800">{{ $parcela->arkod_broj }}</td>
                            <td class="py-1.5 text-right text-gray-500">{{ number_format($parcela->povrsina_ha, 2) }} ha</td>
                            <td class="py-1.5 pl-6 text-gray-600">
                                {{ $parcela->kulture->map(fn($k) => $k->naziv.' ('.$k->godina.')')->join(', ') ?: '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Karta svih parcela (oblik iz ARKOD-a; parcele bez spremljenog oblika dohvate se u pozadini) --}}
            @php
                $zaKartu = $parcele->map(fn ($p) => [
                    'id'      => $p->id,
                    'arkod'   => $p->arkod_broj,
                    'poligon' => $p->arkod_poligon,
                    'ha'      => (float) $p->povrsina_ha,
                    'kulture' => $p->kulture->whereNull('zavrsena_at')->pluck('naziv')->join(', '),
                    'url'     => route('parcela.arkod', $p),
                ])->values();
            @endphp
            @include('partials.karta')
            <div class="bg-white shadow-sm rounded-xl p-6"
                 x-data="dashKarta(@js($zaKartu))" x-init="pokreni($refs.karta)">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700">Karta mojih parcela</h3>
                    <span class="text-xs text-gray-400" x-text="status"></span>
                </div>
                <div x-ref="karta" class="h-[28rem] rounded-lg ring-1 ring-gray-200 bg-gray-100"></div>
                <p class="mt-2 text-xs text-gray-400">Kotačićem miša ili gumbima +/− zumirate, povlačenjem pomičete kartu. Klik na parcelu prikazuje kulture.</p>
            </div>
            <script>
                function dashKarta(parcele) {
                    return {
                        status: '',
                        pokreni(el) {
                            const map = agroKarta.karta(el, { scrollWheelZoom: 'center' });
                            map.setView([45.1, 16.4], 7);   // Hrvatska dok se parcele ne učitaju
                            const sloj = L.featureGroup().addTo(map);
                            const crtaj = (p) => {
                                if (!p.poligon) return;
                                L.polygon(p.poligon, { color: '#3ee36f', weight: 2.5, fillOpacity: 0.3 })
                                    .bindTooltip(String(p.arkod), { permanent: true, direction: 'center', className: 'agro-oznaka' })
                                    .bindPopup(`<b>ARKOD ${p.arkod}</b><br>${p.ha.toFixed(2).replace('.', ',')} ha<br>${p.kulture || 'bez aktivnih kultura'}<br><a href="{{ route('parcele') }}">Parcele &amp; Kulture →</a>`)
                                    .addTo(sloj);
                            };
                            const prilagodi = () => { if (sloj.getLayers().length) map.fitBounds(sloj.getBounds(), { padding: [30, 30], maxZoom: 16 }); };

                            parcele.forEach(crtaj);
                            prilagodi();

                            const bez = parcele.filter(p => !p.poligon);
                            if (!bez.length) return;
                            this.status = 'Tražim ' + bez.length + ' parcela u ARKOD-u…';
                            Promise.all(bez.map(p => fetch(p.url, { headers: { Accept: 'application/json' } })
                                .then(r => r.ok ? r.json() : null).catch(() => null)
                                .then(d => { if (d && d.poligon) { p.poligon = d.poligon; crtaj(p); return true; } return false; })))
                                .then(rez => {
                                    const nema = rez.filter(x => !x).length;
                                    this.status = nema ? nema + ' parcela nije pronađeno u ARKOD-u' : '';
                                    prilagodi();
                                });
                        },
                    };
                }
            </script>
            @endif

            {{-- Kontakt --}}
            <div class="bg-green-50 border border-green-200 rounded-xl p-6 text-sm text-gray-600">
                <p>
                    Za prijedloge, dodatke ili prijavu problema obratite se na:
                    <a href="mailto:info@on-click.hr" class="font-semibold text-green-700 hover:underline">info@on-click.hr</a>
                </p>
            </div>

        </div>
    </div>
</x-app-layout>
