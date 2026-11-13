<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Upute za korištenje – AgroLog</title>
    <meta name="description" content="Upute za korištenje AgroLoga: ARKOD parcele na karti, evidencija gnojidbe i prskanja, registar sredstava, izračun dušika, prognoza za prskanje i izvoz u Ministarstvov format za 2027.">
    <link rel="canonical" href="{{ route('docs') }}">
    @include('partials.favicon')
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
                    <a href="#parcela-bez-arkoda" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Parcela bez ARKOD-a</a>
                    <a href="#prskanje" class="block text-gray-600 hover:text-green-700 py-1 transition">4. Evidencija prskanja</a>
                    <a href="#provjera-fis" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Provjera prema FIS-u</a>
                    <a href="#gnojidba" class="block text-gray-600 hover:text-green-700 py-1 transition">5. Evidencija gnojidbe</a>
                    <a href="#berba" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Berba i trajni nasadi</a>
                    <a href="#vocari" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Upute za voćare i vinogradare</a>
                    <a href="#viseredni-unos" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Višeredni unos</a>
                    <a href="#kopiranje" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Kopiranje zapisa</a>
                    <a href="#skladiste" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Skladište</a>
                    <a href="#troskovi" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Troškovi i dobit</a>
                    <a href="#ostali-radovi" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Ostali radovi</a>
                    <a href="#gorivo" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Gorivo po danu</a>
                    <a href="#kalendar" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Kalendar</a>
                    <a href="#opci-troskovi" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Opći troškovi i vožnja</a>
                    <a href="#mehanizacija" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Mehanizacija</a>
                    <a href="#izvoz" class="block text-gray-600 hover:text-green-700 py-1 transition">6. Ispis i izvoz</a>
                    <a href="#zamjena" class="block text-gray-600 hover:text-green-700 py-1 transition">7. Zamjena kulture</a>
                    <a href="#profil" class="block text-gray-600 hover:text-green-700 py-1 transition">8. Profil i obavijesti</a>
                    <a href="#gospodarstva" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Gospodarstvo na javnom popisu</a>
                    <a href="#trznica" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Tržnica</a>
                    <a href="#obavijesti" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Push i mail obavijesti</a>
                    <a href="#savjetnik" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Pristup savjetniku</a>
                    <a href="#preporuke" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ Preporuke savjetnika</a>
                    <a href="#mobitel" class="block text-green-700 font-semibold hover:text-green-800 py-1 transition">★ AgroLog na mobitelu</a>
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
                            <li><strong class="text-gray-800">Karta svih parcela</strong> na satelitskoj podlozi, s ARKOD brojevima. Zumirate kotačićem miša ili gumbima +/−, kartu pomičete povlačenjem, a klik na parcelu pokaže kulture na njoj. Gumbom ⛶ kartu otvorite <strong>preko cijelog zaslona</strong> (Esc ili isti gumb je zatvara).</li>
                            <li><strong class="text-gray-800">Pregled gospodarstva</strong> ispod karte — za odabrano razdoblje (ova godina, prošla, 12 mjeseci ili od–do): površina, parcele i kulture, broj prskanja i gnojidbi s usporedbom prema godini ranije, graf po mjesecima i što treba dopuniti za evidenciju 2027.</li>
                            <li><strong class="text-gray-800">Kartice</strong> ispod — Parcele, Gnojidba, Prskanje, Berba, Skladište, Mehanizacija i Troškovi, svaka s brojkom i malim grafom za zadnjih 12 mjeseci. Klik vodi na tu stranicu.</li>
                            <li><strong class="text-gray-800">Zvono</strong> uz vaše ime u gornjem desnom kutu — broj pokazuje koliko stvari traži pažnju: aktivne karence, rokovi mehanizacije koji ističu, sredstva pri kraju ili u minusu na skladištu i zapisi koje treba dopuniti za evidenciju 2027. Klik otvara kratki popis, a <strong>Više</strong> vodi na stranicu gdje se to rješava.</li>
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

                        <div id="sto-je-arkod" class="scroll-mt-24 bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-900 space-y-2">
                            <p class="font-semibold">Što je ARKOD i gdje naći svoj broj?</p>
                            <p><strong>ARKOD</strong> je digitalna evidencija uporabe poljoprivrednog zemljišta, koju objavljuje Agencija za plaćanja u poljoprivredi, ribarstvu i ruralnom razvoju. ARKOD parcela je neprekinuta površina poljoprivrednog zemljišta koju obrađuje jedno gospodarstvo.</p>
                            <p>Podatke o svojim parcelama možete vidjeti na dva mjesta:</p>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>u javnom <a href="https://preglednik.arkod.hr/ARKOD-Web/" target="_blank" rel="noopener" class="font-medium underline">ARKOD pregledniku</a>, pomoću MIBPG-a, matičnog broja koji ste dobili pri upisu gospodarstva,</li>
                                <li>u aplikaciji <strong>AGRONET</strong>, ako imate korisničko ime i zaporku.</li>
                            </ul>
                            <p>Ako parcelu ne možete pronaći, obratite se Agenciji. Više na <a href="https://www.apprrr.hr/arkod/" target="_blank" rel="noopener" class="font-medium underline">stranici Agencije o ARKOD-u</a>.</p>
                            <p><strong>Nemate ARKOD?</strong> Parcelu možete <a href="#parcela-bez-arkoda" class="font-medium underline">ručno ucrtati na karti</a>.</p>
                        </div>

                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Dodavanje parcele — ARKOD broj i karta</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Kliknite <strong>+ Nova parcela</strong></li>
                            <li>Upišite <strong>ARKOD broj</strong> i kliknite izvan polja (ili tipku Tab)</li>
                            <li>AgroLog parcelu potraži u javnom ARKOD-u i pokaže je na <strong>satelitskoj karti</strong> — uz kartu piše službena površina, vrsta uporabe (oranica, vinograd…) i lokalni naziv</li>
                            <li>Površina se upiše sama; ako je parcela vaša, kliknite <strong>Potvrdi i spremi</strong></li>
                            <li>Automatski se otvara forma za dodavanje kulture</li>
                        </ol>
                        <p>Ako ARKOD broj nije pronađen (ili servis Agencije trenutno ne odgovara), parcelu možete spremiti i bez karte — samo upišite površinu ručno. Za parcele u zoni zaštite izvorišta ili u području Natura 2000 AgroLog prikaže upozorenje.</p>

                        <h3 id="parcela-bez-arkoda" class="scroll-mt-24 font-semibold text-gray-800 text-lg mt-6">Parcela bez ARKOD-a: ucrtajte je na karti</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Kliknite <strong>+ Nova parcela</strong> pa <strong>Nemam ARKOD, ucrtaj na karti</strong></li>
                            <li>Upišite <strong>naziv</strong>, npr. „Iza kuće”</li>
                            <li>Nađite parcelu pretragom adrese ili mjesta, npr. „Ferenca Kiša 15, Hrastin”, ili gumbom <strong>Moja lokacija</strong> pa približite kartu. Plava točka je pronađena adresa, ali provjerite je na snimci jer ne mora biti posve točna</li>
                            <li>Redom kliknite ili dotaknite uglove parcele. Površina se računa sama, a možete je i ispraviti</li>
                            <li>Kliknite <strong>Spremi</strong>. Dalje parcelu koristite kao i svaku drugu</li>
                        </ol>
                        <p>Na mobitelu možete hodati po rubu parcele i na svakom uglu kliknuti <strong>Dodaj točku gdje stojim</strong>. Pogrešnu točku maknite gumbom <strong>Poništi zadnju točku</strong>.</p>
                        <p>Ručno ucrtana parcela služi za vašu evidenciju i <strong>nije službena ARKOD parcela</strong>, a površina je približna. U ispisima i izvozima piše „Ručno ucrtana” uz naziv. Kad dobijete ARKOD broj, kliknite <strong>Uredi</strong> pa <strong>Imam ARKOD broj</strong> i upišite ga. Sva evidencija ostaje na parceli.</p>

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
                            <li>U izborniku <strong>Aktivnosti → Prskanje</strong> kliknite <strong>+ Dodaj</strong></li>
                            <li>Pretražite i odaberite kulturu — <strong>tretirana površina se popuni sama</strong> iz posađene površine</li>
                            <li>Počnite tipkati <strong>trgovački naziv sredstva</strong> — nude se sredstva koja ste već koristili i sva sredstva iz <strong>registra FIS-a</strong>. Odabirom se <strong>registracijski broj upiše sam</strong>.</li>
                            <li>Unesite datum, dozu (L/ha ili kg/ha), po potrebi vrijeme od–do (24-satni zapis, npr. 17:30) i količinu vode</li>
                            <li>U stupcu <strong>Fenofaza / uporaba</strong> upišite fazu razvoja (BBCH, nude se faze za odabranu kulturu), vrstu uporabe (na otvorenom, plastenik…) i je li površina ekološka</li>
                            <li>Kliknite <strong>Spremi</strong></li>
                        </ol>
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
                            <strong>Upozorenja uz sredstvo:</strong> ako registracija sredstva u FIS-u nije aktivna, AgroLog vas upozori da provjerite smije li se još primjenjivati. Ako sredstvo nije pronađeno u registru, provjerite naziv s etikete — zapis se svejedno može spremiti.
                        </div>
                        <p>Vrsta uporabe i sustav proizvodnje pamte se iz zadnjeg unosa, pa ih ne morate birati svaki put.</p>
                    </div>
                </section>

                {{-- PROVJERA PREMA FIS-u --}}
                <section id="provjera-fis">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">★</div>
                        <h2 class="text-2xl font-bold text-gray-900">Provjera prskanja prema registru FIS-a</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Kad u prskanju odaberete kulturu i sredstvo iz registra, AgroLog odmah ispod sredstva pokaže što o toj kombinaciji piše u registru Ministarstva (FIS):</p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li><strong>registrirano za kulturu</strong> ✓ — ili upozorenje ako sredstvo za tu kulturu nije registrirano;</li>
                            <li><strong>karenca</strong> — broj dana i datum prije kojeg se ne smije brati ni žeti (npr. „žetva ne prije 05.06.2026.”);</li>
                            <li><strong>broj tretmana</strong> — koliko je primjena dozvoljeno u sezoni i koliko ih je već upisano za tu kulturu;</li>
                            <li><strong>doza i način primjene</strong> iz registracije, na klik.</li>
                        </ul>
                        <p>Aktivne karence vide se i u <strong>zvonu</strong> uz vaše ime, dok ne prođu.</p>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
                            <strong>Važno:</strong> provjera je pomoć, ne zamjena za etiketu. Spremanje se nikad ne blokira. Ako registar trenutno ne odgovara, piše da provjera nije dostupna, a zapis spremite normalno.
                        </div>
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

                {{-- BERBA I TRAJNI NASADI --}}
                <section id="berba">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">★</div>
                        <h2 class="text-2xl font-bold text-gray-900">Berba, žetva i trajni nasadi</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <h3 class="font-semibold text-gray-800 text-lg">Upis berbe ili žetve</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>U izborniku <strong>Aktivnosti → Berba i žetva</strong> kliknite <strong>+ Dodaj berbu</strong></li>
                            <li>Odaberite kulturu i datum te upišite količinu u kg ili t</li>
                            <li>Po želji dodajte cijenu, sortu, klasu, pobranu površinu, kupca i napomenu</li>
                        </ol>
                        <p>Jedna kultura može imati koliko god berbi, npr. tri branja iste sorte. Stranica pokazuje ukupnu količinu, vrijednost i prinos po hektaru za odabranu godinu.</p>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
                            <strong>Karenca:</strong> AgroLog usporedi datum berbe s upisanim prskanjima te kulture i registrom FIS-a. Ako karenca nije prošla, upozori vas s nazivom sredstva i datumom. Spremanje se ne blokira, a provjera vrijedi samo za upisana prskanja. Mjerodavna je etiketa sredstva.
                        </div>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Prihod i dobit</h3>
                        <p>Čim kultura ima upisane berbe, prihod na stranici <a href="#troskovi" class="text-green-700 hover:underline">Troškovi</a> računa se iz njih, za odabrano razdoblje. Berbe bez cijene nisu uračunate, a to piše ispod tablice. Kultura bez berbi i dalje koristi ručni upis pod <strong>Prinos</strong>. Ubrani plod ne ulazi u Skladište.</p>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Trajni nasadi: voćnjak, vinograd, maslinik</h3>
                        <ul class="list-disc pl-6 space-y-1">
                            <li>Pri dodavanju kulture prvo označite <strong>Trajni nasad</strong> na vrhu forme. Polje Godina se tada skriva jer nasad ostaje na parceli svake godine, a po želji upišite sortu, godinu sadnje i broj stabala. Oznaka se sama predloži za voćke i vinovu lozu te za parcele koje su u ARKOD-u voćnjak, vinograd ili maslinik.</li>
                            <li>Postojeću kulturu možete označiti gumbom <strong>označi kao trajni nasad</strong> uz njezin naziv, ili pod <strong>Uredi</strong>.</li>
                            <li>Nasad zauzima površinu parcele svake godine, dok ga ne označite kao <strong>iskrčen</strong>. Zapisi ostaju u evidenciji.</li>
                            <li>Gumb <strong>Troškovi i potpore</strong> upisuje potpore i ostale troškove za svaku godinu posebno, a dobit se računa po godinama.</li>
                            <li>Broj tretmana u FIS provjeri za nasad se broji za godinu prskanja.</li>
                        </ul>
                    </div>
                </section>

                {{-- UPUTE ZA VOĆARE --}}
                <section id="vocari">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">★</div>
                        <h2 class="text-2xl font-bold text-gray-900">Upute za voćare i vinogradare: korak po korak</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <ol class="list-decimal pl-6 space-y-3">
                            <li><strong class="text-gray-800">Dodajte parcelu.</strong> Pod <strong>Parcele &amp; Kulture</strong> kliknite <strong>+ Nova parcela</strong> i upišite ARKOD broj.</li>
                            <li><strong class="text-gray-800">Dodajte nasad.</strong> Kliknite <strong>+ Kultura</strong> i upišite naziv, npr. „Jabuka”. Oznaka <strong>Trajni nasad</strong> se predloži sama, a po želji dodajte sortu, godinu sadnje i broj stabala. Ako imate više sorti, upišite svaku kao posebnu kulturu s njezinom površinom, npr. „Jabuka Idared” i „Jabuka Gala”.</li>
                            <li><strong class="text-gray-800">Vodite prskanje i gnojidbu.</strong> Upisujete ih kao i za druge kulture, pod <strong>Aktivnosti</strong>. Nasad ne upisujete ponovno svake godine. Provjera prema FIS-u broj tretmana za nasad računa po godini.</li>
                            <li><strong class="text-gray-800">Upišite svaku berbu.</strong> Pod <strong>Aktivnosti → Berba i žetva</strong> dodajte zaseban zapis za svako branje, s količinom i po želji cijenom, klasom i kupcem. Ako karenca nekog upisanog prskanja nije prošla, AgroLog vas upozori.</li>
                            <li><strong class="text-gray-800">Upišite potpore i ostale troškove.</strong> Uz nasad kliknite <strong>Troškovi i potpore</strong>, odaberite godinu i upišite iznose, npr. sadnice, rezidbu ili usluge. Za svaku godinu upisujete posebno.</li>
                            <li><strong class="text-gray-800">Pogledajte dobit.</strong> Stranica <strong>Troškovi</strong> za odabrano razdoblje pokazuje prihod iz berbi, troškove i dobit po hektaru.</li>
                            <li><strong class="text-gray-800">Kad nasad više ne postoji,</strong> kliknite <strong>Iskrči</strong>. Površina parcele se oslobađa, a sva evidencija ostaje sačuvana.</li>
                        </ol>
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
                            <strong>Već imate voćke upisane kao običnu kulturu?</strong> Kliknite <strong>označi kao trajni nasad</strong> uz njezin naziv. Ako ste isti nasad upisali posebno za svaku godinu, označite samo jedan zapis, a stare završite, inače se površina računa dvaput.
                        </div>
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
                            <li><strong>Radovi i ostalo</strong> su <a href="#ostali-radovi" class="text-green-700 hover:underline">ostali radovi</a> (oranje, rezidba, košnja…) i <a href="#opci-troskovi" class="text-green-700 hover:underline">opći troškovi</a> koje ste vezali uz kulturu.</li>
                            <li><strong>Gorivo</strong> je <a href="#gorivo" class="text-green-700 hover:underline">gorivo upisano po danu</a>, podijeljeno na prskanja, gnojidbe i radove tog dana po hektarima.</li>
                            <li>Tablica <strong>Trošak po parceli i kulturi</strong> pokazuje iznos za zaštitu, gnojidbu, radove i ostalo, ukupno i po hektaru. Gumbima <strong>Parcela / Kultura</strong> desno birate grupiranje, a graf ispod pokazuje isto.</li>
                            <li><strong>Mehanizacija</strong> na dnu zbraja troškove servisa iz <a href="#mehanizacija" class="text-green-700 hover:underline">servisne knjige</a> i iznose uz rokove (registracija, osiguranje…). Gore su zbrojevi <strong>Trošak mehanizacije</strong>, <strong>Opći troškovi</strong> i <strong>Sveukupni trošak</strong> (parcele + mehanizacija + opći troškovi).</li>
                            <li>Razdoblje birate kao i na početnoj: ova godina, prošla, 12 mjeseci ili od–do.</li>
                        </ul>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Dobit po hektaru</h3>
                        <p>Kod kulture (Parcele &amp; Kulture) kliknite <strong class="text-gray-800">Prinos</strong> i upišite prinos (t), prodajnu cijenu (€/t), potpore i po želji sjeme i ostale troškove. Radove i vožnje upišite posebno, pod <a href="#ostali-radovi" class="text-green-700 hover:underline">Ostali radovi</a> i <a href="#opci-troskovi" class="text-green-700 hover:underline">Opći troškovi</a>, da se ne zbroje dvaput. Na Troškovima se tada vidi <strong>prihod, trošak i dobit po kulturi i po hektaru</strong>, a gubitak je označen crveno.</p>
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800">
                            <strong>Savjet:</strong> brojke su točne samo ako uz nabavu upišete cijenu. Ako neko sredstvo nema cijenu, ispod tablice piše koliko zapisa nije uračunato.
                        </div>
                    </div>
                </section>

                {{-- OSTALI RADOVI --}}
                <section id="ostali-radovi" class="scroll-mt-24">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">★</div>
                        <h2 class="text-2xl font-bold text-gray-900">Ostali radovi: oranje, rezidba, košnja…</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Osim prskanja, gnojidbe i berbe, na parceli se radi još puno toga. Upišite i te radove pa će Troškovi pokazati pravu cijenu proizvodnje po hektaru.</p>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>U izborniku <strong>Aktivnosti → Ostali radovi</strong> kliknite <strong>+ Dodaj rad</strong></li>
                            <li>Odaberite kulturu i datum</li>
                            <li>Kliknite vrstu rada. AgroLog nudi radove prema kulturi: za ratarske kulture oranje, tanjuranje, sjetvu, valjanje…, za vinograd rezidbu, vezanje, zelenu rezidbu…, za maslinik i voćnjak rezidbu, košnju, usitnjavanje granja… Ako vaše vrste nema, <strong>upišite svoju</strong>, a ona vam se nudi i sljedeći put</li>
                            <li>Odaberite tko je radio i upišite trošak (može i bez troška)</li>
                        </ol>
                        <ul class="list-disc pl-6 space-y-1">
                            <li><strong>Vlastiti rad:</strong> po želji odaberite stroj iz Mehanizacije i sate rada, a za trošak upišite gorivo (L × €/L) i rad (radni sati × €/h). Dva radnika po 8 sati su 16 radnih sati. Cijene goriva i sata pamte se za sljedeći unos. Ako gorivo ne upišete ovdje, rad ulazi u <a href="#gorivo" class="text-green-700 hover:underline">gorivo po danu</a>.</li>
                            <li><strong>Usluga:</strong> kad posao radi netko drugi, npr. susjed ore za 60 €/ha. Upišite cijenu po hektaru ili ukupno i po želji izvođača. Po hektaru se računa na obrađenu površinu ili, ako je ne upišete, na cijelu površinu kulture.</li>
                            <li>Gumb <strong>Kopiraj</strong> upisuje isti rad s današnjim datumom, npr. drugu košnju.</li>
                            <li><strong>Rad na više kultura odjednom</strong>, npr. oranje tri parcele u jednom danu: uz polje Kultura kliknite <strong>rad na više kultura</strong> i označite kulture (ili <strong>označi sve</strong>). Gorivo, radne sate i ukupnu cijenu upišite za cijeli posao, a AgroLog ih raspodijeli po hektarima i spremi zaseban zapis za svaku kulturu. Usluga po hektaru ostaje ista cijena po hektaru za svaku kulturu.</li>
                            <li><strong>Krivo upisan rad</strong> ispravite gumbom <strong>Uredi</strong> ili obrišite s ✕. Vlastita vrsta nestaje s popisa ponuđenih kad više nijedan zapis nema taj naziv.</li>
                            <li>Vožnja autom do parcele nije rad na parceli. Upisuje se pod <a href="#opci-troskovi" class="text-green-700 hover:underline">Opći troškovi</a>, a AgroLog vas na to upozori ako je upišete među radove.</li>
                        </ul>
                        <p>Trošak radova ulazi u stupac <strong>Radovi i ostalo</strong> na stranici <a href="#troskovi" class="text-green-700 hover:underline">Troškovi</a>, u €/ha kulture i u dobit.</p>
                    </div>
                </section>

                {{-- GORIVO PO DANU --}}
                <section id="gorivo" class="scroll-mt-24">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">★</div>
                        <h2 class="text-2xl font-bold text-gray-900">Gorivo po danu</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Koliko je goriva otišlo na pojedinu parcelu teško je znati, ali koliko ste natočili za cijeli dan rada znate. Zato se gorivo upisuje <strong>jednom za cijeli dan</strong>, a AgroLog ga sam podijeli na poslove tog dana po hektarima. Gorivo se vidi samo u <a href="#troskovi" class="text-green-700 hover:underline">Troškovima</a>, ne u evidenciji prskanja i gnojidbe ni u izvozu za Ministarstvo.</p>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Spremite prvo prskanje ili gnojidbu tog dana. Otvori se prozor <strong>⛽ Gorivo za dd.mm.gggg.</strong> s popisom svih poslova tog dana.</li>
                            <li>Upišite <strong>ukupno natočeno</strong> za taj dan u litrama i cijenu po litri (pamti se zadnja upisana). Možete upisati i samo iznos u eurima.</li>
                            <li>Uz svaki posao odmah piše koliko eura otpada na njega. Kliknite <strong>Spremi</strong>.</li>
                        </ol>
                        <ul class="list-disc pl-6 space-y-1">
                            <li><strong>Upisujete red po red?</strong> Prozor se otvara samo za prvi posao dana. Svaki sljedeći posao tog dana sam ulazi u raspodjelu i ništa ne trebate označavati, a AgroLog kratko javi „Uključeno u gorivo za …”.</li>
                            <li><strong>Ne upisujte gorivo uz svaku parcelu.</strong> Upisuje se jednom za dan, pa se isti trošak ne može zbrojiti dvaput.</li>
                            <li><strong>Unijet ću na kraju dana:</strong> prozor se taj dan više ne otvara, a na vrhu stranice ostaje podsjetnik <em>Gorivo za … još nije uneseno – Unesi</em>.</li>
                            <li><strong>Posao koji nije trošio ovo gorivo</strong> (npr. leđna prskalica) odznačite kvačicom u prozoru. To se pamti, a novi poslovi tog dana i dalje ulaze sami.</li>
                            <li>Više sredstava u istom prskanju iste parcele broji se kao jedan prolaz. <a href="#ostali-radovi" class="text-green-700 hover:underline">Ostali radovi</a> ulaze u raspodjelu samo ako uz njih niste već upisali gorivo.</li>
                        </ul>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Ispravak goriva</h3>
                        <p>Uz svaki red u evidenciji prskanja i gnojidbe je gumb <strong>⛽</strong>. Kad je gorivo za taj dan upisano, na njemu piše koliko, npr. <strong>⛽ 95 L</strong>. Kliknite ga, ispravite litre (npr. na 105) i spremite. Gorivo za dan možete otvoriti i u <a href="#kalendar" class="text-green-700 hover:underline">Kalendaru</a> (klik na dan → ⛽ Gorivo) te na Troškovima, gdje je popis <strong>Gorivo po danima</strong> s gumbom <strong>+ Gorivo za dan</strong> za naknadni upis. Uređivanje prskanja ili gnojidbe ne pita ponovno za gorivo.</p>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Gdje se vidi</h3>
                        <p>Na Troškovima u stupcu <strong>Gorivo</strong> po parceli i kulturi, u €/ha i u dobiti. Gorivo upisano za dan bez ijednog posla ulazi samo u sveukupni trošak.</p>
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800">
                            <strong>Ne pratite gorivo?</strong> U prozoru kliknite <strong>Ne pratim gorivo – ne prikazuj više</strong>. Uključiti ga možete kad god želite: <strong>Profil → Trošak goriva</strong>.
                        </div>
                    </div>
                </section>

                {{-- KALENDAR --}}
                <section id="kalendar" class="scroll-mt-24">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">★</div>
                        <h2 class="text-2xl font-bold text-gray-900">Kalendar: što je napravljeno i što slijedi</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>U izborniku <strong>Aktivnosti → Kalendar</strong> vidite cijeli mjesec na jednom mjestu. Ništa ne treba upisivati posebno: kalendar sam skuplja ono što je već u AgroLogu.</p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li><strong>Napravljeno:</strong> prskanja, gnojidbe, berbe i ostali radovi, svaka vrsta u svojoj boji, te servisi strojeva.</li>
                            <li><strong>Karence:</strong> narančasta crta na dnu dana znači da je kultura pod karencom i berba još nije dozvoljena. Na dan kad karenca istječe piše <em>Kraj karence: smije se brati</em>. Karenca se zna samo za sredstva iz registra FIS-a.</li>
                            <li><strong>Rokovi mehanizacije:</strong> registracija, tehnički, osiguranje i servis na datum isteka.</li>
                            <li><strong>Preporuke savjetnika</strong> koje još niste odradili stoje na svom roku; ako rok prođe, označene su crveno.</li>
                            <li><strong>Vrijeme:</strong> za idućih 7 dana uz datum je prognoza za područje vaših parcela. Zelena točkica znači da je dan povoljan za prskanje, crvena da nije.</li>
                        </ul>
                        <p>Gumbima iznad mreže uključujete i isključujete vrste, a izbornikom <strong>Sve parcele</strong> gledate samo jednu parcelu. Na mobitelu se umjesto mreže prikazuje popis dana.</p>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Klik na dan</h3>
                        <p>Klik na dan pokazuje sve što je toga dana napravljeno ili planirano. Za današnji ili raniji dan možete odmah <strong>upisati obavljeno</strong> (prskanje, gnojidbu, berbu ili rad): forma se otvori s tim datumom.</p>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Planiranje poslova</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Kliknite <strong>+ Planiraj posao</strong> ili dan pa <strong>+ Planiraj za …</strong></li>
                            <li>Odaberite vrstu posla, kulturu i po želji sredstvo, gnojivo ili vrstu rada</li>
                            <li>Kad posao obavite, kliknite <strong>Odrađeno, upiši</strong>: otvori se forma s kulturom i sredstvom iz plana. Provjerite podatke i spremite, a plan se sam zatvori</li>
                        </ol>
                        <p>Plan nije zapis u evidenciji i ne ide u izvještaje. Neodrađeni plan kojem je prošao datum označen je s <strong>kasni</strong>.</p>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Obavijesti</h3>
                        <p>Ako imate uključene obavijesti na uređaju, AgroLog ujutro javi što je danas planirano. Navečer, ako vas čeka prskanje (plan ili preporuka u iduća tri dana), javi je li sutra povoljan dan za prskanje.</p>
                    </div>
                </section>

                {{-- OPĆI TROŠKOVI --}}
                <section id="opci-troskovi" class="scroll-mt-24">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">★</div>
                        <h2 class="text-2xl font-bold text-gray-900">Opći troškovi i vožnja</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Neki troškovi nisu rad na parceli: vožnja do udaljene parcele, zakup zemlje, osiguranje usjeva, članarine, struja, knjigovodstvo. Upisuju se na dnu stranice <strong>Troškovi</strong>, pod <strong>Opći troškovi</strong>.</p>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Kliknite <strong>+ Dodaj trošak</strong>, odaberite vrstu ili upišite svoju</li>
                            <li>Upišite datum, iznos i po želji opis</li>
                            <li><strong>Kultura je neobavezna.</strong> Bez kulture trošak ulazi samo u ukupni trošak gospodarstva. Ako odaberete kulturu, ulazi i u njezin €/ha i dobit.</li>
                        </ol>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Vožnja</h3>
                        <ul class="list-disc pl-6 space-y-1">
                            <li>Kliknite <strong>+ Vožnja</strong>, upišite kamo ste išli i kilometre u jednom smjeru. Uz <strong>Tamo i natrag</strong> računa se dvostruko: 25 km postaje 50 km.</li>
                            <li>Iznos se računa sam, km × cijena po km. Prvi put se predlaže 0,50 €/km, a možete upisati svoju cijenu, npr. stvarni trošak goriva. AgroLog pamti zadnju upisanu.</li>
                            <li>Označite <strong>Zapamti kao rutu</strong> i sljedeći put samo kliknite rutu, npr. „Voćnjak Hrastin · 25 km”. Otvori se popunjena vožnja, a vi provjerite datum i spremite.</li>
                            <li>Imate li samo jedan voćnjak, vožnju vežite na njega i vidjet ćete točan trošak po hektaru. Ako istim putem idete na više kultura, ostavite <strong>cijelo gospodarstvo</strong>.</li>
                            <li>Krivo spremljenu rutu obrišite klikom na <strong>×</strong> uz njezin naziv. Upisane vožnje ostaju. Krivo upisanu vožnju ispravite s <strong>Uredi</strong> ili obrišite s ✕.</li>
                        </ul>
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
                            <strong>Za agronome i savjetnike:</strong> u svom računu (ne u klijentovom gospodarstvu) uz trošak ili vožnju odaberite <strong>Klijenta</strong>. Na dnu se vidi zbroj stavki, kilometara i iznosa po klijentu za godinu, korisno za obračun obilazaka.
                        </div>
                        <p class="text-sm">AgroLog samo zbraja ono što upišete i ne vodi službeni putni nalog ni porezni obračun naknade.</p>
                    </div>
                </section>

                {{-- MEHANIZACIJA (neobavezno) --}}
                <section id="mehanizacija">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">★</div>
                        <h2 class="text-2xl font-bold text-gray-900">Mehanizacija: rokovi i podsjetnici (neobavezno)</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Mehanizacija je u izborniku odmah iza Skladišta. Za traktore, priključke i vozila vodite registraciju, tehnički pregled, osiguranje, servis i pregled prskalice. AgroLog vas mailom podsjeti prije isteka. Dok ne dodate prvi stroj, ništa se ne prikazuje ni ne šalje.</p>
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
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800"><strong>Izvještaji:</strong> u izborniku <strong>Izvještaji</strong> birate razdoblje (ova godina, prošla, 12 mjeseci ili od–do) i jednim klikom dobijete ispis/PDF evidencije prskanja i gnojidbe, Excel za Ministarstvo te CSV gnojidbe i prometa skladišta. Na vrhu su ukupna sredstva, gnojiva i dušik za razdoblje.</div>
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
                            <li><a href="#gospodarstva" class="text-green-700 hover:underline">Prezentacija na AgroLogu</a>: po želji javno predstavljanje gospodarstva</li>
                            <li><strong>Trošak goriva</strong>: uključuje ili isključuje pitanje za <a href="#gorivo" class="text-green-700 hover:underline">gorivo po danu</a> nakon prskanja i gnojidbe</li>
                        </ul>
                        <p>U istom padajućem izborniku pod imenom su i ove <strong>Upute</strong>, a u profilu i <a href="#savjetnik" class="text-green-700 hover:underline">pristup savjetniku</a>.</p>

                        <p>O obavijestima mailom i na mobitel pročitajte u poglavlju <a href="#obavijesti" class="text-green-700 hover:underline">Push i mail obavijesti</a>. Sve novosti su i na stranici <a href="{{ route('novosti') }}" class="text-green-700 hover:underline font-medium">Novosti</a>.</p>
                    </div>
                </section>

                {{-- GOSPODARSTVO NA JAVNOM POPISU --}}
                <section id="gospodarstva" class="scroll-mt-24">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">★</div>
                        <h2 class="text-2xl font-bold text-gray-900">Gospodarstvo na javnom popisu</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Na stranici <a href="{{ route('gospodarstva') }}" class="text-green-700 hover:underline font-medium">Gospodarstva</a> kupci traže proizvođače po mjestu, županiji i onome što nude. Predstavljanje je besplatno i dobrovoljno.</p>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Otvorite <strong>Profil</strong> i pronađite sekciju <strong>Prezentacija na AgroLogu</strong>, odmah ispod podataka o gospodarstvu</li>
                            <li>Označite <strong>Želim da moje gospodarstvo bude na javnom popisu</strong>. Tek tada se pojave polja</li>
                            <li>Obavezno upišite naziv, mjesto i županiju, opis (barem 50 znakova), odaberite što nudite (do 8 oznaka), kontakt telefon i adresu</li>
                            <li>Na karti potražite adresu, a zatim <strong>kliknite točno na svoje dvorište</strong> ili mjesto prodaje. Pin možete povući. Pretraga samo približi kartu, a lokaciju postavljate vi</li>
                            <li>Po želji dodajte sliku, e-mail, WhatsApp ili Viber, prodaju na kućnom pragu s vremenom kada se može doći te poveznice na web, Facebook i Instagram</li>
                            <li>Kliknite <strong>Spremi prezentaciju</strong></li>
                        </ol>
                        <ul class="list-disc pl-6 space-y-1">
                            <li><strong>Prva objava ide na pregled.</strong> Kad je odobrimo, javimo vam mailom. Ako nešto treba doraditi, u mailu i u profilu piše razlog. Izmjene već objavljene prezentacije vidljive su odmah.</li>
                            <li><strong>Broj telefona</strong> nije ispisan na stranici, nego se prikaže klikom na <strong>Prikaži broj</strong>. Tako ga roboti teže skupljaju.</li>
                            <li><strong>Na karti</strong> se gospodarstvo prikazuje točno ondje gdje ste postavili pin, a kupci uz njega dobiju poveznicu <strong>Upute za dolazak</strong>.</li>
                            <li>Ako u zadnjih godinu dana vodite evidenciju prskanja, gnojidbe ili berbe, uz prezentaciju piše <strong>Vodi evidenciju u AgroLogu</strong>. Sama evidencija nikad nije javna.</li>
                            <li>Kad maknete kvačicu i spremite, gospodarstvo odmah nestaje s popisa, a podaci ostaju za kasnije. Gumb <strong>Obriši prezentaciju</strong> briše sve, i sliku.</li>
                        </ul>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-900">
                            Sve što upišete u prezentaciju je <strong>javno</strong>. Objavite samo svoje fotografije i podatke koje želite da kupci vide.
                        </div>
                    </div>
                </section>

                {{-- TRŽNICA --}}
                <section id="trznica" class="scroll-mt-24">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">★</div>
                        <h2 class="text-2xl font-bold text-gray-900">Tržnica: oglasi za proizvode</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Na <a href="{{ route('trznica') }}" class="text-green-700 hover:underline font-medium">Tržnici</a> gospodarstva besplatno oglašavaju svoje proizvode s cijenom. Kupac klikne oglas i vidi tko prodaje, sve kontakte i gumb <strong>Svi oglasi ovog prodavača</strong>. Dogovor ide izravno s gospodarstvom, bez košarice i plaćanja preko AgroLoga.</p>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Prvo uključite <a href="#gospodarstva" class="text-green-700 hover:underline">prezentaciju gospodarstva</a> u profilu. Oglas uvijek pokazuje prodavača, pa bez nje nema oglasa</li>
                            <li>U izborniku otvorite <strong>Tržnica → Moji proizvodi</strong></li>
                            <li>Upišite naziv, vrstu, cijenu i jedinicu (€/kg, €/L, €/kom, €/staklenka…), a po želji opis i sliku, pa kliknite <strong>Dodaj proizvod</strong></li>
                        </ol>
                        <ul class="list-disc pl-6 space-y-1">
                            <li>Proizvodi se na Tržnici pojave kad je prezentacija odobrena. Dodati ih možete i dok čeka odobrenje.</li>
                            <li><strong>Sakrij / Prikaži</strong> gasi proizvod izvan sezone bez brisanja. Uz cijenu piše kada je zadnji put ažurirana.</li>
                            <li>Proizvodi se vide i na stranici vašeg gospodarstva. Gospodarstvo može imati do 40 proizvoda.</li>
                        </ul>
                        <p>Na registraciji možete odmah označiti <strong>Želim besplatno predstaviti gospodarstvo</strong>. Nakon registracije otvori se obrazac za prezentaciju, a možete ga ispuniti i kasnije, iz podsjetnika na početnoj ili u profilu.</p>
                    </div>
                </section>

                {{-- PUSH I MAIL OBAVIJESTI --}}
                <section id="obavijesti">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">★</div>
                        <h2 class="text-2xl font-bold text-gray-900">Push i mail obavijesti</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>U <strong>Profil → Obavijesti</strong> su dvije oznake, <strong>Push obavijesti</strong> i <strong>Mail obavijesti</strong>. Obje su zadano uključene, a svaku možete ugasiti posebno.</p>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border border-gray-200 rounded-lg">
                                <thead class="bg-gray-50 text-gray-700"><tr><th class="text-left px-3 py-2">Obavijest</th><th class="px-3 py-2">Push</th><th class="px-3 py-2">Mail</th></tr></thead>
                                <tbody class="divide-y">
                                    <tr><td class="px-3 py-2">Rokovi mehanizacije: prije isteka i kad rok prođe</td><td class="px-3 py-2 text-center">da</td><td class="px-3 py-2 text-center">da</td></tr>
                                    <tr><td class="px-3 py-2">Novosti u AgroLogu i promjene propisa</td><td class="px-3 py-2 text-center">da</td><td class="px-3 py-2 text-center">da</td></tr>
                                    <tr><td class="px-3 py-2">Nova karenca nakon upisanog prskanja</td><td class="px-3 py-2 text-center">da</td><td class="px-3 py-2 text-center">ne</td></tr>
                                    <tr><td class="px-3 py-2">Sredstvo ili gnojivo pri kraju ili u minusu na skladištu</td><td class="px-3 py-2 text-center">da</td><td class="px-3 py-2 text-center">ne</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p>Provjera ide jednom dnevno, ujutro, a ista se stavka ne javlja svaki dan. Sve te stavke vidite i u <strong>zvonu</strong> uz svoje ime. Poruke o računu, npr. promjena lozinke ili poziv savjetnika, stižu uvijek i ne mogu se ugasiti.</p>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Uključivanje pusha na uređaju</h3>
                        <p>Oznaka u profilu nije dovoljna: preglednik na svakom uređaju traži vaše dopuštenje. Zato jednom kliknite <strong>Uključi</strong> na traci u aplikaciji, ili <strong>Uključi na ovom uređaju</strong> u profilu, i dopustite obavijesti.</p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li><strong>Android:</strong> radi u Chromeu. Aplikaciju možete, ali ne morate dodati na početni zaslon.</li>
                            <li><strong>iPhone:</strong> potreban je iOS 16.4 ili noviji. Najprije u Safariju odaberite <strong>Dijeli → Dodaj na početni zaslon</strong>, otvorite AgroLog s te ikone i tek onda uključite obavijesti.</li>
                            <li><strong>Računalo:</strong> radi u pregledniku koji podržava push obavijesti, npr. Chromeu, Edgeu ili Firefoxu.</li>
                        </ul>
                        <p>Gumbom <strong>Pošalji probnu obavijest</strong> u profilu provjerite stiže li obavijest. Uključiti možete više uređaja. Kad se odjavite s uređaja, on prestaje primati obavijesti, a nakon nove prijave uključuju se same.</p>
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
                            <strong>Ne stiže ništa?</strong> Provjerite je li uključena oznaka u profilu i nisu li obavijesti za agro-log.app blokirane u postavkama preglednika ili mobitela. Ako ste ih blokirali, dopustiti ih možete samo u tim postavkama.
                        </div>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Gašenje</h3>
                        <p>Maknite oznaku u profilu. Mailove s novostima možete ugasiti i poveznicom <strong>Odjavite se jednim klikom</strong> u dnu svakog takvog maila, što gasi i podsjetnike mailom. Gašenje ne utječe na vaš račun ni na evidencije.</p>
                    </div>
                </section>

                {{-- PRISTUP SAVJETNIKU --}}
                <section id="savjetnik">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">★</div>
                        <h2 class="text-2xl font-bold text-gray-900">Pristup savjetniku</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <h3 class="font-semibold text-gray-800 text-lg">Za gospodarstvo: pozovite savjetnika</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Otvorite <strong>Profil → Pristup savjetniku</strong></li>
                            <li>Upišite e-mail savjetnika (agronom, savjetodavna služba, zadruga) i odaberite <strong>samo pregled</strong> ili <strong>pregled i unos</strong></li>
                            <li>Savjetnik dobije mail s poveznicom, prijavi se ili besplatno registrira i prihvati poziv. Vi dobijete potvrdu mailom</li>
                        </ol>
                        <p>Svaku izmjenu savjetnika vidite u <strong>povijesti izmjena</strong> u profilu (tko, kada, što). Prava možete promijeniti, poziv poslati ponovno ili pristup <strong>ukinuti</strong> jednim klikom — vrijedi odmah. Savjetnik ne vidi vaš profil ni lozinku.</p>
                        <h3 id="savjetnik-poziv" class="font-semibold text-gray-800 text-lg mt-6">Za savjetnika: pozovite svoje poljoprivrednike</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Na registraciji označite <strong>Agronom sam ili savjetnik</strong>. Ako već imate račun, označite isto u <strong>Profilu</strong></li>
                            <li>Otvorite <strong>Klijenti</strong>, upišite e-mail poljoprivrednika i kliknite <strong>Pošalji poziv</strong></li>
                            <li>Poljoprivrednik dobije mail. Ako nema račun, registrira se pa se vraća na poziv</li>
                            <li>On bira <strong>samo pregled</strong> ili <strong>pregled i unos</strong> i prihvaća poziv. Gospodarstvo se tada pojavi na vašem popisu, a vi dobijete potvrdu mailom</li>
                        </ol>
                        <p><strong>Dok poljoprivrednik ne prihvati, ne vidite ništa od njegovih podataka</strong> i ne možete unositi za njega. Isti poziv možete poslati ponovno jednom dnevno, najviše tri puta, a ukupno možete poslati do 20 mailova s pozivima na dan. Ako poljoprivrednik odbije poziv, ne možete mu poslati novi. Pozivajte samo ljude s kojima surađujete.</p>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Za gospodarstvo: dobili ste poziv od savjetnika</h3>
                        <p>Otvorite poveznicu iz maila i provjerite tko vas poziva. Prihvatite samo ako tu osobu poznajete. Prava birate sami, a kasnije ih možete promijeniti ili pristup ukinuti u <strong>Profil → Pristup savjetniku</strong>. Ako ne želite suradnju, odaberite <strong>Ne želim, odbij poziv</strong>. Za odbijanje vam ne treba račun.</p>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Za savjetnika: Klijenti</h3>
                        <p>U padajućem izborniku pod imenom je <strong>Klijenti</strong> — popis gospodarstava koja su vam dala pristup, s površinom, zadnjim unosom i onim što traži pažnju. <strong>Otvori</strong> vas prebaci u gospodarstvo klijenta (na vrhu je traka „Radite u gospodarstvu …”), a <strong>Natrag na svoje</strong> vraća u vaše. Uz „samo pregled” sve vidite i ispisujete, ali ne možete mijenjati.</p>
                    </div>
                </section>

                {{-- PREPORUKE --}}
                <section id="preporuke" class="scroll-mt-24">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">★</div>
                        <h2 class="text-2xl font-bold text-gray-900">Preporuke savjetnika</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>Savjetnik može gospodarstvu poslati preporuku za prskanje ili gnojidbu, ili opću poruku. Poljoprivrednik sam odlučuje što će napraviti.</p>
                        <h3 class="font-semibold text-gray-800 text-lg">Za savjetnika: slanje</h3>
                        <ol class="list-decimal pl-6 space-y-1">
                            <li>Otvorite <strong>Klijenti → Pošalji preporuku</strong> i odaberite prskanje, gnojidbu ili opću poruku</li>
                            <li>Odaberite <strong>kulturu</strong>. Na popisu su parcele svih klijenata s tom kulturom i sve su označene, a možete maknuti one koje ne želite. Gospodarstva bez te kulture ne dobivaju preporuku</li>
                            <li>Upišite sredstvo ili gnojivo, a po želji dozu, rok i napomenu. Opća poruka nema kulturu i može ići svim klijentima</li>
                            <li>Kliknite <strong>Pregledaj prije slanja</strong>. Za prskanje AgroLog javlja što kaže registar FIS-a, npr. ako sredstvo nije registrirano za tu kulturu</li>
                            <li>Kliknite <strong>Pošalji</strong>. Stanje svake preporuke vidite na istoj stranici, a na pitanja možete odgovoriti izravno tamo</li>
                        </ol>
                        <p>Preporuke možete slati i sa „samo pregled” jer time ništa ne mijenjate u evidenciji.</p>
                        <h3 class="font-semibold text-gray-800 text-lg mt-6">Za gospodarstvo: odgovor</h3>
                        <p>Obavijest stiže u <strong>zvono</strong>, a prema vašim postavkama i mailom i pushem. Preporuke su u izborniku pod imenom, pod <strong>Preporuke savjetnika</strong>. Možete odabrati <strong>Prihvaćam</strong>, <strong>Ne prihvaćam</strong> ili <strong>Imam pitanje</strong>.</p>
                        <p>Prihvaćanje ne upisuje ništa u evidenciju. Kad posao stvarno obavite, kliknite <strong>Napravljeno, unesi u evidenciju</strong>. Otvara se popunjen unos, a vi provjerite datum, količinu i ostalo pa spremite. Ako zapis već imate, odaberite <strong>Već je upisano</strong>, a ako posao ipak niste obavili, <strong>Nije napravljeno</strong>.</p>
                    </div>
                </section>

                {{-- MOBITEL --}}
                <section id="mobitel">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-8 h-8 bg-green-600 text-white rounded-lg flex items-center justify-center font-bold text-sm flex-shrink-0">★</div>
                        <h2 class="text-2xl font-bold text-gray-900">AgroLog na mobitelu</h2>
                    </div>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>AgroLog možete dodati na početni zaslon mobitela i otvarati ga kao aplikaciju:</p>
                        <ul class="list-disc pl-6 space-y-1">
                            <li><strong>Android (Chrome):</strong> kliknite <strong>Instaliraj aplikaciju</strong> kad se ponudi, ili u izborniku preglednika ⋮ → <strong>Dodaj na početni zaslon</strong>.</li>
                            <li><strong>iPhone (Safari):</strong> gumb <strong>Dijeli</strong> → <strong>Dodaj na početni zaslon</strong>.</li>
                        </ul>
                        <p>Za rad je potreban internet. Bez veze AgroLog pokaže poruku da pokušate ponovno kad se veza vrati.</p>
                    </div>
                </section>

                {{-- 9. ČESTA PITANJA --}}
                <section id="pitanja">
                    {!! $naslov(9, 'Česta pitanja') !!}
                    <div class="space-y-3">
                        @foreach ([
                            ['Je li AgroLog stvarno besplatan?', 'Da. Nema pretplate, kreditne kartice ni skrivenih troškova.'],
                            ['Nemam ARKOD. Mogu li ipak koristiti AgroLog?', 'Možete. Kod nove parcele kliknite „Nemam ARKOD, ucrtaj na karti”, upišite naziv i kliknite uglove parcele na karti. Površina se izračuna sama, a dalje parcelu koristite kao i svaku drugu.'],
                            ['ARKOD broj nije pronađen — što sad?', 'Provjerite broj u ARKOD pregledniku (preglednik.arkod.hr). Ako je ispravan, a servis Agencije trenutno ne odgovara, parcelu spremite bez karte i kasnije kliknite ikonu karte uz nju — oblik će se dohvatiti kad servis proradi.'],
                            ['Zašto je moja površina drukčija od ARKOD-a?', 'AgroLog uzima površinu iz javnog ARKOD sloja Agencije za plaćanja. Ako ste upisali više, uz parcelu piše ARKOD površina — dobro je provjeriti prije prijave potpora.'],
                            ['Sredstvo koje koristim nije u registru?', 'Provjerite točan naziv s etikete (npr. „FOLICUR EW 250”). Ako ga ipak nema, upišite naziv i registracijski broj ručno — zapis se sprema normalno.'],
                            ['Što je N-P-K kod gnojiva i moram li ga upisivati?', 'N-P-K su postoci dušika, fosfora i kalija u gnojivu (npr. NPK 15-15-15 = po 15 %). Kad gnojivo odaberete s popisa, udjeli se upišu sami. Ručno ih upisujete samo za gnojivo kojeg nema na popisu — tri broja s vreće. Ako ih ne znate, ostavite prazno; tada samo nema izračuna dušika.'],
                            ['Moram li upisati fenofazu (BBCH)?', 'Ministarstvov format je traži „kad je relevantna za kulturu i primjenu”. Preporučujemo da je upišete — za odabranu kulturu nude se faze s Ministarstvova popisa.'],
                            ['Što s evidencijom iz 2026.?', 'Primjene do 31. 12. 2026. ne moraju se prenositi u novi format, ali i za njih možete preuzeti Excel (Ministarstvo). AgroLog je već upisao vrstu uporabe „na otvorenom” i neekološku površinu za postojeće zapise — promijenite ih gdje treba.'],
                            ['Mogu li koristiti AgroLog na mobitelu?', 'Da, stranice se prilagođavaju ekranu mobitela i tableta, a AgroLog možete dodati i na početni zaslon kao aplikaciju. Za veće tablice evidencije praktičnije je računalo ili tablet položeno.'],
                            ['Zašto mi za novu kulturu ne nudi cijelu površinu parcele?', 'Nova kultura dobije samo površinu koja je na parceli još slobodna. Ako je parcela već cijela posađena, kulturu ne možete dodati dok jednu ne završite ili joj ne smanjite površinu.'],
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

    @include('partials.tawk')
</body>
</html>
