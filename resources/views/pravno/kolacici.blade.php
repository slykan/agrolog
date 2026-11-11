@extends('layouts.pravno')
@section('naslov', 'Pravila o kolačićima')
@section('opis', 'Koje kolačiće i lokalnu pohranu koristi AgroLog, čemu služe i kako promijeniti svoj odabir.')

@section('sadrzaj')
    @php
        $n = 'text-xl font-semibold text-gray-800 mb-3';
        $th = 'text-left px-3 py-2 font-semibold text-gray-700';
        $td = 'px-3 py-2 align-top';
    @endphp

    <section>
        <h2 class="{{ $n }}">1. Što su kolačići</h2>
        <p>Kolačići su male datoteke koje stranica sprema u vaš preglednik. Slično služi i lokalna pohrana preglednika. Nužni se koriste bez privole, jer bez njih stranica ne radi. Za ostale tražimo vašu privolu.</p>
    </section>

    <section>
        <h2 class="{{ $n }}">2. Nužni kolačići</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border border-gray-200">
                <thead class="bg-gray-50"><tr><th class="{{ $th }}">Naziv</th><th class="{{ $th }}">Svrha</th><th class="{{ $th }}">Trajanje</th></tr></thead>
                <tbody class="divide-y divide-gray-200">
                    <tr><td class="{{ $td }}">agrolog-session</td><td class="{{ $td }}">održava prijavu i sesiju</td><td class="{{ $td }}">2 sata od zadnje aktivnosti</td></tr>
                    <tr><td class="{{ $td }}">XSRF-TOKEN</td><td class="{{ $td }}">zaštita obrazaca od zlouporabe</td><td class="{{ $td }}">2 sata od zadnje aktivnosti</td></tr>
                    <tr><td class="{{ $td }}">remember_web_…</td><td class="{{ $td }}">„zapamti me” pri prijavi</td><td class="{{ $td }}">do odjave, najdulje koliko preglednik dopušta</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <section>
        <h2 class="{{ $n }}">3. Lokalna pohrana preglednika</h2>
        <p class="mb-3">Ovo ostaje samo na vašem uređaju i ne šalje se nama:</p>
        <ul class="list-disc pl-6 space-y-2">
            <li><strong>cookie_consent:</strong> vaš odabir o kolačićima,</li>
            <li><strong>agrolog-novost-…, agrolog-zvono, agrolog-instalacija-skriveno, agrolog-push-skriveno:</strong> pamti što ste zatvorili, pročitali ili odgodili u aplikaciji,</li>
            <li><strong>agro-push-…:</strong> da se pretplata uređaja na obavijesti ne provjerava pri svakom otvaranju stranice,</li>
            <li><strong>spremljena stranica „Nema veze”:</strong> prikazuje se kad nema interneta. Vaša evidencija se ne sprema na uređaj.</li>
        </ul>
    </section>

    <section>
        <h2 class="{{ $n }}">4. Oglasni kolačići (samo uz vašu privolu)</h2>
        <p>Na javnim stranicama s vodičima, alatima i novostima mogu se prikazivati Googleovi oglasi (Google AdSense). Googleova skripta učitava se <strong>samo ako kliknete Prihvaćam</strong>. Tada Google i njegovi partneri mogu koristiti kolačiće za prikaz i mjerenje oglasa. Ako kliknete Odbijam, skripta se ne učitava. Googleovih oglasa nema unutar aplikacije nakon prijave.</p>
        <p class="mt-3">Više o tome kako Google koristi podatke: <a href="https://policies.google.com/technologies/partner-sites?hl=hr" target="_blank" rel="noopener" class="text-green-700 hover:underline">policies.google.com</a>.</p>
    </section>

    <section>
        <h2 class="{{ $n }}">5. Naši oglasi</h2>
        <p>Na naslovnoj stranici, početnoj stranici aplikacije te vodičima i novostima prikazujemo i oglasne bannere koje sami objavljujemo, označene s „Oglas”. Oni ne koriste kolačiće za praćenje i nije potrebna privola. Brojimo samo koliko su puta prikazani i kliknuti, zbirno po danu i mjestu na stranici, bez podataka o tome tko ih je vidio. Klik na oglas može vas odvesti na stranicu oglašivača, na koju se primjenjuju njegova pravila.</p>
    </section>

    <section>
        <h2 class="{{ $n }}">6. Vanjski servisi</h2>
        <p>Karta, prognoza, fontovi i prijava Googleom dohvaćaju se od vanjskih pružatelja, koji pritom vide vašu IP adresu. Chat uživo u kutu stranice pruža Tawk.to, koji u pregledniku sprema svoje kolačiće i lokalnu pohranu (npr. twk_uuid, TawkConnectionTime) kako bi razgovor ostao povezan i kako bismo vidjeli da ste na stranici. Vidi <a href="{{ route('privacy') }}" class="text-green-700 hover:underline">Pravila privatnosti</a>.</p>
    </section>

    <section>
        <h2 class="{{ $n }}">7. Kako promijeniti odabir</h2>
        <p class="mb-3">Odabir možete promijeniti u svakom trenutku, a povlačenje privole ne utječe na ranije korištenje:</p>
        <button type="button" onclick="window.dispatchEvent(new CustomEvent('agro-kolacici'))" class="inline-flex rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Postavke kolačića</button>
        <p class="mt-3">Već spremljene kolačiće možete obrisati u postavkama preglednika. Ako obrišete nužne kolačiće, bit ćete odjavljeni.</p>
    </section>
@endsection
