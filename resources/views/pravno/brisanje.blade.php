@extends('layouts.pravno')
@section('naslov', 'Brisanje računa')
@section('opis', 'Kako obrisati svoj AgroLog račun, što se pritom briše i što napraviti ako mu više nemate pristup.')

@section('sadrzaj')
    @php
        $n = 'text-xl font-semibold text-gray-800 mb-3';
        $lista = 'list-disc pl-6 space-y-2';
        $a = 'text-green-700 hover:underline';
    @endphp

    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        <strong>Brisanje je trajno i ne može se poništiti.</strong> Propisi traže da evidenciju čuvate, za sredstva za zaštitu bilja najmanje tri godine. Zato je prije brisanja preuzmite pod <strong>Izvještaji</strong> (PDF, Excel, CSV).
    </div>

    <section>
        <h2 class="{{ $n }}">Kako obrisati račun</h2>
        <ol class="list-decimal pl-6 space-y-2">
            <li><a href="{{ route('login') }}" class="{{ $a }}">Prijavite se</a> u AgroLog.</li>
            <li>Kliknite svoje ime u gornjem desnom kutu pa <strong>Profil</strong>.</li>
            <li>Na dnu stranice, pod <strong>Brisanje računa</strong>, kliknite <strong>Obriši račun</strong>.</li>
            <li>Potvrdite lozinkom. Ako ste se registrirali Google računom, potvrdite upisom svoje e-mail adrese.</li>
        </ol>
    </section>

    <section>
        <h2 class="{{ $n }}">Nemate pristup računu?</h2>
        <p>Prvo pokušajte s <a href="{{ route('password.request') }}" class="{{ $a }}">Zaboravljena lozinka</a>. Ako ni to ne uspije, pišite s adrese na kojoj je račun otvoren na <a href="mailto:info@agro-log.app" class="{{ $a }}">info@agro-log.app</a>. Radi vaše sigurnosti možemo tražiti dodatnu potvrdu da je račun vaš, a zahtjev ćemo riješiti u roku od mjesec dana.</p>
    </section>

    <section>
        <h2 class="{{ $n }}">Što se briše</h2>
        <ul class="{{ $lista }}">
            <li>podaci računa i profila,</li>
            <li>parcele, kulture i nasadi te evidencija prskanja, gnojidbe i berbe,</li>
            <li>skladište, mehanizacija, rokovi, servisi, prinosi i troškovi,</li>
            <li>pristupi koje ste dali savjetnicima i koje su drugi dali vama,</li>
            <li>pretplate uređaja na push obavijesti.</li>
        </ul>
        <p class="mt-3">Podaci se iz aplikacije brišu odmah. Preostali tragovi, npr. u sigurnosnim kopijama i zapisima poslužitelja, nestaju najkasnije u roku od 30 dana.</p>
    </section>

    <section>
        <h2 class="{{ $n }}">Što ostaje</h2>
        <ul class="{{ $lista }}">
            <li>Podaci koje smo dužni čuvati po zakonu, samo koliko to zakon traži.</li>
            <li>Ako ste bili savjetnik drugom gospodarstvu, u njegovoj povijesti izmjena ostaje vaše ime uz izmjene koje ste napravili.</li>
            <li>Mailovi koje ste s nama razmijenili, dok su potrebni za rješavanje upita.</li>
            <li>Evidencija koju ste ranije preuzeli na svoj uređaj, jer je pod vašom kontrolom.</li>
        </ul>
        <p class="mt-3">Uklanjanje ikone AgroLoga s mobitela ne briše račun. Više u <a href="{{ route('privacy') }}" class="{{ $a }}">Pravilima privatnosti</a>.</p>
    </section>
@endsection
