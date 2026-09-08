<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Politika privatnosti – AgroLog</title>
    <meta name="description" content="Politika privatnosti AgroLog aplikacije. Saznajte kako prikupljamo, koristimo i štitimo vaše osobne podatke.">
    <link rel="canonical" href="{{ route('privacy') }}">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('privacy') }}">
    <meta property="og:title" content="Politika privatnosti – AgroLog">
    <meta property="og:description" content="Politika privatnosti AgroLog aplikacije. Saznajte kako prikupljamo, koristimo i štitimo vaše osobne podatke.">
    <meta property="og:locale" content="hr_HR">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-800 bg-white">

    {{-- NAV --}}
    @include('partials.nav')

    <main class="max-w-3xl mx-auto px-6 py-16">

        <h1 class="text-3xl font-bold text-gray-900 mb-2">Politika privatnosti</h1>
        <p class="text-sm text-gray-400 mb-10">Zadnja izmjena: {{ date('d.m.Y') }}</p>

        <div class="prose prose-gray max-w-none space-y-8 text-gray-600 leading-relaxed">

            <section>
                <h2 class="text-lg font-semibold text-gray-800 mb-2">1. Uvod</h2>
                <p>
                    AgroLog ("mi", "naša aplikacija") posvećen je zaštiti osobnih podataka korisnika. Ovom politikom privatnosti pojašnjavamo koje podatke prikupljamo, kako ih koristimo i na koji način ih štitimo, u skladu s Uredbom (EU) 2016/679 (GDPR) i važećim zakonodavstvom Republike Hrvatske.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-800 mb-2">2. Voditelj obrade</h2>
                <p>
                    Voditelj obrade osobnih podataka je Alan Fadljević, dostupan putem e-mail adrese:
                    <a href="mailto:info@agro-log.app" class="text-green-700 hover:underline font-medium">info@agro-log.app</a>.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-800 mb-2">3. Koje podatke prikupljamo</h2>
                <ul class="list-disc pl-5 space-y-1">
                    <li><strong>Podaci o računu:</strong> ime, e-mail adresa, lozinka (pohranjena u hashiranom obliku), naziv gospodarstva, MIPG broj i OIB.</li>
                    <li><strong>Podaci o parcelama:</strong> ARKOD brojevi, površine i kulture koje unosite.</li>
                    <li><strong>Evidencija radova:</strong> zapisi o gnojidbi i prskanju koje unosite u aplikaciju.</li>
                    <li><strong>Tehnički podaci:</strong> IP adresa, vrsta preglednika i podaci o sesiji, koji se automatski bilježe radi sigurnosti i stabilnosti usluge.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-800 mb-2">4. Svrha i pravna osnova obrade</h2>
                <ul class="list-disc pl-5 space-y-1">
                    <li><strong>Pružanje usluge:</strong> obrada je nužna za izvršenje ugovora (čl. 6. st. 1. b) GDPR-a) — bez ovih podataka aplikacija ne može funkcionirati.</li>
                    <li><strong>Sigurnost i prevencija zlouporabe:</strong> legitimni interes voditelja obrade (čl. 6. st. 1. f) GDPR-a).</li>
                    <li><strong>Zakonske obveze:</strong> ako nas na to obvezuje propis ili tijelo javne vlasti (čl. 6. st. 1. c) GDPR-a).</li>
                </ul>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-800 mb-2">5. Dijeljenje podataka s trećim stranama</h2>
                <p>
                    Vaše podatke ne prodajemo niti ustupamo trećim stranama u komercijalne svrhe. Podaci se mogu proslijediti isključivo:
                </p>
                <ul class="list-disc pl-5 space-y-1 mt-2">
                    <li>pružateljima infrastrukture (hosting, poslužitelji) koji obrađuju podatke isključivo prema našim uputama i u svrhu pružanja usluge,</li>
                    <li>nadležnim tijelima, ako to zahtijeva zakon.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-800 mb-2">6. Pohrana i sigurnost podataka</h2>
                <p>
                    Podaci se pohranjuju na sigurnim poslužiteljima unutar Europskog gospodarskog prostora. Primjenjujemo tehničke i organizacijske mjere zaštite: enkripciju lozinki, HTTPS komunikaciju i ograničen pristup podacima. Unatoč tome, nijedan sustav nije 100% siguran pa vas molimo da sami zaštitite pristupne podatke svog računa.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-800 mb-2">7. Rok čuvanja podataka</h2>
                <p>
                    Podatke čuvamo dok je vaš korisnički račun aktivan. Nakon brisanja računa, osobni podaci se trajno brišu u roku od 30 dana, osim ako zakon ne nalaže dulje čuvanje.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-800 mb-2">8. Vaša prava</h2>
                <p>U skladu s GDPR-om imate pravo:</p>
                <ul class="list-disc pl-5 space-y-1 mt-2">
                    <li><strong>Pristupa</strong> — zatražiti uvid u podatke koje obrađujemo o vama,</li>
                    <li><strong>Ispravka</strong> — zahtijevati ispravak netočnih podataka,</li>
                    <li><strong>Brisanja</strong> — zatražiti brisanje podataka ("pravo na zaborav"),</li>
                    <li><strong>Ograničenja obrade</strong> — privremeno ograničiti obradu vaših podataka,</li>
                    <li><strong>Prenosivosti</strong> — primiti kopiju podataka u strojno čitljivom obliku,</li>
                    <li><strong>Prigovora</strong> — prigovoriti obradi temeljnoj na legitimnom interesu.</li>
                </ul>
                <p class="mt-3">
                    Zahtjeve možete poslati na: <a href="mailto:info@agro-log.app" class="text-green-700 hover:underline font-medium">info@agro-log.app</a>. Odgovorit ćemo u roku od 30 dana. Imate i pravo podnijeti pritužbu Agenciji za zaštitu osobnih podataka (AZOP).
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-800 mb-2">9. Kolačići (Cookies)</h2>
                <p>
                    AgroLog koristi isključivo tehničke kolačiće nužne za rad aplikacije (autentifikacija, CSRF zaštita). Ne koristimo kolačiće za praćenje ili oglašavanje.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-800 mb-2">10. Izmjene politike privatnosti</h2>
                <p>
                    Zadržavamo pravo izmjene ove politike. O značajnim promjenama obavijestit ćemo vas putem e-maila ili obavijesti u aplikaciji. Nastavak korištenja aplikacije nakon izmjena smatra se prihvaćanjem nove politike.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-800 mb-2">11. Kontakt</h2>
                <p>
                    Za sva pitanja vezana uz zaštitu osobnih podataka obratite se na:
                    <a href="mailto:info@agro-log.app" class="text-green-700 hover:underline font-medium">info@agro-log.app</a>
                </p>
            </section>

        </div>

        <div class="mt-12 pt-8 border-t border-gray-100">
            <a href="/" class="text-sm text-green-700 hover:underline">&larr; Natrag na početnu</a>
        </div>

    </main>

    {{-- FOOTER --}}
    @include('partials.footer')

</body>
</html>
