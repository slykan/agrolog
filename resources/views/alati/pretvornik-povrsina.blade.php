@extends('layouts.public', [
    'title' => 'Pretvornik površina — ha, ar, m², jutro, čhv',
    'description' => 'Preračunaj hektare, are, kvadratne metre, katastarska jutra i četvorne hvate. 1 katastarsko jutro = 5.754,64 m² = 1.600 čhv.',
    'active' => 'alati',
    'ads' => true,
])

@section('content')
    <a href="{{ route('alati') }}" class="text-sm text-gray-500 hover:text-green-700 no-print">← Svi alati</a>
    <h1 class="text-4xl font-bold text-gray-900 mt-3 mb-3">📐 Pretvornik površina</h1>
    <p class="text-lg text-gray-500 mb-8">Upiši površinu u bilo kojoj jedinici — ostale se preračunaju same. Korisno kad su papiri u jutrima i hvatima, a ARKOD u hektarima.</p>

    <div class="border border-gray-200 rounded-2xl p-6">
        <div class="grid sm:grid-cols-2 gap-5" id="polja"></div>
    </div>

    <div class="prose-agro mt-10">
        <h2>Jedinice i odnosi</h2>
        <table class="tbl">
            <tr><th>Jedinica</th><th class="num">u m²</th></tr>
            <tr><td>hektar (ha)</td><td class="num">10.000</td></tr>
            <tr><td>ar (a)</td><td class="num">100</td></tr>
            <tr><td>katastarsko jutro (k.j.) = 1.600 čhv</td><td class="num">5.754,64</td></tr>
            <tr><td>četvorni hvat (čhv)</td><td class="num">3,5966</td></tr>
        </table>
        <p>
            Katastarsko jutro i četvorni hvat stare su austro-ugarske mjere koje se i danas nalaze u starim zemljišnim
            knjigama i kupoprodajnim ugovorima. Jedno jutro je nešto više od pola hektara (0,575 ha), a hektar ima
            oko 1,74 jutra ili 2.780 hvati.
        </p>
        <div class="box-note">ARKOD i potpore uvijek se iskazuju u hektarima — stare mjere preračunaj prije unosa.</div>

        <h2>Primjeri preračuna</h2>
        <table class="tbl">
            <tr><th>Imaš</th><th>To je</th></tr>
            <tr><td>3 katastarska jutra</td><td>17.263,92 m² = 1,7264 ha</td></tr>
            <tr><td>850 čhv (vrt ili okućnica)</td><td>3.057 m² ≈ 30,6 ara</td></tr>
            <tr><td>2 jutra i 400 čhv</td><td>3.600 čhv = 12.948 m² ≈ 1,29 ha</td></tr>
            <tr><td>2,5 ha</td><td>25.000 m² ≈ 4,34 jutra</td></tr>
            <tr><td>75 ari</td><td>7.500 m² ≈ 2.085 čhv</td></tr>
        </table>
        <p>
            U starim ispravama površina se često piše kao „jutra i hvati", npr. „2 jutra 400 čhv". Jutro ima točno
            1.600 čhv, pa jutra najprije pretvori u hvate (2 × 1.600 = 3.200), dodaj ostatak (3.200 + 400 = 3.600 čhv) i
            onda preračunaj u m² ili hektare.
        </p>

        <h2>Kako se računa</h2>
        <p>
            Sve jedinice se preračunavaju preko kvadratnog metra. Četvorni hvat je kvadrat stranice jednog bečkog
            hvata (≈ 1,8965 m), pa ima oko 3,5966 m², a jutro je 1.600 takvih hvati. Hektar je kvadrat stranice 100 m,
            a ar kvadrat stranice 10 m. Pretvornik prikazuje do četiri decimale, a za službene svrhe zaokružuj prema
            uputama tijela kojem predaješ podatke.
        </p>

        <h2>Česta pitanja</h2>
        <h3>Zašto se površina u katastru i ARKOD-u razlikuje?</h3>
        <p>
            Katastarska površina je površina čestice u službenoj evidenciji, a ARKOD bilježi površinu koja se stvarno
            koristi za poljoprivredu (bez putova, kanala, zgrada, šikare). Zato ARKOD parcela može biti manja od
            čestice, a može obuhvatiti i više čestica.
        </p>
        <h3>Jesu li sva „jutra" ista?</h3>
        <p>
            Ne. Ovaj pretvornik računa s <strong>katastarskim</strong> jutrom od 1.600 čhv, koje se koristi u zemljišnim
            knjigama i katastru. U narodu su se koristile i druge mjere istog ili sličnog imena (npr. „ralo", „motika",
            ugarsko jutro) koje su se razlikovale od kraja do kraja. Ako isprava ne kaže „katastarsko", provjeri u katastru.
        </p>
        <h3>Koliko je jedan dulum?</h3>
        <p>
            Dulum je stara mjera koja se ponegdje još spominje (npr. u Dalmaciji), ali njegova vrijednost nije bila
            svugdje ista, pa je pretvornik ne nudi. Za kupoprodaju i potpore uvijek se oslanjaj na m² ili hektare iz
            katastra i ARKOD-a.
        </p>
        <h3>Gdje mi treba površina u hektarima?</h3>
        <p>
            U ARKOD-u i zahtjevima za potpore, u evidenciji gnojidbe i zaštite bilja, te kod računanja doza: doza
            sredstva i gnojiva uvijek je po hektaru — vidi <a href="{{ route('alat', 'kalkulator-skropiva') }}">kalkulator škropiva</a>
            i <a href="{{ route('alat', 'kalkulator-gnojiva') }}">kalkulator gnojiva</a>.
        </p>
    </div>

    @include('alati._ostali')
@endsection

@push('scripts')
<script>
(function () {
    var J = [['ha', 'Hektari (ha)', 10000], ['a', 'Ari (a)', 100], ['m2', 'Kvadratni metri (m²)', 1], ['jutro', 'Katastarska jutra', 5754.64], ['chv', 'Četvorni hvati (čhv)', 3.596652]];
    var f = new Intl.NumberFormat('hr-HR', { maximumFractionDigits: 4 });
    var box = document.getElementById('polja');
    box.innerHTML = J.map(function (j) { return '<div class="fld"><label for="u_' + j[0] + '">' + j[1] + '</label><input id="u_' + j[0] + '" type="text" inputmode="decimal"></div>'; }).join('');
    function set(m2, skip) {
        J.forEach(function (j) { if (j[0] !== skip) document.getElementById('u_' + j[0]).value = m2 ? f.format(m2 / j[2]) : ''; });
    }
    J.forEach(function (j) {
        document.getElementById('u_' + j[0]).addEventListener('input', function () {
            var v = parseFloat(this.value.replace(/\./g, '').replace(',', '.'));
            set(isNaN(v) ? 0 : v * j[2], j[0]);
        });
    });
    document.getElementById('u_ha').value = '1'; set(10000, 'ha');
})();
</script>
@endpush
