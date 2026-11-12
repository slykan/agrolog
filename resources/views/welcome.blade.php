<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AgroLog – Digitalni dnevnik gnojidbe i prskanja</title>
    <meta name="description" content="AgroLog je jednostavna web aplikacija za vođenje evidencije gnojidbe i prskanja po ARKOD parcelama. Digitalizirajte svoje agro zapise i ispišite ih u standardnom obliku.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ route('home') }}">
    <meta name="google-adsense-account" content="ca-pub-3054841074530422">
    <meta name="theme-color" content="#022a20">
    @include('partials.favicon')
    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('home') }}">
    <meta property="og:title" content="AgroLog – Digitalni dnevnik gnojidbe i prskanja">
    <meta property="og:description" content="Vodite evidenciju gnojidbe i prskanja po ARKOD parcelama. Digitalizirajte svoje agro zapise i ispišite ih u standardnom obliku.">
    <meta property="og:image" content="{{ asset('images/naslovna/hero-polje.jpg') }}">
    <meta property="og:locale" content="hr_HR">
    <link rel="preload" as="image" href="{{ asset('images/naslovna/hero-polje.jpg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-800 bg-white">

    {{-- NAV --}}
    @include('partials.nav', ['siroko' => true])

    {{-- HERO: tamno, slike koje se izmjenjuju desno (lijevo zatamnjeno), uz svaku sliku svoj tekst --}}
    @php
        $slajdovi = [
            ['slika' => 'hero-polje.jpg', 'pozicija' => 'object-[30%_center]', 'alt' => 'Poljoprivrednik na polju drži tablet s AgroLog evidencijom gnojidbe',
             'nadnaslov' => 'Digitalni alati za poljoprivrednike', 'naslov' => 'Službeni obrazac', 'istaknuto' => 'jednim klikom.',
             'opis' => 'Vodite evidenciju gnojidbe i prskanja po ARKOD parcelama i ispišite PDF u skladu s AGRONET zahtjevima — brzo, jednostavno i bez gubitka podataka.'],
            ['slika' => 'hero-karta.jpg', 'pozicija' => 'object-[80%_center]', 'alt' => 'AgroLog na laptopu i mobitelu: karta ARKOD parcela s prognozom za prskanje i pregled gospodarstva',
             'nadnaslov' => 'Karta i prognoza', 'naslov' => 'Sve parcele', 'istaknuto' => 'na jednoj karti.',
             'opis' => 'Upišite ARKOD broj i parcela se pojavi na satelitskoj karti sa službenom površinom. Iznad karte je prognoza za pet dana — odmah vidite koji je dan za prskanje.'],
            ['slika' => 'hero-sve.jpg', 'pozicija' => 'object-[62%_center]', 'alt' => 'AgroLog na tabletu, laptopu i mobitelu: strojevi i vozila, karta parcela i pregled gospodarstva',
             'nadnaslov' => 'Sve na jednom mjestu', 'naslov' => 'Parcele, strojevi', 'istaknuto' => 'i troškovi.',
             'opis' => 'Registracije, osiguranja i servisi s podsjetnikom mailom, skladište koje samo oduzima potrošnju i trošak po hektaru svake kulture.'],
            ['slika' => 'hero-polje-mobitel.jpg', 'pozicija' => 'object-[62%_center]', 'alt' => 'Poljoprivrednik u polju s mobitelom i laptopom na kojima je AgroLog pregled gospodarstva',
             'nadnaslov' => 'Pregled gospodarstva', 'naslov' => 'Gospodarstvo', 'istaknuto' => 'na dlanu.',
             'opis' => 'Aktivnosti po mjesecima, usporedba s prošlom godinom i popis onoga što treba dopuniti za evidenciju 2027. — na mobitelu, usred polja.'],
        ];
    @endphp
    <section class="relative overflow-hidden bg-[#022a20] text-white"
             x-data="{
                 i: 0, n: {{ count($slajdovi) }}, timer: null,
                 kreni() { if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return; this.stani(); this.timer = setInterval(() => this.i = (this.i + 1) % this.n, 7000) },
                 stani() { clearInterval(this.timer) },
                 idi(k) { this.i = (k + this.n) % this.n; this.kreni() },
             }"
             x-init="kreni()" @mouseenter="stani()" @mouseleave="kreni()">
        <div class="absolute inset-0 lg:left-[40%] 2xl:left-[34%] overflow-hidden">
            @foreach($slajdovi as $k => $sl)
                <img src="{{ asset('images/naslovna/' . $sl['slika']) }}" alt="{{ $sl['alt'] }}"
                     class="absolute inset-0 w-full h-full object-cover {{ $sl['pozicija'] }} transition-opacity duration-1000" @if($k > 0) x-cloak @endif
                     :class="i === {{ $k }} ? 'opacity-100' : 'opacity-0'" @if($k === 0) fetchpriority="high" @else loading="lazy" decoding="async" @endif>
            @endforeach
        </div>
        {{-- zatamnjenje: na mobitelu preko cijele slike, na računalu prijelaz s lijeva --}}
        <div class="absolute inset-0 bg-[#022a20]/80 lg:bg-transparent lg:bg-gradient-to-r lg:from-[#022a20] lg:from-[40%] lg:via-[#022a20]/70 lg:via-[52%] lg:to-transparent lg:to-[75%] 2xl:from-[34%] 2xl:via-[44%] 2xl:to-[66%]"></div>
        <div class="absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-[#022a20] to-transparent"></div>

        <div class="relative px-6 sm:px-10 lg:px-14 xl:px-20 pt-16 pb-14 lg:pt-24 lg:pb-20">
            <div class="max-w-xl">
                {{-- istaknuta novost --}}
                @php
                    $istaknuta = \App\Http\Controllers\SadrzajController::novosti()[0] ?? null;
                @endphp
                @if($istaknuta)
                    <a href="{{ route('novost', $istaknuta['slug']) }}" class="group inline-flex items-center gap-2 mb-7 max-w-full rounded-full bg-white/10 hover:bg-white/15 ring-1 ring-[#86bc25]/50 pl-1.5 pr-4 py-1.5 text-sm transition">
                        <span class="flex-none rounded-full bg-[#86bc25] text-[#022a20] text-[11px] font-extrabold uppercase tracking-wide px-2.5 py-0.5">Novo</span>
                        <span class="truncate text-white/90">{{ $istaknuta['kratko'] }}</span>
                        <span class="flex-none text-[#86bc25] group-hover:translate-x-0.5 transition" aria-hidden="true">→</span>
                    </a>
                @endif
                <div class="grid">
                    @foreach($slajdovi as $k => $sl)
                        <div class="[grid-area:1/1] transition-all duration-700" @if($k > 0) x-cloak @endif
                             :class="i === {{ $k }} ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2 pointer-events-none'" :aria-hidden="i !== {{ $k }}"
                             >
                            <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-[0.35em] text-white/80 mb-6">{{ $sl['nadnaslov'] }}</p>
                            {!! $k === 0 ? '<h1' : '<p' !!} class="text-[2.6rem] leading-[1.05] sm:text-6xl lg:text-[4.2rem] font-extrabold tracking-tight">
                                {{ $sl['naslov'] }}<br>
                                <span class="relative inline-block text-[#86bc25]">
                                    {{ $sl['istaknuto'] }}
                                    <svg class="absolute left-0 -bottom-3 w-[70%] h-4 text-[#86bc25]" viewBox="0 0 300 16" fill="none" preserveAspectRatio="none" aria-hidden="true"><path d="M3 12C70 4 170 2 297 6" stroke="currentColor" stroke-width="5" stroke-linecap="round"/></svg>
                                </span>
                            {!! $k === 0 ? '</h1>' : '</p>' !!}
                            <p class="mt-8 text-lg sm:text-xl text-white/85 leading-relaxed">{{ $sl['opis'] }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="mt-9 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-[#86bc25] hover:bg-[#9ccf3e] text-[#022a20] font-bold px-7 py-4 rounded-xl transition shadow-[0_10px_40px_-10px_rgba(134,188,37,.7)]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Isprobaj besplatno
                        <span aria-hidden="true">→</span>
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 border-2 border-[#86bc25]/70 hover:border-[#86bc25] hover:bg-[#86bc25]/10 text-white font-semibold px-7 py-[14px] rounded-xl transition">Moj račun <span aria-hidden="true">→</span></a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 border-2 border-[#86bc25]/70 hover:border-[#86bc25] hover:bg-[#86bc25]/10 text-white font-semibold px-7 py-[14px] rounded-xl transition">Već imam račun <span aria-hidden="true">→</span></a>
                    @endauth
                </div>

                {{-- mobilne aplikacije: još nisu u trgovinama, pa namjerno nisu poveznice (i bez službenih Google Play / App Store oznaka) --}}
                <div class="mt-5 flex flex-wrap items-center gap-3">
                    @foreach ([['Android', 'Aplikacija za Android'], ['iPhone', 'Aplikacija za iPhone']] as [$sustav, $opis])
                        <span title="{{ $opis }}: uskoro"
                              class="relative inline-flex items-center gap-2.5 rounded-xl border border-white/20 bg-white/5 px-4 py-2.5 text-white/55 cursor-not-allowed select-none">
                            @if ($sustav === 'Android')
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7.2 9.5h9.6c.4 0 .7.3.7.7v6.6c0 .6-.5 1.1-1.1 1.1h-.8v2.4a1 1 0 01-2 0v-2.4h-3.2v2.4a1 1 0 01-2 0v-2.4h-.8c-.6 0-1.1-.5-1.1-1.1v-6.6c0-.4.3-.7.7-.7zM4.6 9.6a1 1 0 011 1v4.6a1 1 0 01-2 0v-4.6a1 1 0 011-1zm14.8 0a1 1 0 011 1v4.6a1 1 0 01-2 0v-4.6a1 1 0 011-1zM8.6 4.2l-.9-1.5a.3.3 0 01.5-.3l.9 1.6A6 6 0 0112 3.4c1 0 2 .2 2.9.6l.9-1.6a.3.3 0 01.5.3l-.9 1.5A5 5 0 0117.6 8.7H6.4a5 5 0 012.2-4.5zM10 6.3a.6.6 0 100 1.2.6.6 0 000-1.2zm4 0a.6.6 0 100 1.2.6.6 0 000-1.2z"/></svg>
                            @else
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="7" y="2.5" width="10" height="19" rx="2.4"/><path stroke-linecap="round" d="M10.5 5h3M11 18.5h2"/></svg>
                            @endif
                            <span class="leading-tight">
                                <span class="block text-[10px] uppercase tracking-wider text-white/45">Aplikacija za</span>
                                <span class="block text-sm font-semibold">{{ $sustav }}</span>
                            </span>
                            <span class="ml-1 rounded-full bg-[#86bc25] px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide text-[#022a20]">Uskoro</span>
                        </span>
                    @endforeach
                </div>

                <ul class="mt-12 grid grid-cols-2 sm:grid-cols-4 gap-y-5 sm:divide-x sm:divide-white/15 text-sm">
                    <li class="flex items-center gap-2.5 sm:pr-4">
                        <svg class="w-8 h-8 flex-none text-[#86bc25]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M5 21c0-9 5-15 15-17-1 10-6 15-15 17zm0 0l7-7"/></svg>
                        <span class="leading-tight text-white/90">Potpuno<br>besplatno</span>
                    </li>
                    <li class="flex items-center gap-2.5 sm:px-4">
                        <svg class="w-8 h-8 flex-none text-[#86bc25]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h4m4 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span class="leading-tight text-white/90">PDF za<br>AGRONET</span>
                    </li>
                    <li class="flex items-center gap-2.5 sm:px-4">
                        <svg class="w-8 h-8 flex-none text-[#86bc25]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span class="leading-tight text-white/90 whitespace-nowrap">SZB prema<br>EU 2023/564</span>
                    </li>
                    <li class="flex items-center gap-2.5 sm:pl-4">
                        <svg class="w-8 h-8 flex-none text-[#86bc25]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-5.5-7-11a7 7 0 0114 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                        <span class="leading-tight text-white/90">100% Made<br>in Croatia</span>
                    </li>
                </ul>

                <div class="mt-10 flex items-center gap-3" aria-label="Slike">
                    <button type="button" @click="idi(i - 1)" class="w-9 h-9 rounded-full ring-1 ring-white/25 hover:ring-[#86bc25] hover:text-[#86bc25] flex items-center justify-center transition" aria-label="Prethodna slika">‹</button>
                    @foreach($slajdovi as $k => $sl)
                        <button type="button" @click="idi({{ $k }})" aria-label="{{ $sl['nadnaslov'] }}"
                                class="h-2 w-2 rounded-full bg-white/35 transition-all duration-500"
                                :class="i === {{ $k }} ? '!w-8 !bg-[#86bc25]' : 'hover:bg-white/60'"></button>
                    @endforeach
                    <button type="button" @click="idi(i + 1)" class="w-9 h-9 rounded-full ring-1 ring-white/25 hover:ring-[#86bc25] hover:text-[#86bc25] flex items-center justify-center transition" aria-label="Sljedeća slika">›</button>
                </div>
            </div>
        </div>
    </section>

    {{-- ZAŠTO BESPLATNO --}}
    <section class="bg-[#022a20] pb-16">
        <div class="max-w-6xl mx-auto px-6">
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#033b2c] to-[#0d4f34] px-8 py-8 md:px-10 flex flex-col md:flex-row items-center gap-6 text-center md:text-left ring-1 ring-[#86bc25]/30">
                <div class="absolute -right-10 -top-10 w-48 h-48 rounded-full bg-[#86bc25]/20 blur-3xl" aria-hidden="true"></div>
                <span class="relative flex-shrink-0 bg-[#86bc25] text-[#022a20] text-sm font-extrabold uppercase tracking-widest px-5 py-2.5 rounded-xl whitespace-nowrap">0 € — zauvijek</span>
                <div class="relative">
                    <p class="text-white font-bold text-lg mb-1">Zašto besplatno?</p>
                    <p class="text-green-100/90 leading-relaxed text-sm md:text-base">
                        Vjerujemo da digitalni alati trebaju biti dostupni svim poljoprivrednicima — bez pretplate, bez kreditne kartice i bez skrivenih troškova. Naša misija je olakšati svakodnevni posao na gospodarstvu.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- oglas u bijelom dijelu, odmah ispod tamnog vrha stranice --}}
    @include('partials.oglas', ['mjesto' => 'naslovna', 'omotac' => 'bg-white pt-12', 'klasa' => 'max-w-6xl mx-auto px-6'])

    {{-- PREPORUKE: agronom i gospodarstvo na istom mjestu --}}
    <section id="agronomi" class="scroll-mt-24 pt-16 bg-white">
        <div class="max-w-6xl mx-auto px-6">
            <div class="relative overflow-hidden rounded-3xl bg-[#022a20] text-white p-7 md:p-12">
                <div class="absolute -left-20 -bottom-24 w-72 h-72 rounded-full bg-[#86bc25]/15 blur-3xl" aria-hidden="true"></div>
                <div class="relative grid gap-10 lg:grid-cols-2 lg:items-center">
                    <div>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider bg-[#86bc25] text-[#022a20] rounded-full px-2.5 py-1">Novo · za agronome i gospodarstva</span>
                        <h2 class="mt-4 text-3xl md:text-4xl font-extrabold tracking-tight leading-tight">Od agronomove preporuke do zapisa u evidenciji</h2>
                        <p class="mt-4 text-white/80 text-lg leading-relaxed">Agronom jednim slanjem javi preporuku svim klijentima s tom kulturom. Gospodarstvo odgovori, a kad posao obavi, zapis je već popunjen.</p>
                        <ul class="mt-6 space-y-2.5 text-white/90">
                            @foreach(['Provjera sredstva u registru FIS-a prije slanja', 'Prihvaćanje, odbijanje ili pitanje izravno uz preporuku', 'Obavijest u aplikaciji, mailom i na mobitel', 'Popunjen unos u evidenciju kad je posao stvarno obavljen'] as $stavka)
                                <li class="flex gap-3"><span class="text-[#86bc25] font-bold" aria-hidden="true">✓</span><span>{{ $stavka }}</span></li>
                            @endforeach
                        </ul>
                        <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3">
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-xl bg-[#86bc25] px-6 py-3 font-bold text-[#022a20] transition hover:bg-[#9ad033]">Registrirajte se besplatno</a>
                            <a href="{{ route('novost', 'preporuke-savjetnika') }}" class="font-semibold text-white underline underline-offset-4 hover:text-[#cfe6a9]">Kako radi?</a>
                        </div>
                    </div>
                    {{-- primjer preporuke, ne stvarni podaci --}}
                    <div class="rounded-2xl bg-white text-gray-800 p-5 shadow-2xl ring-1 ring-black/5 text-sm" aria-label="Primjer preporuke">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs text-gray-500">Ana Kovač, agronom · 06.11.2026.</p>
                                <p class="mt-0.5 text-base font-bold text-gray-900">Gnojidba: KAN</p>
                            </div>
                            <span class="flex-none rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-800">čeka odgovor</span>
                        </div>
                        <p class="mt-3 text-gray-700">Pšenica · ARKOD 1515224 · 200 kg/ha</p>
                        <p class="mt-1 text-gray-600">Prihrana prije kiše.</p>
                        <div class="mt-4 flex flex-wrap gap-2 text-xs font-semibold" aria-hidden="true">
                            <span class="rounded bg-[#4f9a3a] px-3 py-1.5 text-white">Prihvaćam</span>
                            <span class="rounded bg-blue-500 px-3 py-1.5 text-white">Imam pitanje</span>
                            <span class="rounded bg-red-500 px-3 py-1.5 text-white">Ne prihvaćam</span>
                        </div>
                        <div class="mt-4 border-t border-gray-100 pt-3">
                            <p class="flex gap-2 font-medium text-green-800"><span aria-hidden="true">✓</span> Napravljeno: zapis je popunjen, provjerite i spremite.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- NOVO U AGROLOGU: upravljanje gospodarstvom (FIS provjera, skladište, troškovi i dobit, mehanizacija, savjetnik, izvještaji) --}}
    <section class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-6">
            <div class="max-w-3xl mb-12">
                <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.3em] text-green-700">
                    <span class="w-2 h-2 rounded-full bg-[#86bc25]"></span> Novo u AgroLogu
                </span>
                <h2 class="mt-3 text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight">Više od evidencije — cijelo gospodarstvo na jednom mjestu</h2>
                <p class="mt-3 text-gray-600 text-lg">Provjera prskanja prema službenom registru, skladište koje se samo vodi, troškovi i dobit po hektaru, rokovi za strojeve i suradnja sa savjetnikom. Sve besplatno.</p>
            </div>

            <div class="grid gap-5 lg:grid-cols-3">
                {{-- 1. FIS provjera (istaknuto) --}}
                <div class="lg:col-span-2 relative overflow-hidden rounded-3xl bg-[#022a20] text-white p-8 md:p-10">
                    <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-[#86bc25]/15 blur-3xl" aria-hidden="true"></div>
                    <div class="relative grid gap-8 md:grid-cols-2 md:items-center">
                        <div>
                            <span class="text-[11px] font-extrabold uppercase tracking-wider bg-[#86bc25] text-[#022a20] rounded-full px-2.5 py-1">Pametna provjera</span>
                            <h3 class="mt-4 text-2xl font-extrabold leading-tight">Prskanje provjereno prema registru FIS-a</h3>
                            <p class="mt-3 text-white/80 leading-relaxed">Odaberete sredstvo i kulturu — AgroLog odmah javi je li sredstvo registrirano za tu kulturu, do kada traje karenca i koliko je tretmana dozvoljeno. Tako izbjegnete kaznu prije nego što se dogodi.</p>
                        </div>
                        {{-- mini prikaz provjere --}}
                        <div class="rounded-2xl bg-white text-gray-800 p-4 shadow-2xl ring-1 ring-black/5 text-sm">
                            <p class="text-[11px] font-semibold text-gray-500 mb-2">Provjera prema registru FIS-a</p>
                            <p class="flex gap-2 text-green-800"><span>✓</span> Registriran za kulturu Pšenica.</p>
                            <p class="flex gap-2 text-gray-700 mt-1"><span class="text-sky-600">ℹ</span> Karenca 35 dana: žetva ne prije 05.06.2026.</p>
                            <p class="flex gap-2 text-amber-800 font-medium mt-1"><span>⚠</span> Ovo je 3. tretman, a dozvoljena su najviše 2.</p>
                            <p class="mt-2 pt-2 border-t border-gray-100 text-[11px] text-green-700">▸ Doza i način primjene iz registracije</p>
                        </div>
                    </div>
                </div>

                {{-- 2. Pristup savjetniku --}}
                <div class="rounded-3xl bg-[#f3f7f1] ring-1 ring-black/5 p-7 flex flex-col">
                    <div class="w-11 h-11 rounded-xl bg-[#022a20] flex items-center justify-center mb-4"><svg class="w-6 h-6 text-[#86bc25]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
                    <h3 class="text-lg font-bold text-gray-900">Pozovite savjetnika</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Agronom, savjetodavna služba ili zadruga vidi — ili vodi — vašu evidenciju. Svaka izmjena se bilježi, a pristup ukidate jednim klikom.</p>
                    <div class="mt-auto pt-5 space-y-1.5 text-xs">
                        <p class="flex justify-between rounded-lg bg-white px-3 py-2 ring-1 ring-black/5"><span class="font-semibold text-gray-800">Ana Kovač</span><span class="text-green-700">Pregled i unos</span></p>
                        <p class="text-gray-500 px-1">05.11.2026. · Prskanje: novi zapis</p>
                    </div>
                </div>

                {{-- 3. Skladište --}}
                <div class="rounded-3xl bg-[#f3f7f1] ring-1 ring-black/5 p-7 flex flex-col">
                    <div class="w-11 h-11 rounded-xl bg-[#022a20] flex items-center justify-center mb-4"><svg class="w-6 h-6 text-[#86bc25]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
                    <h3 class="text-lg font-bold text-gray-900">Skladište koje se samo vodi</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Upišete nabavu, a potrošnju iz prskanja i gnojidbe AgroLog oduzima sam. Više skladišta, prijenos i inventura.</p>
                    <div class="mt-auto pt-5 space-y-1.5 text-xs">
                        <p class="flex justify-between rounded-lg bg-white px-3 py-2 ring-1 ring-black/5"><span class="text-gray-800">KAN 27% N</span><span class="font-semibold text-green-800">1.840 kg</span></p>
                        <p class="flex justify-between rounded-lg bg-amber-50 px-3 py-2 ring-1 ring-amber-200"><span class="text-gray-800">Roundup</span><span class="font-semibold text-amber-800">4 L · pri kraju</span></p>
                    </div>
                </div>

                {{-- 4. Troškovi i dobit --}}
                <div class="rounded-3xl bg-[#f3f7f1] ring-1 ring-black/5 p-7 flex flex-col">
                    <div class="w-11 h-11 rounded-xl bg-[#022a20] flex items-center justify-center mb-4"><svg class="w-6 h-6 text-[#86bc25]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div>
                    <h3 class="text-lg font-bold text-gray-900">Troškovi i dobit po hektaru</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Koliko vas stoji hektar pšenice, a koliko donosi kukuruz? Prinos, cijena i potpore — dobit po parceli i kulturi.</p>
                    <div class="mt-auto pt-5 space-y-2 text-xs">
                        @foreach([['Kukuruz', 92, '+1.828'], ['Pšenica', 70, '+1.389'], ['Ječam', 6, '−104']] as [$k, $w, $d])
                            <div class="grid grid-cols-[4.5rem_1fr_4rem] items-center gap-2">
                                <span class="text-gray-700">{{ $k }}</span>
                                <span class="h-2.5 rounded-full {{ str_starts_with($d, '−') ? 'bg-red-500' : 'bg-green-600' }}" style="width: {{ $w }}%"></span>
                                <span class="text-right font-semibold {{ str_starts_with($d, '−') ? 'text-red-700' : 'text-green-800' }}">{{ $d }} €</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- 5. Mehanizacija --}}
                <div class="rounded-3xl bg-[#f3f7f1] ring-1 ring-black/5 p-7 flex flex-col">
                    <div class="w-11 h-11 rounded-xl bg-[#022a20] flex items-center justify-center mb-4"><svg class="w-6 h-6 text-[#86bc25]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 17a2 2 0 104 0 2 2 0 00-4 0zm10 0a2 2 0 104 0 2 2 0 00-4 0M5 17H3v-4l2-5h7l2 5h3a2 2 0 012 2v2h-2M9 17h6M12 8V5"/></svg></div>
                    <h3 class="text-lg font-bold text-gray-900">Strojevi bez propuštenih rokova</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">Registracija, osiguranje, servis i pregled prskalice — podsjetnik mailom prije isteka, obnova jednim klikom.</p>
                    <div class="mt-auto pt-5 space-y-1.5 text-xs">
                        <p class="flex justify-between rounded-lg bg-white px-3 py-2 ring-1 ring-black/5"><span class="text-gray-800">Traktor · Registracija</span><span class="font-semibold text-amber-800">za 18 d.</span></p>
                        <p class="flex justify-between rounded-lg bg-white px-3 py-2 ring-1 ring-black/5"><span class="text-gray-800">Prskalica · Pregled</span><span class="font-semibold text-green-700">vrijedi</span></p>
                    </div>
                </div>

                {{-- 6. Izvještaji i pregled (preko cijele širine, da ne ostane sama u redu) --}}
                <div class="lg:col-span-3 rounded-3xl bg-[#f3f7f1] ring-1 ring-black/5 p-7 flex flex-col lg:grid lg:grid-cols-[auto_1fr_16rem] lg:items-center lg:gap-x-8">
                    <div class="w-11 h-11 rounded-xl bg-[#022a20] flex items-center justify-center mb-4 lg:mb-0 lg:row-span-2"><svg class="w-6 h-6 text-[#86bc25]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
                    <h3 class="text-lg font-bold text-gray-900 lg:col-start-2 lg:self-end">Izvještaji i zvono s upozorenjima</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed lg:col-start-2 lg:row-start-2 lg:self-start">Pregled gospodarstva s grafom, izvještaji za bilo koje razdoblje (PDF, Excel, CSV) i zvono koje javi karence, rokove i što treba dopuniti.</p>
                    <div class="mt-auto pt-5 lg:pt-0 lg:col-start-3 lg:row-start-1 lg:row-span-2 flex items-end gap-1.5 h-16" aria-hidden="true">
                        @foreach([30, 55, 40, 90, 65, 35, 50] as $i => $v)
                            <span class="flex-1 rounded-t {{ $i === 3 ? 'bg-green-600' : 'bg-green-300' }}" style="height: {{ $v }}%"></span>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-10 flex flex-wrap items-center gap-4">
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-[#86bc25] hover:bg-[#9ccf3e] text-[#022a20] font-bold px-6 py-3 rounded-xl transition">Isprobajte besplatno <span aria-hidden="true">→</span></a>
                <a href="{{ route('novosti') }}" class="font-semibold text-green-700 hover:underline">Sve novosti &rarr;</a>
            </div>
        </div>
    </section>

    {{-- FEATURES --}}
    <section class="relative overflow-hidden py-20 bg-[#f3f7f1]">
        {{-- pozadina: mozaik svijetlih parcela, više traktora polako obrađuje red po red (iza kartica; samo dok je vidljivo) --}}
        <div class="absolute inset-0 pointer-events-none no-print" aria-hidden="true" x-data="poljaTraktori()"></div>
        <div class="relative max-w-6xl mx-auto px-6">
            <div class="text-center mb-14" data-zaglavlje>
                <span class="text-xs font-bold uppercase tracking-[0.3em] text-green-700">Što sve dobivate</span>
                <h2 class="mt-3 text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight">Sve što vam treba za agro evidenciju</h2>
                <p class="mt-3 text-gray-600 max-w-2xl mx-auto">Dizajnirano prema stvarnim potrebama poljoprivrednika i zahtjevima AGRONET sustava — s ARKOD kartom, registrom sredstava i prognozom za vaše parcele.</p>
            </div>
            @php
                // [naslov, opis, ikona (heroicons outline path), nova mogućnost]
                $znacajke = [
                    ['ARKOD parcela na karti', 'Upišete ARKOD broj, a AgroLog parcelu pronađe u ARKOD-u, pokaže na satelitskoj karti i preuzme službenu površinu.', 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7', false],
                    ['Karta svih parcela', 'Sve vaše parcele na jednoj karti s ARKOD brojevima i kulturama — zumirajte, pomičite, kliknite parcelu.', 'M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z', false],
                    ['Prognoza i dani za prskanje', 'Prognoza za 5 dana za područje vaših parcela, s oznakom koji su dani povoljni za prskanje: vjetar, kiša, mraz, temperatura.', 'M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z', false],
                    ['Sredstva iz registra FIS-a', 'Oko 900 registriranih sredstava za zaštitu bilja — odaberete naziv, registracijski broj upiše se sam.', 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z', false],
                    ['Dušik u gnojidbi', 'Odaberete gnojivo s popisa, AgroLog izračuna kg N/ha i prati granicu od 170 kg N/ha iz stajskog gnoja.', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', false],
                    ['Spremni za 2027.', 'EPPO kod, fenofaza (BBCH) i vrsta uporabe — evidencija prskanja izvozi se izravno u Ministarstvovu Excel tablicu.', 'M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z', false],
                    ['Evidencija prskanja', 'Sredstvo, registracijski broj, doza, površina, vrijeme, voda i fenofaza — za svaku primjenu, prema obrascu.', 'M12 3c3 4 6 7.5 6 11a6 6 0 01-12 0c0-3.5 3-7 6-11z', false],
                    ['Evidencija gnojidbe', 'Datum, gnojivo s udjelima N-P-K i količina po ha. Pretraga i filtriranje po parcelama i kulturama.', 'M12 21c-4-3-7-7-7-11a7 7 0 0114 0c0 4-3 8-7 11zm0-11v6m0-6c-1.5-2-3.5-2.5-5-2m5 2c1.5-2 3.5-2.5 5-2', false],
                    ['Upozorenja za parcelu', 'Parcela u zoni zaštite izvorišta ili u području Natura 2000? AgroLog vas upozori već pri unosu.', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', false],
                    ['PDF za AGRONET', 'Službeni obrazac evidencije u jednom kliku — cijela tablica na A4 listu, spremna za ispis ili kontrolu.', 'M9 12h6m-6 4h4m4 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', false],
                    ['Izvoz u Excel i CSV', 'Evidencija u tablici za Excel — za savjetnika, zadrugu, kontrolu ili vlastitu arhivu.', 'M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3', false],
                    ['Povijest se čuva', 'Zamjena kulture na parceli bez gubitka starih zapisa — evidencija ostaje točna godinama.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', false],
                    ['Tržnica domaćih proizvoda', 'Kupujmo hrvatsko: med, voće, povrće, vino i ulje s cijenom. Uz svaki oglas piše tko prodaje i kako ga kontaktirati.', 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z', true],
                    ['Gospodarstva na karti', 'Besplatna prezentacija gospodarstva s opisom, slikom, kontaktom i lokacijom, da vas kupci pronađu po mjestu i ponudi.', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', true],
                    ['Ostali radovi i vožnja', 'Oranje, rezidba, košnja i vožnja do parcele s troškom, pa vidite pravu cijenu proizvodnje po hektaru.', 'M13 10V3L4 14h7v7l9-11h-7z', true],
                ];
            @endphp
            <div x-data="{ sve: false }">
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5" data-kartice>
                @foreach ($znacajke as $i => [$naslov, $opis, $ikona, $nova])
                    <div @if($i >= 6) x-show="sve" x-cloak x-transition.opacity.duration.400ms @endif class="group relative p-7 rounded-2xl bg-white ring-1 ring-black/5 shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300">
                        @if($nova)
                            <span class="absolute top-5 right-5 text-[10px] font-extrabold uppercase tracking-wider bg-[#86bc25] text-[#022a20] rounded-full px-2 py-0.5">Novo</span>
                        @endif
                        <div class="w-12 h-12 rounded-xl bg-[#022a20] flex items-center justify-center mb-5 shadow-[0_8px_24px_-8px_rgba(6,18,10,.6)] group-hover:bg-[#86bc25] transition">
                            <svg class="w-6 h-6 text-[#86bc25] group-hover:text-[#022a20] transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikona }}"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $naslov }}</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">{{ $opis }}</p>
                    </div>
                @endforeach
            </div>
            @if(count($znacajke) > 6)
                <div class="mt-8 text-center">
                    <button type="button" @click="sve = !sve; if (!sve) $el.closest('section').scrollIntoView({ behavior: 'smooth' })"
                            class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 ring-1 ring-black/10 shadow-sm text-[#022a20] font-semibold px-5 py-2.5 rounded-xl transition">
                        <span x-text="sve ? 'Prikaži manje' : 'Prikaži sve mogućnosti ({{ count($znacajke) }})'">Prikaži sve mogućnosti ({{ count($znacajke) }})</span>
                        <svg class="w-4 h-4 transition-transform" :class="sve && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                </div>
            @endif
            </div>
            <p class="mt-8 text-center text-sm text-gray-500">
                Povezano sa službenim izvorima: ARKOD (Agencija za plaćanja), registar sredstava FIS-a i format evidencije Ministarstva poljoprivrede.
                <a href="{{ route('novosti') }}" class="font-semibold text-green-700 hover:underline">Što je novo &rarr;</a>
            </p>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section class="py-20 bg-[#022a20] text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-[0.07] bg-[linear-gradient(rgba(134,188,37,1)_1px,transparent_1px),linear-gradient(90deg,rgba(134,188,37,1)_1px,transparent_1px)] bg-[size:48px_48px]" aria-hidden="true"></div>
        {{-- neonske točke na sjecištima mreže (kao parcele na karti); samo dok je sekcija vidljiva, bez animacije uz prefers-reduced-motion --}}
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true" x-data="neonMreza()"></div>
        <div class="relative max-w-5xl mx-auto px-6" data-sadrzaj>
            <div class="text-center mb-14">
                <span class="text-xs font-bold uppercase tracking-[0.3em] text-[#86bc25]">Tri koraka</span>
                <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight">Kako funkcionira?</h2>
            </div>
            <div class="relative grid md:grid-cols-3 gap-10">
                <div class="hidden md:block absolute top-7 left-[16%] right-[16%] h-px bg-gradient-to-r from-[#86bc25]/0 via-[#86bc25]/60 to-[#86bc25]/0" aria-hidden="true"></div>
                @foreach ([['Registrirajte se', 'Naziv gospodarstva, MIPG i OIB. Registracija traje manje od minute.'], ['Dodajte parcele', 'Upišite ARKOD broj — parcela se pokaže na karti sa službenom površinom. Dodajte kulture koje uzgajate.'], ['Vodite evidenciju', 'Bilježite gnojidbu i prskanje uz prognozu za vaše parcele, ispišite PDF ili izvezite u Excel.']] as $i => [$naslov, $opis])
                    <div class="relative text-center">
                        <div class="w-14 h-14 mx-auto mb-5 rounded-full bg-[#022a20] ring-2 ring-[#86bc25] text-[#86bc25] flex items-center justify-center text-xl font-extrabold shadow-[0_0_30px_-4px_rgba(134,188,37,.6)]">{{ $i + 1 }}</div>
                        <h3 class="font-bold text-lg mb-2">{{ $naslov }}</h3>
                        <p class="text-sm text-white/65 leading-relaxed max-w-xs mx-auto">{{ $opis }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- KUPUJMO HRVATSKO: Tržnica i Gospodarstva --}}
    <section id="trznica" class="scroll-mt-24 py-20 bg-[#f3f7f1]">
        <div class="max-w-6xl mx-auto px-6 grid gap-10 lg:grid-cols-2 lg:items-center">
            <div>
                <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.3em] text-green-700">
                    <span class="w-2 h-2 rounded-full bg-[#86bc25]"></span> Novo · Tržnica i Gospodarstva
                </span>
                <h2 class="mt-3 text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight">Kupujmo hrvatsko</h2>
                <p class="mt-2 text-xl font-semibold text-green-800">Podržimo domaće. Uživajmo lokalno. Vjerujmo u naše ljude.</p>
                <p class="mt-4 text-gray-600 text-lg leading-relaxed">Gospodarstva se besplatno predstave s opisom, slikom, kontaktom i lokacijom na karti, a proizvode stave na <strong>Tržnicu</strong> s cijenom. Kupac klikne oglas i vidi tko prodaje, kako ga nazvati i kako doći do njega. Bez posrednika i provizije.</p>
                <ul class="mt-5 space-y-2 text-gray-700">
                    <li class="flex gap-2"><span class="text-green-700" aria-hidden="true">✓</span> Prezentacija gospodarstva uključuje se jednom kvačicom u profilu</li>
                    <li class="flex gap-2"><span class="text-green-700" aria-hidden="true">✓</span> Oglasi s cijenom po kg, litri, komadu ili staklenci</li>
                    <li class="flex gap-2"><span class="text-green-700" aria-hidden="true">✓</span> Svi oglasi jednog prodavača na jednom mjestu</li>
                    <li class="flex gap-2"><span class="text-green-700" aria-hidden="true">✓</span> Pregledamo svako gospodarstvo prije objave</li>
                </ul>
                <div class="mt-7 flex flex-wrap items-center gap-4">
                    <a href="{{ route('trznica') }}" class="inline-flex items-center gap-2 bg-[#022a20] hover:bg-[#033b2c] text-white font-bold px-6 py-3 rounded-xl transition">Otvori Tržnicu <span aria-hidden="true">→</span></a>
                    <a href="{{ route('gospodarstva') }}" class="font-semibold text-green-700 hover:underline">Gospodarstva &rarr;</a>
                    <a href="{{ auth()->check() ? route('profile.edit') . '#prezentacija' : route('register') }}" class="font-semibold text-green-700 hover:underline">Predstavite svoje gospodarstvo &rarr;</a>
                </div>
            </div>
            {{-- primjer oglasa (ilustracija) --}}
            <div class="relative" aria-hidden="true">
                <div class="rounded-3xl bg-white p-5 shadow-xl ring-1 ring-black/5 max-w-md lg:ml-auto">
                    <div class="relative h-36 rounded-2xl bg-gradient-to-br from-[#f7fbf5] to-[#e8f3e3] flex items-end p-4">
                        <span class="absolute inset-0 flex items-center justify-center text-6xl">🍯</span>
                        <span class="relative rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-gray-800">Med</span>
                    </div>
                    <p class="mt-4 text-lg font-bold text-gray-900">Bagremov med</p>
                    <p class="text-2xl font-extrabold text-green-800">9,50 € / staklenka</p>
                    <div class="mt-4 rounded-xl border border-gray-200 p-3 text-sm">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Prodaje</p>
                        <p class="font-bold text-gray-900">OPG iz vašeg kraja</p>
                        <p class="text-gray-500">kućni prag · pon–sub 8–18 h</p>
                        <p class="mt-2 rounded-lg border border-green-600 px-3 py-1.5 text-center text-xs font-semibold text-green-800">Svi oglasi ovog prodavača</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Izdvajamo proizvode: najnoviji javni oglasi, 4 po stranici, sami se vrte (stoje dok je miš iznad ili ako korisnik ne želi animacije) --}}
        @php
            $izdvojeni = \App\Models\Proizvod::javni()->with('prezentacija')->latest('updated_at')->take(8)->get();
        @endphp
        @if($izdvojeni->count() >= 4)
            <div class="max-w-6xl mx-auto px-6 mt-16"
                 x-data="{
                     str: 0, ukupno: 1, t: null,
                     izmjeri() { const s = this.$refs.traka; this.ukupno = Math.max(1, Math.round(s.scrollWidth / s.clientWidth)); this.str = Math.min(this.ukupno - 1, Math.round(s.scrollLeft / s.clientWidth)) },
                     idi(i) { const s = this.$refs.traka; this.str = (i + this.ukupno) % this.ukupno; s.scrollTo({ left: this.str * s.clientWidth, behavior: 'smooth' }) },
                     kreni() { clearInterval(this.t); if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return; this.t = setInterval(() => { if (! document.hidden) this.idi(this.str + 1) }, 6000) },
                     stani() { clearInterval(this.t) },
                 }"
                 x-init="$nextTick(() => { izmjeri(); kreni() })" @resize.window.debounce.200ms="izmjeri()"
                 @mouseenter="stani()" @mouseleave="kreni()" @focusin="stani()">
                <div class="flex items-end justify-between gap-4 mb-5">
                    <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight">Izdvajamo proizvode</h3>
                    <div class="flex items-center gap-3">
                        <div class="hidden sm:flex items-center gap-1.5" x-show="ukupno > 1">
                            <template x-for="i in ukupno" :key="i">
                                <button type="button" @click="idi(i - 1); kreni()" class="h-2 rounded-full transition-all" :class="str === i - 1 ? 'w-6 bg-green-700' : 'w-2 bg-green-700/25'" :aria-label="'Stranica ' + i"></button>
                            </template>
                        </div>
                        <button type="button" @click="idi(str - 1); kreni()" x-show="ukupno > 1" class="h-9 w-9 rounded-full border border-gray-300 bg-white text-gray-700 hover:bg-gray-50" aria-label="Prethodni">‹</button>
                        <button type="button" @click="idi(str + 1); kreni()" x-show="ukupno > 1" class="h-9 w-9 rounded-full border border-gray-300 bg-white text-gray-700 hover:bg-gray-50" aria-label="Sljedeći">›</button>
                    </div>
                </div>
                <div x-ref="traka" @scroll.debounce.150ms="izmjeri()"
                     class="flex snap-x snap-mandatory overflow-x-auto -mx-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    @foreach($izdvojeni as $pr)
                        <div class="snap-start shrink-0 basis-[80%] sm:basis-1/2 lg:basis-1/4 px-2">
                            @include('trznica._kartica', ['pr' => $pr])
                        </div>
                    @endforeach
                </div>
                <div class="mt-5 text-center">
                    <a href="{{ route('trznica') }}" class="font-semibold text-green-700 hover:underline">Svi proizvodi na Tržnici &rarr;</a>
                </div>
            </div>
        @endif
    </section>

    {{-- STATS --}}
    @php
        $statsKorisnici = \App\Models\User::count();
        $zadnjiKorisnik = \App\Models\User::latest()->value('naziv_gospodarstva') ?: \App\Models\User::latest()->value('name');
        $statsPrskanja  = \App\Models\Prskanje::count();
        $statsGnojidbe  = \App\Models\Gnojidba::count();
    @endphp
    <section class="py-16 bg-white">
        <div class="max-w-5xl mx-auto px-6">
            <div class="text-center mb-10">
                <span class="text-xs font-bold uppercase tracking-[0.3em] text-green-700">Zajednica koja raste</span>
                <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900 mt-2 tracking-tight">Već koriste AgroLog</h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-6 rounded-2xl bg-[#022a20] text-white flex flex-col items-center justify-center">
                    <div class="text-4xl font-extrabold text-[#86bc25] mb-1">{{ $statsKorisnici }}+</div>
                    <div class="text-xs text-white/60 font-semibold uppercase tracking-wider">Korisnika</div>
                </div>
                <div class="text-center p-6 rounded-2xl bg-[#f3f7f1] ring-1 ring-green-900/10 flex flex-col items-center justify-center">
                    <div class="text-sm font-bold text-green-800 mb-1 leading-snug">{{ $zadnjiKorisnik }}</div>
                    <div class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Zadnji korisnik</div>
                </div>
                <div class="text-center p-6 rounded-2xl bg-[#f3f7f1] ring-1 ring-green-900/10 flex flex-col items-center justify-center">
                    <div class="text-4xl font-extrabold text-green-700 mb-1">{{ $statsPrskanja }}+</div>
                    <div class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Unosa prskanja</div>
                </div>
                <div class="text-center p-6 rounded-2xl bg-[#f3f7f1] ring-1 ring-green-900/10 flex flex-col items-center justify-center">
                    <div class="text-4xl font-extrabold text-green-700 mb-1">{{ $statsGnojidbe }}+</div>
                    <div class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Unosa gnojidbe</div>
                </div>
            </div>
        </div>
    </section>

    {{-- ALATI I VODIČI --}}
    <section class="pb-16 bg-white">
        <div class="max-w-5xl mx-auto px-6 grid md:grid-cols-2 gap-5">
            <a href="{{ route('alati') }}" class="group flex items-center gap-5 p-6 rounded-2xl ring-1 ring-black/5 bg-white hover:ring-[#86bc25] hover:shadow-lg transition">
                <span class="w-12 h-12 flex-none rounded-xl bg-[#86bc25]/15 text-green-700 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </span>
                <span class="flex-1"><b class="block text-gray-900">Besplatni alati</b><span class="text-sm text-gray-600">Kalkulator škropiva, gnojiva i karence, pretvornik površina.</span></span>
                <span class="text-green-700 text-xl group-hover:translate-x-1 transition" aria-hidden="true">→</span>
            </a>
            <a href="{{ route('vodici') }}" class="group flex items-center gap-5 p-6 rounded-2xl ring-1 ring-black/5 bg-white hover:ring-[#86bc25] hover:shadow-lg transition">
                <span class="w-12 h-12 flex-none rounded-xl bg-[#86bc25]/15 text-green-700 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </span>
                <span class="flex-1"><b class="block text-gray-900">Vodiči za evidenciju</b><span class="text-sm text-gray-600">Elektronička evidencija SZB, karenca, gnojidba dušikom i više.</span></span>
                <span class="text-green-700 text-xl group-hover:translate-x-1 transition" aria-hidden="true">→</span>
            </a>
        </div>
    </section>

    {{-- PARTNER PROMO --}}
    <section class="py-14 bg-[#f3f7f1]">
        <div class="max-w-5xl mx-auto px-6 grid md:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl ring-1 ring-black/5 shadow-sm p-8 flex flex-col items-center sm:items-start gap-5 text-center sm:text-left">
                <svg class="h-9 w-auto flex-shrink-0" viewBox="0 0 340 110" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="stemG" x1="0" y1="0" x2="0.4" y2="1">
                            <stop offset="0%" stop-color="#2ba99b"/>
                            <stop offset="100%" stop-color="#3dcfb8"/>
                        </linearGradient>
                        <linearGradient id="bowlG" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="#42d4a8"/>
                            <stop offset="100%" stop-color="#96f070"/>
                        </linearGradient>
                    </defs>
                    <rect x="7" y="19" width="48" height="88" rx="14" fill="#196b5e" opacity="0.35"/>
                    <rect x="3" y="15" width="48" height="88" rx="14" fill="url(#stemG)"/>
                    <rect x="19" y="7" width="72" height="58" rx="14" fill="#196b5e" opacity="0.25"/>
                    <rect x="15" y="3" width="72" height="58" rx="14" fill="url(#bowlG)"/>
                    <text x="108" y="68" font-family="'Nunito','Poppins','Segoe UI',system-ui,sans-serif" font-weight="800" font-size="52" fill="#162433" letter-spacing="-1">Placko</text>
                    <text x="223" y="90" font-family="'Nunito','Poppins','Segoe UI',system-ui,sans-serif" font-weight="500" font-size="22" fill="#2ba99b" letter-spacing="0.5">.app</text>
                </svg>
                <div class="flex-1">
                    <p class="text-gray-900 font-semibold mb-1">Vodite OPG ili obrt? Fakturirajte jednostavno.</p>
                    <p class="text-gray-500 text-sm leading-relaxed">Plačko.app je aplikacija za izdavanje računa, fiskalizaciju i eRačun za paušalne obrtnike i male tvrtke u Hrvatskoj.</p>
                </div>
                <a href="https://plačko.app" target="_blank" rel="noopener" class="flex-shrink-0 bg-gray-900 hover:bg-gray-800 text-white font-semibold px-5 py-2.5 rounded-xl transition text-sm whitespace-nowrap">
                    Isprobaj Plačko →
                </a>
            </div>
            <div class="bg-white rounded-2xl ring-1 ring-black/5 shadow-sm p-8 flex flex-col items-center sm:items-start gap-5 text-center sm:text-left">
                <img src="{{ asset('images/partneri/on-click.png') }}" alt="ON-CLICK IT Solutions" class="h-9 w-auto flex-shrink-0" width="126" height="36" loading="lazy">
                <div class="flex-1">
                    <p class="text-gray-900 font-semibold mb-1">Trebate web stranicu ili aplikaciju?</p>
                    <p class="text-gray-500 text-sm leading-relaxed">ON-CLICK izrađuje internet stranice, web trgovine i mobilne aplikacije &mdash; tim koji stoji iza AgroLoga i Plačka.</p>
                </div>
                <a href="https://on-click.hr" target="_blank" rel="noopener" class="flex-shrink-0 bg-gray-900 hover:bg-gray-800 text-white font-semibold px-5 py-2.5 rounded-xl transition text-sm whitespace-nowrap">
                    Pogledaj ON-CLICK →
                </a>
            </div>
        </div>
    </section>

    {{-- CTA: polja u zalasku sunca (-mb-8 poništava razmak iznad zajedničkog podnožja) --}}
    <section class="relative overflow-hidden bg-[#022a20] -mb-8">
        <img src="{{ asset('images/naslovna/polja-zalazak.jpg') }}" alt="" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
        <div class="absolute inset-0 bg-gradient-to-b from-[#022a20]/70 via-[#022a20]/75 to-[#022a20]/95"></div>
        <div class="relative max-w-2xl mx-auto px-6 py-24 text-center text-white">
            <h2 class="text-3xl md:text-5xl font-extrabold tracking-tight mb-4">Počnite danas — <span class="text-[#86bc25]">besplatno</span></h2>
            <p class="text-white/85 mb-9 text-lg">Digitalizirajte svoje agro zapise i uvijek imajte pregled nad parcelama.</p>
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-[#86bc25] hover:bg-[#9ccf3e] text-[#022a20] font-extrabold px-9 py-4 rounded-xl transition text-lg shadow-[0_10px_40px_-10px_rgba(134,188,37,.8)]">
                Kreirajte račun <span aria-hidden="true">→</span>
            </a>
        </div>
    </section>

    {{-- FOOTER --}}
    @include('partials.footer')

    <script>
        // Neonske točke na sjecištima mreže od 48 px: točka s valom, pribadača ili križić; ponekad svjetleća crta po mreži.
        function neonMreza() {
            const KORAK = 48, PIN = '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M12 2a7 7 0 00-7 7c0 5.2 7 13 7 13s7-7.8 7-13a7 7 0 00-7-7zm0 9.5A2.5 2.5 0 1112 6.5a2.5 2.5 0 010 5z"/></svg>';
            return {
                timer: null, zivih: 0,
                init() {
                    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                    new IntersectionObserver(([u]) => u.isIntersecting ? this.kreni() : this.stani(), { threshold: 0.15 }).observe(this.$el);
                    document.addEventListener('visibilitychange', () => document.hidden ? this.stani() : null);
                },
                kreni() { if (! this.timer) this.timer = setInterval(() => this.nova(), 700) },
                stani() { clearInterval(this.timer); this.timer = null },
                mjesto() {
                    const w = this.$el.clientWidth, h = this.$el.clientHeight;
                    const s = this.$el.parentElement.querySelector('[data-sadrzaj]');
                    const r = s ? s.getBoundingClientRect() : null, o = this.$el.getBoundingClientRect();
                    for (let i = 0; i < 6; i++) {
                        const x = KORAK * (1 + Math.floor(Math.random() * Math.max(1, Math.floor(w / KORAK) - 1)));
                        const y = KORAK * (1 + Math.floor(Math.random() * Math.max(1, Math.floor(h / KORAK) - 1)));
                        // uglavnom izvan teksta s koracima
                        const preko = r && x > r.left - o.left - 24 && x < r.right - o.left + 24 && y > r.top - o.top && y < r.bottom - o.top;
                        if (! preko || Math.random() < 0.12) return [x, y];
                    }
                    return null;
                },
                nova() {
                    if (this.zivih >= 9) return;
                    const m = this.mjesto(); if (! m) return;
                    const [x, y] = m, n = Math.random();
                    const el = document.createElement('span');
                    el.className = n < 0.6 ? 'neon-tocka' : (n < 0.82 ? 'neon-pin' : 'neon-kriz');
                    if (el.className === 'neon-pin') el.innerHTML = PIN;
                    el.style.left = x + 'px'; el.style.top = y + 'px';
                    this.dodaj(el, 4200);
                    if (Math.random() < 0.35) this.crta(x, y);
                },
                crta(x, y) {
                    const vod = Math.random() < 0.5, d = KORAK * (2 + Math.floor(Math.random() * 3)), smjer = Math.random() < 0.5 ? -1 : 1;
                    const el = document.createElement('span');
                    el.className = 'neon-crta ' + (vod ? 'neon-crta-v' : 'neon-crta-o');
                    if (vod) { el.style.left = (smjer > 0 ? x : x - d) + 'px'; el.style.top = y + 'px'; el.style.width = d + 'px' }
                    else { el.style.left = x + 'px'; el.style.top = (smjer > 0 ? y : y - d) + 'px'; el.style.height = d + 'px' }
                    el.style.transformOrigin = vod ? (smjer > 0 ? 'left' : 'right') : (smjer > 0 ? 'top' : 'bottom');
                    this.dodaj(el, 3000);
                },
                dodaj(el, ms) { this.zivih++; this.$el.appendChild(el); setTimeout(() => { el.remove(); this.zivih-- }, ms) },
            };
        }
    </script>
    @include('partials.tawk')
</body>
</html>
