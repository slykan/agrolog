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
    <meta name="theme-color" content="#06120a">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
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
    @include('partials.nav')

    {{-- HERO: tamno, fotografija polja s tabletom desno, jarko zeleni naglasci --}}
    <section class="relative overflow-hidden bg-[#06120a] text-white">
        <div class="absolute inset-0 lg:left-[40%]">
            <img src="{{ asset('images/naslovna/hero-polje.jpg') }}" alt="Poljoprivrednik na polju drži tablet s AgroLog evidencijom gnojidbe" class="w-full h-full object-cover object-[30%_center]" fetchpriority="high">
        </div>
        {{-- zatamnjenje: na mobitelu preko cijele slike, na računalu prijelaz s lijeva --}}
        <div class="absolute inset-0 bg-[#06120a]/80 lg:bg-transparent lg:bg-gradient-to-r lg:from-[#06120a] lg:from-[40%] lg:via-[#06120a]/70 lg:via-[52%] lg:to-transparent lg:to-[75%]"></div>
        <div class="absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-[#06120a] to-transparent"></div>

        <div class="relative max-w-6xl mx-auto px-6 pt-16 pb-14 lg:pt-24 lg:pb-20">
            <div class="max-w-xl">
                <p class="text-[11px] sm:text-xs font-semibold uppercase tracking-[0.35em] text-white/80 mb-6">Digitalni alati za poljoprivrednike</p>
                <h1 class="text-[2.6rem] leading-[1.05] sm:text-6xl lg:text-[4.2rem] font-extrabold tracking-tight">
                    Službeni obrazac<br>
                    <span class="relative inline-block text-[#3ee36f]">
                        jednim klikom.
                        <svg class="absolute left-0 -bottom-3 w-[70%] h-4 text-[#3ee36f]" viewBox="0 0 300 16" fill="none" preserveAspectRatio="none" aria-hidden="true"><path d="M3 12C70 4 170 2 297 6" stroke="currentColor" stroke-width="5" stroke-linecap="round"/></svg>
                    </span>
                </h1>
                <p class="mt-8 text-lg sm:text-xl text-white/85 leading-relaxed">
                    Vodite evidenciju gnojidbe i prskanja po ARKOD parcelama i ispišite PDF u skladu s AGRONET zahtjevima — brzo, jednostavno i bez gubitka podataka.
                </p>
                <div class="mt-9 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-[#3ee36f] hover:bg-[#5cf088] text-[#06120a] font-bold px-7 py-4 rounded-xl transition shadow-[0_10px_40px_-10px_rgba(62,227,111,.7)]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Isprobaj besplatno
                        <span aria-hidden="true">→</span>
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 border-2 border-[#3ee36f]/70 hover:border-[#3ee36f] hover:bg-[#3ee36f]/10 text-white font-semibold px-7 py-[14px] rounded-xl transition">Moj račun <span aria-hidden="true">→</span></a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 border-2 border-[#3ee36f]/70 hover:border-[#3ee36f] hover:bg-[#3ee36f]/10 text-white font-semibold px-7 py-[14px] rounded-xl transition">Već imam račun <span aria-hidden="true">→</span></a>
                    @endauth
                </div>

                <ul class="mt-12 grid grid-cols-2 sm:grid-cols-4 gap-y-5 sm:divide-x sm:divide-white/15 text-sm">
                    <li class="flex items-center gap-2.5 sm:pr-4">
                        <svg class="w-8 h-8 flex-none text-[#3ee36f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M5 21c0-9 5-15 15-17-1 10-6 15-15 17zm0 0l7-7"/></svg>
                        <span class="leading-tight text-white/90">Potpuno<br>besplatno</span>
                    </li>
                    <li class="flex items-center gap-2.5 sm:px-4">
                        <svg class="w-8 h-8 flex-none text-[#3ee36f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h4m4 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span class="leading-tight text-white/90">PDF za<br>AGRONET</span>
                    </li>
                    <li class="flex items-center gap-2.5 sm:px-4">
                        <svg class="w-8 h-8 flex-none text-[#3ee36f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span class="leading-tight text-white/90 whitespace-nowrap">SZB prema<br>EU 2023/564</span>
                    </li>
                    <li class="flex items-center gap-2.5 sm:pl-4">
                        <svg class="w-8 h-8 flex-none text-[#3ee36f]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-5.5-7-11a7 7 0 0114 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                        <span class="leading-tight text-white/90">100% Made<br>in Croatia</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    {{-- ZAŠTO BESPLATNO --}}
    <section class="bg-[#06120a] pb-16">
        <div class="max-w-6xl mx-auto px-6">
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#14532d] to-[#166534] px-8 py-8 md:px-10 flex flex-col md:flex-row items-center gap-6 text-center md:text-left ring-1 ring-[#3ee36f]/30">
                <div class="absolute -right-10 -top-10 w-48 h-48 rounded-full bg-[#3ee36f]/20 blur-3xl" aria-hidden="true"></div>
                <span class="relative flex-shrink-0 bg-[#3ee36f] text-[#06120a] text-sm font-extrabold uppercase tracking-widest px-5 py-2.5 rounded-xl whitespace-nowrap">0 € — zauvijek</span>
                <div class="relative">
                    <p class="text-white font-bold text-lg mb-1">Zašto besplatno?</p>
                    <p class="text-green-100/90 leading-relaxed text-sm md:text-base">
                        Vjerujemo da digitalni alati trebaju biti dostupni svim poljoprivrednicima — bez pretplate, bez kreditne kartice i bez skrivenih troškova. Naša misija je olakšati svakodnevni posao na gospodarstvu.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- FEATURES --}}
    <section class="py-20 bg-[#f3f7f1]">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center mb-14">
                <span class="text-xs font-bold uppercase tracking-[0.3em] text-green-700">Što sve dobivate</span>
                <h2 class="mt-3 text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight">Sve što vam treba za agro evidenciju</h2>
                <p class="mt-3 text-gray-600 max-w-xl mx-auto">Dizajnirano prema stvarnim potrebama poljoprivrednika i zahtjevima AGRONET sustava.</p>
            </div>
            @php
                $znacajke = [
                    ['ARKOD parcele', 'Parcele i kulture po ARKOD broju i godini uzgoja, s posađenom površinom.', 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7'],
                    ['Evidencija gnojidbe', 'Datum, vrsta gnojiva i količina po ha. Pretraga i filtriranje po parcelama i kulturama.', 'M12 21c-4-3-7-7-7-11a7 7 0 0114 0c0 4-3 8-7 11zm0-11v6m0-6c-1.5-2-3.5-2.5-5-2m5 2c1.5-2 3.5-2.5 5-2'],
                    ['Evidencija prskanja', 'Sredstvo, registracijski broj, doza, površina, vrijeme i količina vode — prema obrascu.', 'M12 3c3 4 6 7.5 6 11a6 6 0 01-12 0c0-3.5 3-7 6-11z'],
                    ['PDF za AGRONET', 'Službeni obrazac evidencije u jednom kliku — spreman za ispis ili kontrolu.', 'M9 12h6m-6 4h4m4 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ['Izvoz u CSV', 'Evidencija prskanja u tablici za Excel — za savjetnika, zadrugu ili vlastitu arhivu.', 'M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3'],
                    ['Povijest se čuva', 'Zamjena kulture na parceli bez gubitka starih zapisa — evidencija ostaje točna godinama.', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ];
            @endphp
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($znacajke as [$naslov, $opis, $ikona])
                    <div class="group p-7 rounded-2xl bg-white ring-1 ring-black/5 shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300">
                        <div class="w-12 h-12 rounded-xl bg-[#06120a] flex items-center justify-center mb-5 shadow-[0_8px_24px_-8px_rgba(6,18,10,.6)] group-hover:bg-[#3ee36f] transition">
                            <svg class="w-6 h-6 text-[#3ee36f] group-hover:text-[#06120a] transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikona }}"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $naslov }}</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">{{ $opis }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section class="py-20 bg-[#06120a] text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-[0.07] bg-[linear-gradient(rgba(62,227,111,1)_1px,transparent_1px),linear-gradient(90deg,rgba(62,227,111,1)_1px,transparent_1px)] bg-[size:48px_48px]" aria-hidden="true"></div>
        <div class="relative max-w-5xl mx-auto px-6">
            <div class="text-center mb-14">
                <span class="text-xs font-bold uppercase tracking-[0.3em] text-[#3ee36f]">Tri koraka</span>
                <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight">Kako funkcionira?</h2>
            </div>
            <div class="relative grid md:grid-cols-3 gap-10">
                <div class="hidden md:block absolute top-7 left-[16%] right-[16%] h-px bg-gradient-to-r from-[#3ee36f]/0 via-[#3ee36f]/60 to-[#3ee36f]/0" aria-hidden="true"></div>
                @foreach ([['Registrirajte se', 'Naziv gospodarstva, MIPG i OIB. Registracija traje manje od minute.'], ['Dodajte parcele', 'ARKOD brojevi parcela i kulture koje uzgajate, s posađenom površinom.'], ['Vodite evidenciju', 'Bilježite gnojidbu i prskanje, filtrirajte i ispišite PDF u jednom kliku.']] as $i => [$naslov, $opis])
                    <div class="relative text-center">
                        <div class="w-14 h-14 mx-auto mb-5 rounded-full bg-[#06120a] ring-2 ring-[#3ee36f] text-[#3ee36f] flex items-center justify-center text-xl font-extrabold shadow-[0_0_30px_-4px_rgba(62,227,111,.6)]">{{ $i + 1 }}</div>
                        <h3 class="font-bold text-lg mb-2">{{ $naslov }}</h3>
                        <p class="text-sm text-white/65 leading-relaxed max-w-xs mx-auto">{{ $opis }}</p>
                    </div>
                @endforeach
            </div>
        </div>
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
                <div class="text-center p-6 rounded-2xl bg-[#06120a] text-white flex flex-col items-center justify-center">
                    <div class="text-4xl font-extrabold text-[#3ee36f] mb-1">{{ $statsKorisnici }}+</div>
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
            <a href="{{ route('alati') }}" class="group flex items-center gap-5 p-6 rounded-2xl ring-1 ring-black/5 bg-white hover:ring-[#3ee36f] hover:shadow-lg transition">
                <span class="w-12 h-12 flex-none rounded-xl bg-[#3ee36f]/15 text-green-700 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </span>
                <span class="flex-1"><b class="block text-gray-900">Besplatni alati</b><span class="text-sm text-gray-600">Kalkulator škropiva, gnojiva i karence, pretvornik površina.</span></span>
                <span class="text-green-700 text-xl group-hover:translate-x-1 transition" aria-hidden="true">→</span>
            </a>
            <a href="{{ route('vodici') }}" class="group flex items-center gap-5 p-6 rounded-2xl ring-1 ring-black/5 bg-white hover:ring-[#3ee36f] hover:shadow-lg transition">
                <span class="w-12 h-12 flex-none rounded-xl bg-[#3ee36f]/15 text-green-700 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </span>
                <span class="flex-1"><b class="block text-gray-900">Vodiči za evidenciju</b><span class="text-sm text-gray-600">Elektronička evidencija SZB, karenca, gnojidba dušikom i više.</span></span>
                <span class="text-green-700 text-xl group-hover:translate-x-1 transition" aria-hidden="true">→</span>
            </a>
        </div>
    </section>

    {{-- PARTNER PROMO --}}
    <section class="py-14 bg-[#f3f7f1]">
        <div class="max-w-4xl mx-auto px-6">
            <div class="bg-white rounded-2xl ring-1 ring-black/5 shadow-sm p-8 flex flex-col md:flex-row items-center gap-6 text-center md:text-left">
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
        </div>
    </section>

    {{-- CTA: polja u zalasku sunca (-mb-8 poništava razmak iznad zajedničkog podnožja) --}}
    <section class="relative overflow-hidden bg-[#06120a] -mb-8">
        <img src="{{ asset('images/naslovna/polja-zalazak.jpg') }}" alt="" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
        <div class="absolute inset-0 bg-gradient-to-b from-[#06120a]/70 via-[#06120a]/75 to-[#06120a]/95"></div>
        <div class="relative max-w-2xl mx-auto px-6 py-24 text-center text-white">
            <h2 class="text-3xl md:text-5xl font-extrabold tracking-tight mb-4">Počnite danas — <span class="text-[#3ee36f]">besplatno</span></h2>
            <p class="text-white/85 mb-9 text-lg">Digitalizirajte svoje agro zapise i uvijek imajte pregled nad parcelama.</p>
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-[#3ee36f] hover:bg-[#5cf088] text-[#06120a] font-extrabold px-9 py-4 rounded-xl transition text-lg shadow-[0_10px_40px_-10px_rgba(62,227,111,.8)]">
                Kreirajte račun <span aria-hidden="true">→</span>
            </a>
        </div>
    </section>

    {{-- FOOTER --}}
    @include('partials.footer')

</body>
</html>
