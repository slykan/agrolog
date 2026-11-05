<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dobrodošli, {{ auth()->user()->naziv_gospodarstva ?: auth()->user()->name }}
        </h2>
    </x-slot>

    <div class="py-3 sm:py-8 lg:py-10">
        <div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8 space-y-4 sm:space-y-8">

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
                    <p class="text-sm text-green-50 leading-relaxed mb-4">{{ $novost['opis'] ?? $novost['excerpt'] }}</p>
                    <a href="{{ route('novost', $novost['slug']) }}" class="inline-flex items-center gap-1.5 bg-white text-green-700 hover:bg-green-50 font-semibold text-sm px-4 py-2 rounded-lg transition">Više <span aria-hidden="true">→</span></a>
                    <a href="{{ route('novosti') }}" class="ml-3 text-sm text-green-50 hover:text-white underline underline-offset-2">Sve novosti</a>
                </div>
            @endif

            {{-- Karta mojih parcela — odmah ispod novosti --}}
            @php
                $parcele = auth()->user()->parcele()->with(['kulture' => fn($q) => $q->orderByDesc('godina')->orderBy('naziv')])->orderBy('arkod_broj')->get();
            @endphp
            @if($parcele->isNotEmpty())
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
                {{-- Prognoza za 5 dana za središte karte (Open-Meteo); prati pomicanje karte --}}
                <div class="mb-4" x-show="prognoza.length" x-cloak>
                    <div class="flex items-baseline justify-between mb-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Prognoza za područje na karti</span>
                        <span class="text-[11px] text-gray-400">Prskanje povoljno: vjetar &lt; 15 km/h, kiša &lt; 40 %, bez mraza, 5–30 °C</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                        <template x-for="d in prognoza" :key="d.datum">
                            <div class="rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-gray-700" x-text="d.dan"></span>
                                    <span class="text-xl" :title="d.opis" x-text="d.ikona"></span>
                                </div>
                                <div class="text-gray-800"><b x-text="d.max + '°'"></b> <span class="text-gray-400" x-text="d.min + '°'"></span></div>
                                <div class="text-xs text-gray-500"><span x-text="d.kisa + ' mm'"></span> · <span x-text="d.vjerojatnost + ' %'"></span></div>
                                <div class="text-xs text-gray-500" x-text="'vjetar ' + d.vjetar + ' km/h'"></div>
                                <div class="mt-1 text-[11px] font-semibold rounded px-1.5 py-0.5 inline-block"
                                     :class="d.prskanje ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'"
                                     x-text="d.prskanje ? 'za prskanje ✓' : 'nije za prskanje: ' + d.razlog"></div>
                            </div>
                        </template>
                    </div>
                </div>
                {{-- okvir karte: gumb za cijeli zaslon (Fullscreen API; na iPhoneu karta prekrije stranicu) --}}
                <div x-ref="okvir" :class="puni && 'karta-puni'" class="relative h-[28rem]"
                     @keydown.escape.window="puni && cijeliZaslon()">
                    <div x-ref="karta" class="h-full w-full rounded-lg ring-1 ring-gray-200 bg-gray-100"></div>
                    <button type="button" @click="cijeliZaslon()" :title="puni ? 'Zatvori cijeli zaslon (Esc)' : 'Karta preko cijelog zaslona'"
                            class="absolute top-3 right-3 z-[1001] inline-flex items-center gap-1.5 rounded-lg bg-white/95 px-3 py-2 text-sm font-medium text-gray-800 shadow ring-1 ring-black/10 hover:bg-white">
                        <svg x-show="! puni" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></svg>
                        <svg x-show="puni" x-cloak class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 4v5H4M15 4v5h5M9 20v-5H4M15 20v-5h5"/></svg>
                        <span x-text="puni ? 'Zatvori' : 'Cijeli zaslon'"></span>
                    </button>
                </div>
                <p class="mt-2 text-xs text-gray-400">Kotačićem miša ili gumbima +/− zumirate, povlačenjem pomičete kartu. Klik na parcelu prikazuje kulture. Prognoza: <a href="https://open-meteo.com/" target="_blank" rel="noopener" class="underline">Open-Meteo</a>.</p>
            </div>
            <script>
                function dashKarta(parcele) {
                    return {
                        status: '',
                        prognoza: [],
                        puni: false,
                        _map: null,

                        cijeliZaslon() {
                            this.puni = ! this.puni;
                            const okvir = this.$refs.okvir;
                            if (this.puni) {
                                document.body.style.overflow = 'hidden';
                                if (okvir.requestFullscreen) okvir.requestFullscreen().catch(() => {});
                            } else {
                                document.body.style.overflow = '';
                                if (document.fullscreenElement) document.exitFullscreen().catch(() => {});
                            }
                            setTimeout(() => this._map && this._map.invalidateSize(), 200);
                        },
                        zadnjeSrediste: null,

                        // WMO šifra vremena → ikona i opis
                        vrijeme(kod) {
                            if (kod === 0) return ['☀️', 'vedro'];
                            if (kod <= 2) return ['🌤️', 'pretežno sunčano'];
                            if (kod === 3) return ['☁️', 'oblačno'];
                            if (kod <= 48) return ['🌫️', 'magla'];
                            if (kod <= 57) return ['🌦️', 'rosulja'];
                            if (kod <= 67) return ['🌧️', 'kiša'];
                            if (kod <= 77) return ['🌨️', 'snijeg'];
                            if (kod <= 82) return ['🌧️', 'pljuskovi'];
                            if (kod <= 86) return ['🌨️', 'snježni pljuskovi'];
                            return ['⛈️', 'grmljavina'];
                        },

                        // dan za prskanje: bez jačeg vjetra i kiše, bez mraza, ni prehladno ni prevruće (djelovanje sredstava, isparavanje)
                        ocjenaPrskanja(vjetar, kisa, min, max) {
                            const razlozi = [];
                            if (vjetar >= 15) razlozi.push('vjetar');
                            if (kisa >= 40) razlozi.push('kiša');
                            if (min < 0) razlozi.push('mraz');
                            else if (max < 5) razlozi.push('hladno');
                            if (max > 30) razlozi.push('vruće');
                            return { prskanje: razlozi.length === 0, razlog: razlozi.join(', ') };
                        },

                        ucitajPrognozu(srediste) {
                            // ne ponavljaj za pomak manji od ~5 km
                            if (this.zadnjeSrediste && this.zadnjeSrediste.distanceTo(srediste) < 5000) return;
                            this.zadnjeSrediste = srediste;
                            const url = 'https://api.open-meteo.com/v1/forecast?latitude=' + srediste.lat.toFixed(3) + '&longitude=' + srediste.lng.toFixed(3)
                                + '&daily=weather_code,temperature_2m_max,temperature_2m_min,precipitation_sum,precipitation_probability_max,wind_speed_10m_max'
                                + '&timezone=Europe%2FZagreb&forecast_days=5';
                            const dani = ['ned', 'pon', 'uto', 'sri', 'čet', 'pet', 'sub'];
                            fetch(url).then(r => r.ok ? r.json() : null).then(j => {
                                if (!j || !j.daily) return;
                                const d = j.daily;
                                this.prognoza = d.time.map((datum, i) => {
                                    const [ikona, opis] = this.vrijeme(d.weather_code[i]);
                                    const vjetar = Math.round(d.wind_speed_10m_max[i]);
                                    const vjerojatnost = d.precipitation_probability_max[i] ?? 0;
                                    return {
                                        datum, ikona, opis, vjetar, vjerojatnost,
                                        dan: i === 0 ? 'danas' : dani[new Date(datum + 'T12:00:00').getDay()] + ' ' + datum.slice(8, 10) + '.' + datum.slice(5, 7) + '.',
                                        max: Math.round(d.temperature_2m_max[i]), min: Math.round(d.temperature_2m_min[i]),
                                        kisa: (d.precipitation_sum[i] ?? 0).toFixed(1).replace('.', ','),
                                        ...this.ocjenaPrskanja(vjetar, vjerojatnost, d.temperature_2m_min[i], d.temperature_2m_max[i]),
                                    };
                                });
                            }).catch(() => {});
                        },

                        pokreni(el) {
                            const map = agroKarta.karta(el, { scrollWheelZoom: 'center' });
                            this._map = map;
                            // izlaz iz cijelog zaslona tipkom Esc (preglednik) vrati kartu na mjesto
                            document.addEventListener('fullscreenchange', () => {
                                if (! document.fullscreenElement && this.puni) { this.puni = false; document.body.style.overflow = ''; setTimeout(() => map.invalidateSize(), 200); }
                            });
                            map.setView([45.1, 16.4], 7);   // Hrvatska dok se parcele ne učitaju
                            const sloj = L.featureGroup().addTo(map);
                            const crtaj = (p) => {
                                if (!p.poligon) return;
                                L.polygon(p.poligon, { color: '#86bc25', weight: 2.5, fillOpacity: 0.3 })
                                    .bindTooltip(String(p.arkod), { permanent: true, direction: 'center', className: 'agro-oznaka' })
                                    .bindPopup(`<b>ARKOD ${p.arkod}</b><br>${p.ha.toFixed(2).replace('.', ',')} ha<br>${p.kulture || 'bez aktivnih kultura'}<br><a href="{{ route('parcele') }}">Parcele &amp; Kulture →</a>`)
                                    .addTo(sloj);
                            };
                            const prilagodi = () => { if (sloj.getLayers().length) map.fitBounds(sloj.getBounds(), { padding: [30, 30], maxZoom: 16 }); };
                            let odgoda;
                            map.on('moveend', () => { clearTimeout(odgoda); odgoda = setTimeout(() => { if (sloj.getLayers().length) this.ucitajPrognozu(map.getCenter()); }, 600); });

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
            @else
                <a href="{{ route('parcele') }}" class="flex items-center gap-4 bg-white shadow-sm rounded-xl p-6 border border-dashed border-green-300 hover:bg-green-50 transition">
                    <span class="text-3xl" aria-hidden="true">&#128506;</span>
                    <span class="text-sm text-gray-600"><b class="block text-gray-900">Vaše parcele na karti</b>Dodajte prvu ARKOD parcelu i AgroLog će je pokazati na satelitskoj karti sa službenom površinom.</span>
                </a>
            @endif

            {{-- Pregled gospodarstva: aktivnosti po mjesecima za razdoblje --}}
            @if($parcele->isNotEmpty())
                <livewire:dash-pregled />
            @endif

            {{-- Skladište: što je pri kraju ili u minusu (samo ako korisnik koristi skladište) --}}
            @if(auth()->user()->skladista()->exists())
                @php
                    $zaliha = \App\Support\Skladiste::stanje(auth()->user());
                    $viseSkladista = $zaliha->pluck('skladiste_id')->unique()->count() > 1;
                    $upozorenja = $zaliha->whereIn('status', ['minus', 'malo'])->sortBy(fn ($a) => $a['status'] === 'minus' ? 0 : 1);
                @endphp
                @if($upozorenja->isNotEmpty())
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <h3 class="font-semibold text-amber-900">Skladište: pri kraju ili u minusu</h3>
                            <a href="{{ route('skladiste') }}" class="text-sm text-amber-800 underline underline-offset-2 whitespace-nowrap">Otvori skladište →</a>
                        </div>
                        <ul class="text-sm space-y-1">
                            @foreach($upozorenja->take(6) as $a)
                                <li class="flex justify-between gap-3">
                                    <span class="text-gray-800">{{ $a['naziv'] }}@if($viseSkladista) <span class="text-xs text-gray-500">· {{ $a['skladiste'] }}</span>@endif</span>
                                    <span class="font-semibold whitespace-nowrap {{ $a['status'] === 'minus' ? 'text-red-700' : 'text-amber-800' }}">{{ \App\Support\Skladiste::broj($a['stanje'], 3) }} {{ $a['jedinica'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                        @if($upozorenja->count() > 6)<p class="text-xs text-amber-800 mt-2">i još {{ $upozorenja->count() - 6 }}…</p>@endif
                    </div>
                @endif
            @endif

            {{-- Mehanizacija: istekli rokovi i oni unutar razdoblja podsjetnika --}}
            @php
                $hitniRokovi = \App\Support\Mehanizacija::aktivniRokovi(auth()->user())->filter(fn ($r) => $r->status !== 'ok');
            @endphp
            @if($hitniRokovi->isNotEmpty())
                <div class="bg-white border border-amber-200 rounded-xl p-5">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <h3 class="font-semibold text-gray-900">Mehanizacija: rokovi</h3>
                        <a href="{{ route('mehanizacija') }}" class="text-sm text-green-700 underline underline-offset-2 whitespace-nowrap">Otvori mehanizaciju →</a>
                    </div>
                    <ul class="text-sm space-y-1">
                        @foreach($hitniRokovi->take(6) as $r)
                            <li class="flex justify-between gap-3">
                                <span class="text-gray-800">{{ $r->stroj->naziv }} <span class="text-gray-500">· {{ $r->opis }}</span></span>
                                <span class="font-semibold whitespace-nowrap {{ $r->status === 'istekao' ? 'text-red-700' : 'text-amber-800' }}">
                                    {{ $r->dana < 0 ? 'isteklo ' . $r->datum_isteka->format('d.m.Y.') : ($r->dana === 0 ? 'danas' : 'za ' . $r->dana . ' d. (' . $r->datum_isteka->format('d.m.Y.') . ')') }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

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
