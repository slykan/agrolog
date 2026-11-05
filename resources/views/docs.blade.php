<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upute za korištenje – AgroLog</title>
    <meta name="description" content="Upute za korištenje AgroLoga: ARKOD parcele na karti, evidencija gnojidbe i prskanja, registar sredstava, izračun dušika, prognoza za prskanje i izvoz u Ministarstvov format za 2027.">
    <link rel="canonical" href="{{ route('docs') }}">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('docs') }}">
    <meta property="og:title" content="Upute za korištenje – AgroLog">
    <meta property="og:description" content="Upute za korištenje AgroLoga: ARKOD parcele na karti, evidencija gnojidbe i prskanja, registar sredstava, izračun dušika, prognoza za prskanje i izvoz u Ministarstvov format za 2027.">
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
            <p class="text-lg text-gray-500 leading-relaxed">Sve što trebate znati za vođenje digitalne evidencije — od ARKOD parcele na karti do izvoza za Ministarstvo, korak po korak.</p>
        </div>
    </section>

    @php
        // naslov odjeljka s brojem; $tamno za istaknute (zelene/tamne) kartice
        $naslov = fn ($broj, $tekst) => '<div class="flex items-center gap-3 mb-5"><div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">' . $broj . '</div><h2 class="text-2xl font-bold text-gray-900">' . e($tekst) . '</h2></div>';
    @endphp

    {{-- SADRŽAJ --}}
    <div class="max-w-5xl mx-auto px-6 py-16">
        <div class="flex flex-col lg:flex-row gap-12">

            {{-- SIDEBAR navigacija --}}
            <aside class="lg:w-56 flex-shrink-0">
                <div class="sticky top-24 space-y-1 text-sm">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 mb-3">Na ovoj stranici</p>
                    <a href="#pocetak" class="block text-gray-600 hover:text-green-700 py-1 transition">1. Početak</a>
                    <a href="#pocetna" class="block text-gray-600 hover:text-green-700 py-1 transition">2. Početna: karta i prognoza</a>
                    <a href="#parcele" class="block text-gray-600 hover:text-green-700 py-1 transition">3. Parcele i kulture</a>
                    <a href="#prskanje" class="block text-gray-600 hover:text-green-700 py-1 transition">4. Evidencija prskanja</a>
                    <a href="#gnojidba" class="block text-gray-600 hover:text-green-700 py-1 transition">5. Evidencija gnojidbe</a>
                    <a href="#viseredni-unos" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Višeredni unos</a>
                    <a href="#kopiranje" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Kopiranje zapisa</a>
                    <a href="#skladiste" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Skladište</a>
                    <a href="#troskovi" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Troškovi</a>
                    <a href="#mehanizacija" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Mehanizacija</a>
                    <a href="#izvoz" class="block text-gray-600 hover:text-green-700 py-1 transition">6. Ispis i izvoz</a>
                    <a href="#zamjena" class="block text-gray-600 hover:text-green-700 py-1 transition">7. Zamjena kulture</a>
                    <a href="#profil" class="block text-gray-600 hover:text-green-700 py-1 transition">8. Profil i obavijesti</a>
                    <a href="#pitanja" class="block text-gray-600 hover:text-green-700 py-1 transition">9. Česta pitanja</a>
                </div>
            </aside>

            {{-- SADRŽAJ --}}
            <main class="flex-1 space-y-16 min-w-0">

                {{-- 1. POČETAK --}}
                <section id="pocetak">
                    {!! $naslov(1, 'Početak — registracija i prijava') !!}
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Registracija traje manje od minute. Na stranici <a href="{{ route('register') }}" class="text-green-700 hover:underline font-medium">Registracija</a> unesite:</p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li><strong class="text-gray-800">Naziv gospodarstva</strong> — prikazuje se u zaglavlju obrazaca za ispis</li>
                            <li><strong class="text-gray-800">MIPG broj</strong> — matični identifikacijski broj poljoprivrednog gospodarstva</li>
                            <li><strong class="text-gray-800">OIB</strong> — osobni identifikacijski broj (ide u zaglavlje evidencije za Ministarstvo)</li>
                            <li><strong class="text-gray-800">E-mail i lozinka</strong></li>
                        </ul>
                        <p>Možete se prijaviti i <strong class="text-gray-800">Google računom</strong> — tada podatke o gospodarstvu dopunite u profilu. Nakon registracije stiže vam mail dobrodošlice s prvim koracima.</p>
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
                            <strong>Napomena:</strong> Naziv gospodarstva, MIPG i OIB pojavljuju se na ispisanim obrascima i u izvozu za Ministarstvo. Uvijek ih možete promijeniti u postavkama profila.
                        </div>
                    </div>
                </section>

                {{-- 2. POČETNA --}}
                <section id="pocetna">
                    {!! $naslov(2, 'Početna: karta parcela i prognoza') !!}
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Nakon prijave na početnoj stranici vidite:</p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li><strong class="text-gray-800">Što je novo</strong> — kratka obavijest o novostima u AgroLogu. Zatvorite je s ✕, a pojavit će se opet tek kad stigne nova.</li>
                            <li><strong class="text-gray-800">Prognoza za 5 dana</strong> za područje vaših parcela: vrijeme, temperatura, kiša, vjetar i oznaka je li dan povoljan za prskanje.</li>
                            <li><strong class="text-gray-800">Karta svih parcela</strong> na satelitskoj podlozi, s ARKOD brojevima. Zumirate kotačićem miša ili gumbima +/−, kartu pomičete povlačenjem, a klik na parcelu pokaže kulture na njoj.</li>
                        </ul>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Kada je dan povoljan za prskanje</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border border-gray-200 rounded-lg">
                                <thead class="bg-gray-50 text-gray-700"><tr><th class="text-left px-3 py-2">Uvjet</th><th class="text-left px-3 py-2">Zašto</th></tr></thead>
                                <tbody class="divide-y">
                                    <tr><td class="px-3 py-2">vjetar ispod 15 km/h</td><td class="px-3 py-2">jači vjetar nosi škropivo izvan parcele</td></tr>
                                    <tr><td class="px-3 py-2">vjerojatnost kiše ispod 40 %</td><td class="px-3 py-2">kiša može isprati sredstvo</td></tr>
                                    <tr><td class="px-3 py-2">bez mraza</td><td class="px-3 py-2">smrznute biljke slabo primaju sredstvo</td></tr>
                                    <tr><td class="px-3 py-2">najviša temperatura 5–30 °C</td><td class="px-3 py-2">na hladnom sredstva slabo djeluju, na vrućini škropivo isparava</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p>Kad dan nije povoljan, uz oznaku piše razlog (npr. „nije za prskanje: mraz”). Pomaknete li kartu na drugo područje, prognoza se osvježi za to mjesto.</p>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
                            <strong>Važno:</strong> Oznaka je pomoć pri planiranju. Neka sredstva imaju strože uvjete primjene — uvijek provjerite etiketu.
                        </div>
                    </div>
                </section>

                {{-- 3. PARCELE --}}
                <section id="parcele">
                    {!! $naslov(3, 'Parcele i kulture') !!}
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Prije unosa gnojidbe i prskanja unesite parcele i kulture koje uzgajate — u izborniku <strong class="text-gray-800">Parcele &amp; Kulture</strong>.</p>

                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Dodavanje parcele — ARKOD broj i karta</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Kliknite <strong>+ Nova parcela</strong></li>
                            <li>Upišite <strong>ARKOD broj</strong> i kliknite izvan polja (ili tipku Tab)</li>
                            <li>AgroLog parcelu potraži u javnom ARKOD-u i pokaže je na <strong>satelitskoj karti</strong> — uz kartu piše službena površina, vrsta uporabe (oranica, vinograd…) i lokalni naziv</li>
                            <li>Površina se upiše sama; ako je parcela vaša, kliknite <strong>Potvrdi i spremi</strong></li>
                            <li>Automatski se otvara forma za dodavanje kulture</li>
                        </ol>
                        <p>Ako ARKOD broj nije pronađen (ili servis Agencije trenutno ne odgovara), parcelu možete spremiti i bez karte — samo upišite površinu ručno. Za parcele u zoni zaštite izvorišta ili u području Natura 2000 AgroLog prikaže upozorenje.</p>

                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Karta postojeće parcele</h3>
                        <p>Uz svaki ARKOD broj u popisu parcela nalazi se <strong>ikona karte</strong>. Klikom se otvori karta parcele s usporedbom vaše i ARKOD površine. Ako ste upisali više nego što stoji u ARKOD-u, uz parcelu piše narančasto npr. „ARKOD: 2,02 ha”.</p>

                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Dodavanje kulture na parcelu</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Počnite tipkati naziv — nude se kulture koje ste već upisivali i <strong>popis najčešćih kultura</strong> (pšenica, kukuruz, soja, vinova loza, voćke, povrće…). Možete upisati i bilo koju drugu.</li>
                            <li>Polje <strong>Usjev za evidenciju (EPPO)</strong> popuni se samo iz naziva; po potrebi odaberite drugi usjev s Ministarstvova popisa</li>
                            <li>Unesite posađenu površinu u ha — zbroj kultura ne može biti veći od površine parcele</li>
                            <li>Odaberite godinu uzgoja</li>
                            <li><strong>Spremi i nova</strong> za brzi unos više kultura na istu parcelu</li>
                        </ol>
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800">
                            <strong>EPPO kod</strong> je međunarodna oznaka usjeva koju od 2027. traži Ministarstvova evidencija prskanja (npr. pšenica = TRZAX). U popisu parcela uz kulturu piše njezin kod; kulture bez koda označene su s „bez EPPO koda” — kliknite na tu oznaku i odaberite usjev.
                        </div>
                    </div>
                </section>

                {{-- 4. PRSKANJE --}}
                <section id="prskanje">
                    {!! $naslov(4, 'Evidencija prskanja') !!}
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Evidencija prskanja bilježi sve što traži obrazac i Ministarstvov format za 2027.: parcelu, kulturu, tretiranu površinu, sredstvo i registracijski broj, dozu, vrijeme, količinu vode, fenofazu, vrstu uporabe i sustav proizvodnje.</p>

                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Dodavanje zapisa</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Kliknite <strong>+ Dodaj</strong></li>
                            <li>Pretražite i odaberite kulturu — <strong>tretirana površina se popuni sama</strong> iz posađene površine</li>
                            <li>Počnite tipkati <strong>trgovački naziv sredstva</strong> — nude se sredstva koja ste već koristili i sva sredstva iz <strong>registra FIS-a</strong>. Odabirom se <strong>registracijski broj upiše sam</strong>.</li>
                            <li>Unesite datum, dozu (L/ha ili kg/ha), po potrebi vrijeme od–do i količinu vode</li>
                            <li>U stupcu <strong>Fenofaza / uporaba</strong> upišite fazu razvoja (BBCH, nude se faze za odabranu kulturu), vrstu uporabe (na otvorenom, plastenik…) i je li površina ekološka</li>
                            <li>Kliknite <strong>Spremi</strong></li>
                        </ol>
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
                            <strong>Upozorenja uz sredstvo:</strong> ako registracija sredstva u FIS-u nije aktivna, AgroLog vas upozori da provjerite smije li se još primjenjivati. Ako sredstvo nije pronađeno u registru, provjerite naziv s etikete — zapis se svejedno može spremiti.
                        </div>
                        <p>Vrsta uporabe i sustav proizvodnje pamte se iz zadnjeg unosa, pa ih ne morate birati svaki put.</p>
                    </div>
                </section>

                {{-- 5. GNOJIDBA --}}
                <section id="gnojidba">
                    {!! $naslov(5, 'Evidencija gnojidbe i dušik') !!}
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Svaki unos gnojidbe bilježi parcelu, kulturu, datum, gnojivo s udjelima hraniva i količinu u kg/ha — a AgroLog iz toga računa kilograme dušika po hektaru.</p>

                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Dodavanje zapisa</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Kliknite <strong>+ Dodaj</strong> i odaberite kulturu</li>
                            <li>U polju <strong>Tip gnojiva</strong> odaberite gnojivo s popisa (KAN, UREA, AN, NPK 15-15-15, 7-20-30, MAP, DAP, KCl, stajski gnoj, gnojovka, gnojnica…) — udjeli <strong>N-P-K %</strong> upišu se sami</li>
                            <li>Unesite količinu u kg/ha — odmah se prikaže izračun (npr. 300 kg KAN-a = 81 kg N/ha)</li>
                            <li>Za gnojivo kojeg nema na popisu upišite udjele s deklaracije; kod stajskog gnoja kvačica <strong>stajski</strong> se postavi sama</li>
                            <li>Kliknite <strong>Spremi</strong></li>
                        </ol>
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800 space-y-2">
                            <p><strong>Što je N-P-K i morate li ga upisivati?</strong></p>
                            <p>N-P-K su tri glavna hraniva u gnojivu: <strong>N</strong> = dušik, <strong>P</strong> = fosfor (kao P₂O₅), <strong>K</strong> = kalij (kao K₂O). Brojevi su postoci — „NPK 15-15-15” znači da 100 kg gnojiva sadrži 15 kg dušika, 15 kg fosfora i 15 kg kalija. KAN 27 % ima samo dušik: 27-0-0.</p>
                            <p><strong>Ne morate ih upisivati sami.</strong> Kad gnojivo odaberete s popisa, udjeli se upišu automatski. Upisujete ih samo za gnojivo kojeg nema na popisu — prepišite tri broja s vreće ili deklaracije (npr. 20-10-10). Ako ih ne znate, ostavite prazno: zapis se sprema, samo neće biti izračuna dušika.</p>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800">
                            <strong>Stajski gnoj i gnojovka:</strong> količinu upišite u kilogramima po hektaru (1 tona = 1.000 kg, 1 m³ gnojovke ≈ 1.000 kg). Za izračun se koriste prosječni udjeli hraniva; ako imate analizu gnoja, upišite svoje udjele.
                        </div>

                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Bilanca dušika i granica od 170 kg N/ha</h3>
                        <p>Iznad tablice je pregled dušika za tekuću godinu: koliko je dušika uneseno na svaku kulturu i koliko od toga iz stajskog gnoja, te <strong>prosjek gospodarstva za stajski gnoj</strong>. Kad prijeđe 170 kg N/ha (Akcijski program zaštite voda od nitrata), prikaz postane crven. Filtrom datuma birate godinu.</p>

                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Pretraga i filtriranje</h3>
                        <p>Polja za filtriranje iznad stupaca (ARKOD, kultura, datum, gnojivo) i pretraga desno gore mogu se kombinirati.</p>
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
                            Umjesto da isti tretman ili gnojidbu unosite posebno za svaku kulturu, jednim klikom dodajete zapise za sve kulture koje pronađe pretraga.
                        </p>

                        <h3 class="font-semibold text-white text-lg mb-3">Kako koristiti:</h3>
                        <ol class="space-y-3 text-green-100">
                            <li class="flex gap-3"><span class="bg-white/20 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">1</span><span>Kliknite <strong class="text-white">+ Dodaj</strong> za otvaranje forme</span></li>
                            <li class="flex gap-3"><span class="bg-white/20 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">2</span><span>U polje pretrage upišite dio naziva kulture ili ARKOD — npr. <em>"Pšenica"</em> ili samo <em>"12"</em></span></li>
                            <li class="flex gap-3"><span class="bg-white/20 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">3</span><span>Pojavljuje se gumb <strong class="text-white">"Dodaj sve (X)"</strong> s brojem pronađenih kultura</span></li>
                            <li class="flex gap-3"><span class="bg-white/20 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">4</span><span>Ispunite datum, gnojivo/sredstvo i količinu, zatim kliknite <strong class="text-white">"Dodaj sve"</strong></span></li>
                            <li class="flex gap-3"><span class="bg-white/20 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">5</span><span>Kreiraju se zasebni zapisi za svaku kulturu — svaki s ispravnom površinom (i izračunom dušika kod gnojidbe)</span></li>
                        </ol>

                        <div class="bg-white/10 border border-white/20 rounded-xl p-4 mt-6 text-sm text-green-100">
                            <strong class="text-white">Primjer:</strong> Imate 8 parcela s pšenicom i istim herbicidom. Upišite "Pšenica", odaberite sredstvo (registracijski broj se upiše sam) i kliknite "Dodaj sve (8)" — jedan korak umjesto 8 unosa.
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
                            Svaki red u tablici ima gumb za kopiranje — kada isti tretman ili gnojidbu ponavljate na istoj parceli, bez ponovnog unosa.
                        </p>
                        <ul class="space-y-3 text-gray-300">
                            <li class="flex gap-3"><svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg><span>Kliknite ikonu kopiranja na desnoj strani retka i potvrdite</span></li>
                            <li class="flex gap-3"><svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg><span>Kreira se novi zapis s <strong class="text-white">istim podacima</strong> — kultura, sredstvo i registracijski broj ili gnojivo s udjelima, količina, fenofaza…</span></li>
                            <li class="flex gap-3"><svg class="w-5 h-5 text-green-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg><span><strong class="text-white">Datum se postavlja na današnji</strong> — po potrebi ga uredite</span></li>
                        </ul>
                    </div>
                </section>

                {{-- SKLADIŠTE (neobavezno) --}}
                <section id="skladiste">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">★</div>
                        <h2 class="text-2xl font-bold text-gray-900">Skladište sredstava i gnojiva (neobavezno)</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Skladište pokazuje koliko vam je ostalo svakog sredstva i gnojiva. Vi upisujete samo nabavu, a potrošnju AgroLog računa iz evidencije.</p>
                        <h3 class="font-semibold text-gray-800 text-lg">Prvo skladište</h3>
                        <p>U izborniku kliknite <strong class="text-gray-800">Skladište</strong> i upišite naziv, npr. „Glavno skladište”. Dok ne dodate skladište, AgroLog ne prati zalihe i ništa se ne mijenja.</p>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Više skladišta</h3>
                        <ul class="list-disc pl-6 space-y-1">
                            <li>Gumbom <strong class="text-gray-800">+ Skladište</strong> dodate drugo (npr. „Ekonomija”). Svako ima svoje stanje.</li>
                            <li>Prvo skladište je <strong>glavno (★)</strong>. Iz njega se troši kad kod prskanja ili gnojidbe ne izaberete drugo. Glavno možete promijeniti.</li>
                            <li>Kod prskanja i gnojidbe, ispod naziva sredstva ili gnojiva, birate <strong>Iz skladišta</strong>.</li>
                            <li><strong>Prijenos:</strong> u detaljima artikla upišete količinu i skladište u koje prenosite. Brisanjem prijenosa poništava se u oba skladišta.</li>
                            <li>Skladište se može obrisati tek kad je prazno (bez ulaza).</li>
                        </ul>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Nabava (ulaz)</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Kliknite <strong class="text-gray-800">+ Nabava sredstva</strong> ili <strong class="text-gray-800">+ Nabava gnojiva</strong></li>
                            <li>Odaberite naziv s popisa: sredstva iz registra FIS-a, gnojiva s popisa ili ona koja ste već koristili</li>
                            <li>Upišite datum i ukupnu količinu (npr. 4 kanistra po 5 L = 20 L, ili 2 t gnojiva). Cijenu i dobavljača možete preskočiti</li>
                        </ol>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Potrošnja (izlaz) ide sama</h3>
                        <p>Za svaki zapis prskanja ili gnojidbe s istim nazivom AgroLog oduzme <strong>dozu po hektaru × površinu</strong>: kod prskanja tretiranu površinu, a kod gnojidbe površinu kulture. Ako zapis izmijenite ili obrišete, stanje se samo ispravi. Računaju se samo zapisi od datuma prve nabave, pa starija evidencija ne utječe na stanje.</p>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-900">
                            <strong>Pazite na dvije stvari:</strong> (1) naziv u evidenciji mora biti isti kao na skladištu (velika i mala slova nisu bitna), a najlakše je odabrati ga s popisa. (2) Jedinica mora odgovarati dozi: ako dozu sredstva upisujete u L/ha, nabavu upišite u litrama, a za praškasta sredstva u kg.
                        </div>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Upozorenja i inventura</h3>
                        <ul class="list-disc pl-6 space-y-1">
                            <li>Pri unosu prskanja ili gnojidbe ispod naziva piše koliko je na skladištu i koliko ostaje nakon tog zapisa.</li>
                            <li>Artikl s manje od 15 % nabavljene količine označen je <strong>pri kraju</strong>, a artikl u minusu crveno. Popis je i na početnoj stranici.</li>
                            <li>Unos u evidenciju se nikad ne blokira. Minus samo znači da nabava još nije upisana.</li>
                            <li><strong>Inventura:</strong> u detaljima artikla upišite stvarnu količinu i AgroLog doda ispravak za razliku.</li>
                        </ul>
                        <p>Ako uz nabavu upišete cijenu, vidi se prosječna cijena po L ili kg i vrijednost zalihe.</p>
                    </div>
                </section>

                {{-- TROŠKOVI --}}
                <section id="troskovi">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">★</div>
                        <h2 class="text-2xl font-bold text-gray-900">Troškovi po parceli i hektaru</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Stranica <strong class="text-gray-800">Troškovi</strong> računa koliko vas stoji zaštita bilja i gnojidba, iz <a href="#skladiste" class="text-green-700 hover:underline">skladišta</a> i evidencije:</p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li><strong>Nabava</strong> su ulazi na skladište s upisanom cijenom, po mjesecima i po vrsti (zaštita bilja, gnojivo). Prijenosi između skladišta i inventure se ne broje.</li>
                            <li><strong>Utrošak</strong> je doza × površina iz svakog zapisa prskanja i gnojidbe, pomnožena s prosječnom nabavnom cijenom tog sredstva ili gnojiva.</li>
                            <li>Tablica <strong>Trošak po parceli i kulturi</strong> pokazuje iznos za zaštitu, gnojidbu, ukupno i po hektaru.</li>
                        </ul>
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800">
                            <strong>Savjet:</strong> brojke su točne samo ako uz nabavu upišete cijenu. Ako neko sredstvo nema cijenu, ispod tablice piše koliko zapisa nije uračunato.
                        </div>
                    </div>
                </section>

                {{-- MEHANIZACIJA (neobavezno) --}}
                <section id="mehanizacija">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">★</div>
                        <h2 class="text-2xl font-bold text-gray-900">Mehanizacija: rokovi i podsjetnici (neobavezno)</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Za traktore, priključke i vozila vodite registraciju, tehnički pregled, osiguranje, servis i pregled prskalice. AgroLog vas mailom podsjeti prije isteka. Dok ne dodate prvi stroj, ništa se ne prikazuje ni ne šalje.</p>
                        <h3 class="font-semibold text-gray-800 text-lg">Dodavanje stroja</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>U izborniku <strong class="text-gray-800">Mehanizacija</strong> kliknite <strong class="text-gray-800">+ Dodaj stroj ili vozilo</strong></li>
                            <li>Odaberite vrstu (traktor, prskalica, prikolica…) i upišite naziv, npr. „John Deere 6120M”, te po želji registraciju i godinu</li>
                            <li>Ispod odmah upišite do kada vrijede rokovi koji se za tu vrstu nude (npr. registracija i osiguranje za traktor, pregled za prskalicu). Može i kasnije</li>
                        </ol>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Rokovi i podsjetnik</h3>
                        <ul class="list-disc pl-6 space-y-1">
                            <li>Uz svaki stroj su gumbi <strong>+ Registracija</strong>, <strong>+ Osiguranje (AO)</strong>… za rokove koji još nisu upisani, i <strong>+ Drugi rok</strong> za bilo što svoje.</li>
                            <li>Boja pokazuje stanje: zeleno vrijedi, žuto ističe uskoro, crveno je isteklo. Hitni rokovi su i na početnoj stranici.</li>
                            <li><strong>Podsjetnik mailom</strong> stiže zadano 30 dana prije isteka, a ako rok prođe, još jedan. Za svaki rok možete izabrati 7 do 90 dana ili „bez podsjetnika”.</li>
                        </ul>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Obnovljeno</h3>
                        <p>Kad obavite registraciju, pregled ili platite osiguranje, kliknite <strong class="text-gray-800">Obnovljeno</strong>. AgroLog predloži sljedeći datum (+1 godina, a za pregled prskalice +3 godine), vi ga po potrebi ispravite i spremite. Stari rok ostaje u povijesti stroja.</p>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Servisna knjiga</h3>
                        <p><strong class="text-gray-800">+ Servis</strong> upisuje datum, što je rađeno, radne sate, trošak i servisera. Ako stroj ima rok servisa, on se sam pomakne godinu dana od datuma servisa. Popis svih servisa s ukupnim troškom je pod <strong>Servisna knjiga</strong>.</p>
                    </div>
                </section>

                {{-- 6. ISPIS I IZVOZ --}}
                <section id="izvoz">
                    {!! $naslov(6, 'Ispis i izvoz — PDF, Excel za Ministarstvo, CSV') !!}
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <h3 class="font-semibold text-gray-800 text-lg">Ispis / PDF</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Po potrebi filtrirajte zapise (kultura, ARKOD, datum) i kvačicama iznad tablice odaberite stupce</li>
                            <li>Kliknite <strong class="text-gray-800">Print / PDF</strong> i u dijalogu odaberite <strong class="text-gray-800">Spremi kao PDF</strong> ili ispišite</li>
                        </ol>
                        <p>Evidencija se ispisuje na <strong>A4 položeno</strong>, tako da cijela tablica stane u širinu lista. Kod više zapisa ide na sljedeći list s ponovljenim zaglavljem tablice. Gumbi, filteri i pregled dušika se ne ispisuju.</p>

                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Excel (Ministarstvo) — format za 2027.</h3>
                        <p>Od <strong>1. siječnja 2027.</strong> evidencija o uporabi sredstava za zaštitu bilja mora biti u propisanom elektroničkom formatu (Provedbena uredba (EU) 2023/564). U evidenciji prskanja gumb <strong class="text-gray-800">Excel (Ministarstvo)</strong> preuzima Ministarstvovu tablicu „Evidencija SBZ.xlsx” već popunjenu vašim zapisima — s vašim imenom i OIB-om u zaglavlju, EPPO kodovima, fenofazama, vrstom uporabe i sustavom proizvodnje.</p>
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800">
                            <strong>Savjet:</strong> prije preuzimanja provjerite imaju li sve kulture EPPO kod (Parcele &amp; Kulture) i upišite fenofazu gdje je važna. Više u novosti <a href="{{ route('novost', 'evidencija-szb-format-ministarstva-2027') }}" class="underline">Evidencija prskanja u formatu Ministarstva</a>.
                        </div>

                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Izvoz CSV</h3>
                        <p>Gumb <strong class="text-gray-800">Izvoz CSV</strong> u evidenciji prskanja daje tablicu za Excel (strojno čitljivo, s točkom-zarezom) — za savjetnika, zadrugu ili vlastitu arhivu.</p>
                    </div>
                </section>

                {{-- 7. ZAMJENA KULTURE --}}
                <section id="zamjena">
                    {!! $naslov(7, 'Zamjena kulture i čuvanje povijesti') !!}
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Evidencije se moraju čuvati, pa AgroLog ne dopušta da promjena kulture prepiše stare zapise:</p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li><strong class="text-gray-800">Zamijeni kulturu</strong> — nova kultura na istoj parceli (npr. nakon žetve ječma sijete kukuruz). Stara se označi završenom i ostaje u evidenciji sa svim zapisima.</li>
                            <li><strong class="text-gray-800">Završi</strong> — kultura je požnjevena; ne nudi se više za nove zapise. <strong>Vrati</strong> je ponovno aktivira.</li>
                            <li>Naziv kulture koja već ima zapise ne može se mijenjati, a kultura ili parcela sa zapisima ne može se obrisati.</li>
                        </ul>
                    </div>
                </section>

                {{-- 8. PROFIL I OBAVIJESTI --}}
                <section id="profil">
                    {!! $naslov(8, 'Profil i obavijesti') !!}
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Profilu pristupate klikom na svoje ime u gornjem desnom kutu. Tamo mijenjate podatke koji se pojavljuju na obrascima i u izvozu:</p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li>Naziv gospodarstva, MIPG broj, OIB</li>
                            <li>Ime i prezime, e-mail adresa, lozinka</li>
                        </ul>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Obavijesti mailom</h3>
                        <p>Povremeno šaljemo kratku obavijest o važnim novostima u AgroLogu i promjenama propisa o evidencijama. Ne želite ih primati? U dnu svakog takvog maila je poveznica <strong>Odjavite se jednim klikom</strong>. Odjava ne utječe na vaš račun ni na evidencije. Sve novosti su i na stranici <a href="{{ route('novosti') }}" class="text-green-700 hover:underline font-medium">Novosti</a>.</p>
                    </div>
                </section>

                {{-- 9. ČESTA PITANJA --}}
                <section id="pitanja">
                    {!! $naslov(9, 'Česta pitanja') !!}
                    <div class="space-y-3">
                        @foreach ([
                            ['Je li AgroLog stvarno besplatan?', 'Da. Nema pretplate, kreditne kartice ni skrivenih troškova.'],
                            ['ARKOD broj nije pronađen — što sad?', 'Provjerite broj u ARKOD pregledniku (preglednik.arkod.hr). Ako je ispravan, a servis Agencije trenutno ne odgovara, parcelu spremite bez karte i kasnije kliknite ikonu karte uz nju — oblik će se dohvatiti kad servis proradi.'],
                            ['Zašto je moja površina drukčija od ARKOD-a?', 'AgroLog uzima površinu iz javnog ARKOD sloja Agencije za plaćanja. Ako ste upisali više, uz parcelu piše ARKOD površina — dobro je provjeriti prije prijave potpora.'],
                            ['Sredstvo koje koristim nije u registru?', 'Provjerite točan naziv s etikete (npr. „FOLICUR EW 250”). Ako ga ipak nema, upišite naziv i registracijski broj ručno — zapis se sprema normalno.'],
                            ['Što je N-P-K kod gnojiva i moram li ga upisivati?', 'N-P-K su postoci dušika, fosfora i kalija u gnojivu (npr. NPK 15-15-15 = po 15 %). Kad gnojivo odaberete s popisa, udjeli se upišu sami. Ručno ih upisujete samo za gnojivo kojeg nema na popisu — tri broja s vreće. Ako ih ne znate, ostavite prazno; tada samo nema izračuna dušika.'],
                            ['Moram li upisati fenofazu (BBCH)?', 'Ministarstvov format je traži „kad je relevantna za kulturu i primjenu”. Preporučujemo da je upišete — za odabranu kulturu nude se faze s Ministarstvova popisa.'],
                            ['Što s evidencijom iz 2026.?', 'Primjene do 31. 12. 2026. ne moraju se prenositi u novi format, ali i za njih možete preuzeti Excel (Ministarstvo). AgroLog je već upisao vrstu uporabe „na otvorenom” i neekološku površinu za postojeće zapise — promijenite ih gdje treba.'],
                            ['Mogu li koristiti AgroLog na mobitelu?', 'Da, stranice se prilagođavaju ekranu mobitela i tableta. Za veće tablice evidencije praktičnije je računalo ili tablet položeno.'],
                            ['Tko vidi moje podatke?', 'Samo vi. Detalje pročitajte u Politici privatnosti.'],
                        ] as [$pitanje, $odgovor])
                            <details class="group rounded-xl border border-gray-200 bg-white px-5 py-4 open:shadow-sm">
                                <summary class="cursor-pointer list-none flex justify-between items-center gap-3 font-semibold text-gray-800">
                                    {{ $pitanje }}
                                    <span class="text-green-600 transition group-open:rotate-45 text-xl leading-none">+</span>
                                </summary>
                                <p class="mt-3 text-sm text-gray-600 leading-relaxed">{{ $odgovor }}</p>
                            </details>
                        @endforeach
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
                            Odgovaramo brzo! Pogledajte i <a href="{{ route('vodici') }}" class="text-green-700 hover:underline font-medium">vodiče</a> i <a href="{{ route('novosti') }}" class="text-green-700 hover:underline font-medium">novosti</a>.
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
