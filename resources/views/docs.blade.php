<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upute za korištenje – AgroLog</title>
    <meta name="description" content="Upute za korištenje AgroLog aplikacije. Naučite kako unijeti parcele, voditi evidenciju gnojidbe i prskanja te koristiti napredne funkcije.">
    <link rel="canonical" href="{{ route('docs') }}">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('docs') }}">
    <meta property="og:title" content="Upute za korištenje – AgroLog">
    <meta property="og:description" content="Upute za korištenje AgroLog aplikacije. Naučite kako unijeti parcele, voditi evidenciju gnojidbe i prskanja te koristiti napredne funkcije.">
    <meta property="og:locale" content="hr_HR">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-800 bg-white">

    {{-- NAV --}}
    @include('partials.nav', ['active' => 'docs'])

    {{-- HERO --}}
    <section class="bg-gradient-to-br from-green-50 via-white to-emerald-50 py-16">
        <div class="max-w-3xl mx-auto px-6 text-center">
            <span class="inline-block text-xs font-semibold uppercase tracking-widest text-green-600 bg-green-100 px-3 py-1 rounded-full mb-4">Upute za korištenje</span>
            <h1 class="text-4xl font-bold text-gray-900 leading-tight mb-4">Kako koristiti AgroLog</h1>
            <p class="text-lg text-gray-500 leading-relaxed">Sve što trebate znati za vođenje digitalne evidencije — korak po korak.</p>
        </div>
    </section>

    {{-- SADRŽAJ --}}
    <div class="max-w-5xl mx-auto px-6 py-16">
        <div class="flex flex-col lg:flex-row gap-12">

            {{-- SIDEBAR navigacija --}}
            <aside class="lg:w-56 flex-shrink-0">
                <div class="sticky top-24 space-y-1 text-sm">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Na ovoj stranici</p>
                    <a href="#pocetak" class="block text-gray-600 hover:text-green-700 py-1 transition">1. Početak</a>
                    <a href="#parcele" class="block text-gray-600 hover:text-green-700 py-1 transition">2. Parcele i kulture</a>
                    <a href="#gnojidba" class="block text-gray-600 hover:text-green-700 py-1 transition">3. Evidencija gnojidbe</a>
                    <a href="#prskanje" class="block text-gray-600 hover:text-green-700 py-1 transition">4. Evidencija prskanja</a>
                    <a href="#viseredni-unos" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Višeredni unos</a>
                    <a href="#kopiranje" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Kopiranje zapisa</a>
                    <a href="#ispis" class="block text-gray-600 hover:text-green-700 py-1 transition">5. Ispis PDF-a</a>
                    <a href="#profil" class="block text-gray-600 hover:text-green-700 py-1 transition">6. Profil</a>
                </div>
            </aside>

            {{-- SADRŽAJ --}}
            <main class="flex-1 space-y-16 min-w-0">

                {{-- 1. POČETAK --}}
                <section id="pocetak">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">1</div>
                        <h2 class="text-2xl font-bold text-gray-900">Početak — registracija i prijava</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Registracija traje manje od minute. Na stranici <a href="{{ route('register') }}" class="text-green-700 hover:underline font-medium">Registracija</a> unesite:</p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li><strong class="text-gray-800">Naziv gospodarstva</strong> — prikazuje se u zaglavlju obrazaca za ispis</li>
                            <li><strong class="text-gray-800">MIPG broj</strong> — matični identifikacijski broj poljoprivrednog gospodarstva</li>
                            <li><strong class="text-gray-800">OIB</strong> — osobni identifikacijski broj</li>
                            <li><strong class="text-gray-800">E-mail i lozinka</strong></li>
                        </ul>
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
                            <strong>Napomena:</strong> Naziv gospodarstva, MIPG i OIB pojavljuju se na ispisanim obrascima. Uvijek ih možete promijeniti u postavkama profila.
                        </div>
                    </div>
                </section>

                {{-- 2. PARCELE --}}
                <section id="parcele">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">2</div>
                        <h2 class="text-2xl font-bold text-gray-900">Parcele i kulture</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Prije unosa gnojidbe i prskanja potrebno je unijeti parcele i kulture koje uzgajate. Idite na <strong class="text-gray-800">Parcele &amp; Kulture</strong> u izborniku.</p>

                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Dodavanje parcele</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Kliknite <strong>+ Nova parcela</strong></li>
                            <li>Unesite ARKOD broj parcele i površinu u ha</li>
                            <li>Kliknite <strong>Spremi</strong> — automatski se otvara forma za dodavanje kulture</li>
                        </ol>

                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Dodavanje kulture na parcelu</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Unesite naziv kulture (npr. <em>Pšenica</em>, <em>Kukuruz</em>)</li>
                            <li>Unesite posađenu površinu u ha — ne može biti veća od površine parcele</li>
                            <li>Odaberite godinu uzgoja</li>
                            <li>Koristite <strong>Spremi i nova</strong> za brzi unos više kultura na isti parcel</li>
                        </ol>

                        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800">
                            <strong>Savjet:</strong> Na jednoj parceli može biti više kultura (npr. pšenica i kukuruz u različitim godinama). Svaka kultura se zasebno prati u evidenciji.
                        </div>
                    </div>
                </section>

                {{-- 3. GNOJIDBA --}}
                <section id="gnojidba">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">3</div>
                        <h2 class="text-2xl font-bold text-gray-900">Evidencija gnojidbe</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Svaki unos gnojidbe bilježi: parcelu, kulturu, datum, tip gnojiva i količinu u kg/ha.</p>

                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Dodavanje zapisa</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Kliknite <strong>+ Dodaj</strong></li>
                            <li>U polje za pretragu upišite ARKOD broj ili naziv kulture</li>
                            <li>Odaberite kulturu iz liste</li>
                            <li>Unesite datum, tip gnojiva i količinu kg/ha</li>
                            <li>Kliknite <strong>Spremi</strong></li>
                        </ol>

                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Pretraga i filtriranje</h3>
                        <p>Koristite polja za filtriranje iznad svake kolumne (ARKOD, kultura, datum, gnojivo) za brzo pronalaženje zapisa. Filteri se mogu kombinirati.</p>
                    </div>
                </section>

                {{-- 4. PRSKANJE --}}
                <section id="prskanje">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">4</div>
                        <h2 class="text-2xl font-bold text-gray-900">Evidencija prskanja</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Evidencija prskanja prati sve podatke sukladne AGRONET obrascu: parcelu, kulturu, tretiranu površinu, sredstvo, dozu, vrijeme tretiranja i količinu vode.</p>

                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Dodavanje zapisa</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Kliknite <strong>+ Dodaj</strong></li>
                            <li>Pretražite i odaberite kulturu — <strong>tretirana površina se automatski popunjava</strong> iz posađene površine kulture</li>
                            <li>Unesite datum, naziv sredstva, količinu L/ha</li>
                            <li>Po potrebi unesite vrijeme tretiranja (od–do) i količinu vode L/ha</li>
                            <li>Kliknite <strong>Spremi</strong></li>
                        </ol>

                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
                            <strong>Auto-popunjavanje:</strong> Kada odaberete kulturu, polje <em>Tretirana površina (ha)</em> automatski preuzima posađenu površinu te kulture. Možete je ručno prilagoditi po potrebi.
                        </div>
                    </div>
                </section>

                {{-- ★ VIŠEREDNI UNOS --}}
                <section id="viseredni-unos">
                    <div class="bg-green-600 rounded-2xl p-8 text-white">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-8 h-8 bg-white text-green-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <h2 class="text-2xl font-bold">Višeredni unos — "Dodaj sve"</h2>
                        </div>
                        <p class="text-green-100 leading-relaxed mb-6">
                            Ovo je jedna od najkorisnijih funkcija AgroLoga. Umjesto da isti tretman unosite posebno za svaku kulturu, možete jednim klikom dodati zapise za sve kulture koje pronađe pretraga.
                        </p>

                        <h3 class="font-semibold text-white text-lg mb-3">Kako koristiti:</h3>
                        <ol class="space-y-3 text-green-100">
                            <li class="flex gap-3">
                                <span class="bg-white/20 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">1</span>
                                <span>Kliknite <strong class="text-white">+ Dodaj</strong> za otvaranje forme</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="bg-white/20 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">2</span>
                                <span>U polje pretrage upišite dio naziva kulture ili ARKOD — npr. <em>"Pšenica"</em> ili samo <em>"12"</em></span>
                            </li>
                            <li class="flex gap-3">
                                <span class="bg-white/20 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">3</span>
                                <span>Pojavljuje se gumb <strong class="text-white">"Dodaj sve (X)"</strong> koji pokazuje broj pronađenih kultura</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="bg-white/20 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">4</span>
                                <span>Ispunite datum, gnojivo/sredstvo i količinu, zatim kliknite <strong class="text-white">"Dodaj sve"</strong></span>
                            </li>
                            <li class="flex gap-3">
                                <span class="bg-white/20 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">5</span>
                                <span>Odjednom se kreiraju zasebni zapisi za svaku pronađenu kulturu — svaki s ispravnom površinom</span>
                            </li>
                        </ol>

                        <div class="bg-white/10 border border-white/20 rounded-xl p-4 mt-6 text-sm text-green-100">
                            <strong class="text-white">Primjer:</strong> Imate 8 parcela s pšenicom i trebate unijeti isti tretman herbicidom. Upišite "Pšenica" u pretragu, ispunite sredstvo i kliknite "Dodaj sve (8)" — gotovo u jednom koraku umjesto 8 zasebnih unosa.
                        </div>
                    </div>
                </section>

                {{-- ★ KOPIRANJE --}}
                <section id="kopiranje">
                    <div class="bg-gray-900 rounded-2xl p-8 text-white">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-8 h-8 bg-green-500 text-white rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </div>
                            <h2 class="text-2xl font-bold">Kopiranje zapisa</h2>
                        </div>
                        <p class="text-gray-300 leading-relaxed mb-6">
                            Svaki red u tablici ima gumb za kopiranje. Korisno kada isti tretman ili gnojidbu trebate ponoviti na istoj parceli — bez ponovnog unosa svih podataka.
                        </p>

                        <h3 class="font-semibold text-white text-lg mb-3">Kako funkcionira:</h3>
                        <ul class="space-y-3 text-gray-300">
                            <li class="flex gap-3">
                                <svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Kliknite ikonu kopiranja na desnoj strani retka</span>
                            </li>
                            <li class="flex gap-3">
                                <svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Potvrdite kopiranje u dijalogu</span>
                            </li>
                            <li class="flex gap-3">
                                <svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span>Kreira se novi zapis s <strong class="text-white">identičnim podacima</strong> (kultura, gnojivo/sredstvo, količina...)</span>
                            </li>
                            <li class="flex gap-3">
                                <svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span><strong class="text-white">Datum se postavlja na današnji dan</strong> — kopirani zapis automatski dobiva aktualni datum</span>
                            </li>
                        </ul>

                        <div class="bg-white/5 border border-white/10 rounded-xl p-4 mt-6 text-sm text-gray-300">
                            <strong class="text-white">Primjer:</strong> Godišnje koristite isti herbicid na istoj parceli. Na kraju sezone kopirajte prošlogodišnji zapis — sve je već ispunjeno, samo datum je ažuriran na danas.
                        </div>
                    </div>
                </section>

                {{-- 5. ISPIS --}}
                <section id="ispis">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">5</div>
                        <h2 class="text-2xl font-bold text-gray-900">Ispis PDF-a</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Svaka evidencija može se ispisati kao službeni obrazac.</p>
                        <ol class="list-decimal pl-6 space-y-2">
                            <li>Po potrebi filtrirajte zapise (npr. samo određena kultura ili vremensko razdoblje)</li>
                            <li>Kliknite gumb <strong class="text-gray-800">Print / PDF</strong> u gornjem desnom kutu tablice</li>
                            <li>Otvara se dijaloški okvir za ispis — odaberite <strong class="text-gray-800">Spremi kao PDF</strong> ili direktno ispišite</li>
                        </ol>
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800">
                            <strong>Što se ispisuje:</strong> Zaglavlje s nazivom gospodarstva, MIPG-om i OIB-om, te tablica s vidljivim kolonama. Svi filteri, gumbi i kontrole su automatski skriveni na ispisu.
                        </div>

                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Vidljivost kolumni</h3>
                        <p>Iznad tablice nalaze se kvačice za uključivanje/isključivanje pojedinih kolumni. Na taj način možete prilagoditi ispis — prikazati samo ono što je relevantno za određeni obrazac.</p>
                    </div>
                </section>

                {{-- 6. PROFIL --}}
                <section id="profil">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">6</div>
                        <h2 class="text-2xl font-bold text-gray-900">Profil i postavke</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>U postavkama profila možete promijeniti sve podatke o gospodarstvu koji se pojavljuju na ispisanim obrascima:</p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li>Naziv gospodarstva</li>
                            <li>MIPG broj</li>
                            <li>OIB</li>
                            <li>Ime i prezime</li>
                            <li>E-mail adresa</li>
                            <li>Lozinka</li>
                        </ul>
                        <p>Profilu pristupate klikom na svoje ime u gornjem desnom kutu aplikacije.</p>
                    </div>
                </section>

                {{-- KONTAKT --}}
                <div class="bg-green-50 border border-green-200 rounded-2xl p-8 flex flex-col md:flex-row gap-6 items-start">
                    <div class="w-10 h-10 bg-green-600 rounded-xl flex items-center justify-center flex-shrink-0 mt-1">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-2">Trebate pomoć?</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            Ako nešto ne funkcionira kako treba ili imate prijedlog za poboljšanje, slobodno nas kontaktirajte na
                            <a href="mailto:info@agro-log.app" class="text-green-700 hover:underline font-medium">info@agro-log.app</a>
                            ili na <a href="tel:+385989876697" class="text-green-700 hover:underline font-medium">+385 98 987 66 97</a>.
                            Odgovaramo brzo!
                        </p>
                    </div>
                </div>

            </main>
        </div>
    </div>

    {{-- FOOTER --}}
    @include('partials.footer')

</body>
</html>
