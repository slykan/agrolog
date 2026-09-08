<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AgroLog – Digitalni dnevnik gnojidbe i prskanja</title>
    <meta name="description" content="AgroLog je jednostavna web aplikacija za vođenje evidencije gnojidbe i prskanja po ARKOD parcelama. Digitalizirajte svoje agro zapise i ispišite ih u standardnom obliku.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ route('home') }}">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('home') }}">
    <meta property="og:title" content="AgroLog – Digitalni dnevnik gnojidbe i prskanja">
    <meta property="og:description" content="Vodite evidenciju gnojidbe i prskanja po ARKOD parcelama. Digitalizirajte svoje agro zapise i ispišite ih u standardnom obliku.">
    <meta property="og:locale" content="hr_HR">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-800 bg-white">

    {{-- NAV --}}
    @include('partials.nav')

    {{-- HERO --}}
    <section class="py-8 bg-white">
        <div class="max-w-6xl mx-auto px-6">
            <div
                x-data="{ slide: 0 }"
                x-init="setInterval(() => { slide = (slide + 1) % 2 }, 5500)"
                class="relative overflow-hidden rounded-2xl"
            >
                {{-- Background images --}}
                <div class="absolute inset-0 transition-opacity duration-1000" :class="slide === 0 ? 'opacity-100' : 'opacity-0'">
                    <img src="{{ asset('images/hero1.jpg') }}" class="w-full h-full object-cover" alt="">
                </div>
                <div class="absolute inset-0 transition-opacity duration-1000" :class="slide === 1 ? 'opacity-100' : 'opacity-0'">
                    <img src="{{ asset('images/hero2.jpg') }}" class="w-full h-full object-cover" alt="">
                </div>
                <div class="absolute inset-0 bg-white/50"></div>

                {{-- Content --}}
                <div class="relative z-10 w-full md:w-1/2 px-12 py-20">
                    <div class="relative min-h-[320px]">

                        {{-- Slide 1 --}}
                        <div
                            class="absolute inset-0 transition-opacity duration-700"
                            :class="slide === 0 ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'"
                        >
                            <span class="inline-block text-xs font-semibold uppercase tracking-widest text-green-600 bg-green-100 px-3 py-1 rounded-full mb-4">Digitalna evidencija</span>
                            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 leading-tight mb-5">
                                Agro zapisi<br>
                                <span class="text-green-600">bez papira.</span>
                            </h1>
                            <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                                Vodite evidenciju gnojidbe i prskanja po ARKOD parcelama, filtrirajte zapise i ispišite ih u standardnom obliku — sve na jednom mjestu.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('register') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-xl transition shadow-sm">Isprobaj besplatno</a>
                                <a href="{{ route('login') }}" class="bg-white hover:bg-gray-50 text-gray-700 font-semibold px-6 py-3 rounded-xl border border-gray-200 transition">Već imam račun</a>
                            </div>
                        </div>

                        {{-- Slide 2 --}}
                        <div
                            class="absolute inset-0 transition-opacity duration-700"
                            :class="slide === 1 ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'"
                        >
                            <span class="inline-block text-xs font-semibold uppercase tracking-widest text-green-600 bg-green-100 px-3 py-1 rounded-full mb-4">Ispis u sekundi</span>
                            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 leading-tight mb-5">
                                Službeni obrazac<br>
                                <span class="text-green-600">jednim klikom.</span>
                            </h1>
                            <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                                Generirajte PDF evidenciju gnojidbe i prskanja u skladu s AGRONET zahtjevima — uvijek ažurno, bez gubitka podataka.
                            </p>
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('register') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-xl transition shadow-sm">Isprobaj besplatno</a>
                                <a href="{{ route('login') }}" class="bg-white hover:bg-gray-50 text-gray-700 font-semibold px-6 py-3 rounded-xl border border-gray-200 transition">Već imam račun</a>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Dots --}}
                <div class="absolute bottom-5 left-12 flex gap-2 z-10">
                    <button @click="slide = 0" class="w-2.5 h-2.5 rounded-full transition-colors duration-300" :class="slide === 0 ? 'bg-green-600' : 'bg-gray-400/60'"></button>
                    <button @click="slide = 1" class="w-2.5 h-2.5 rounded-full transition-colors duration-300" :class="slide === 1 ? 'bg-green-600' : 'bg-gray-400/60'"></button>
                </div>
            </div>
        </div>
    </section>

    {{-- BESPLATNO STRIP --}}
    <section class="py-8 bg-white">
        <div class="max-w-6xl mx-auto px-6">
            <div class="bg-green-600 rounded-2xl px-10 py-8 flex flex-col md:flex-row items-center gap-6 text-center md:text-left">
                <div class="flex-shrink-0">
                    <span class="bg-white text-green-700 text-sm font-bold uppercase tracking-widest px-5 py-2.5 rounded-xl whitespace-nowrap block">Potpuno besplatno</span>
                </div>
                <div class="w-px h-10 bg-green-500 hidden md:block flex-shrink-0"></div>
                <div>
                    <p class="text-white font-semibold text-lg mb-1">Zašto besplatno?</p>
                    <p class="text-green-100 leading-relaxed text-sm">
                        Vjerujemo da digitalni alati trebaju biti dostupni svim poljoprivrednicima — bez pretplate, bez skrivenih troškova. Naša misija je olakšati svakodnevni posao na gospodarstvu.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- FEATURES --}}
    <section class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center mb-14">
                <h2 class="text-3xl font-bold text-gray-900 mb-3">Sve što vam treba za agro evidenciju</h2>
                <p class="text-gray-500 max-w-xl mx-auto">Dizajnirano prema stvarnim potrebama poljoprivrednika i zahtjevima AGRONET sustava.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="group p-7 rounded-2xl border border-gray-100 hover:border-green-200 hover:shadow-lg transition bg-white">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-5 group-hover:bg-green-600 transition">
                        <svg class="w-6 h-6 text-green-600 group-hover:text-white transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">ARKOD Parcele</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Upravljajte parcelama i kulturama. Pratite posađene površine po ARKOD broju i godini uzgoja.</p>
                </div>
                <div class="group p-7 rounded-2xl border border-gray-100 hover:border-green-200 hover:shadow-lg transition bg-white">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-5 group-hover:bg-green-600 transition">
                        <svg class="w-6 h-6 text-green-600 group-hover:text-white transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Evidencija gnojidbe</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Bilježite datum, vrstu gnojiva i količinu po ha. Filtrirajte i pretražujte zapise po parcelama i kulturama.</p>
                </div>
                <div class="group p-7 rounded-2xl border border-gray-100 hover:border-green-200 hover:shadow-lg transition bg-white">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-5 group-hover:bg-green-600 transition">
                        <svg class="w-6 h-6 text-green-600 group-hover:text-white transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Evidencija prskanja</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Svi podaci o tretiranju: sredstvo, doza, površina, vrijeme tretiranja i količina vode — prema obrascu.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section class="py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-6">
            <div class="text-center mb-14">
                <h2 class="text-3xl font-bold text-gray-900 mb-3">Kako funkcionira?</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-10">
                <div class="text-center">
                    <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg mx-auto mb-4">1</div>
                    <h3 class="font-semibold text-gray-900 mb-2">Registrirajte se</h3>
                    <p class="text-sm text-gray-500">Unesite naziv gospodarstva, MIPG i OIB. Registracija traje manje od minute.</p>
                </div>
                <div class="text-center">
                    <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg mx-auto mb-4">2</div>
                    <h3 class="font-semibold text-gray-900 mb-2">Dodajte parcele</h3>
                    <p class="text-sm text-gray-500">Unesite ARKOD brojeve parcela i kulture koje uzgajate s posađenom površinom.</p>
                </div>
                <div class="text-center">
                    <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg mx-auto mb-4">3</div>
                    <h3 class="font-semibold text-gray-900 mb-2">Vodite evidenciju</h3>
                    <p class="text-sm text-gray-500">Bilježite gnojidbu i prskanje u tablici. Filtrirajte i ispišite PDF u jednom kliku.</p>
                </div>
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
    <section class="py-16 bg-white border-t border-gray-100">
        <div class="max-w-4xl mx-auto px-6">
            <div class="text-center mb-10">
                <span class="text-xs font-semibold uppercase tracking-widest text-green-600">Zajednica koja raste</span>
                <h2 class="text-2xl font-bold text-gray-900 mt-2">Već koriste AgroLog</h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="text-center p-6 rounded-2xl bg-green-50 border border-green-100 flex flex-col items-center justify-center">
                    <div class="text-3xl font-bold text-green-700 mb-1">{{ $statsKorisnici }}+</div>
                    <div class="text-xs text-gray-500 font-medium uppercase tracking-wide">Korisnika</div>
                </div>
                <div class="text-center p-6 rounded-2xl bg-green-50 border border-green-100 flex flex-col items-center justify-center">
                    <div class="text-xs font-bold text-green-700 mb-1 leading-snug">{{ $zadnjiKorisnik }}</div>
                    <div class="text-xs text-gray-500 font-medium uppercase tracking-wide">Zadnji korisnik</div>
                </div>
                <div class="text-center p-6 rounded-2xl bg-green-50 border border-green-100 flex flex-col items-center justify-center">
                    <div class="text-3xl font-bold text-green-700 mb-1">{{ $statsPrskanja }}+</div>
                    <div class="text-xs text-gray-500 font-medium uppercase tracking-wide">Unosa prskanja</div>
                </div>
                <div class="text-center p-6 rounded-2xl bg-green-50 border border-green-100 flex flex-col items-center justify-center">
                    <div class="text-3xl font-bold text-green-700 mb-1">{{ $statsGnojidbe }}+</div>
                    <div class="text-xs text-gray-500 font-medium uppercase tracking-wide">Unosa gnojidbe</div>
                </div>
            </div>
        </div>
    </section>

    {{-- PARTNER PROMO --}}
    <section class="py-14 bg-gray-50 border-t border-gray-100">
        <div class="max-w-4xl mx-auto px-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 flex flex-col md:flex-row items-center gap-6 text-center md:text-left">
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

    {{-- CTA --}}
    <section class="py-20 bg-green-600">
        <div class="max-w-2xl mx-auto px-6 text-center">
            <h2 class="text-3xl font-bold text-white mb-4">Počnite danas — besplatno</h2>
            <p class="text-green-100 mb-8 text-lg">Digitalizirajte svoje agro zapise i uvijek imajte pregled nad parcelama.</p>
            <a href="{{ route('register') }}" class="inline-block bg-white hover:bg-gray-50 text-green-700 font-bold px-8 py-4 rounded-xl transition shadow-md text-lg">
                Kreirajte račun
            </a>
        </div>
    </section>

    {{-- FOOTER --}}
    @include('partials.footer')

</body>
</html>
