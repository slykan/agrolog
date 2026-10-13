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
