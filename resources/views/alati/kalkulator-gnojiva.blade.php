@extends('layouts.public', [
    'title' => 'Kalkulator gnojiva — KAN, UREA, NPK po hektaru',
    'description' => 'Koliko KAN-a, UREE ili NPK gnojiva treba za željenu količinu dušika, fosfora ili kalija po hektaru? Upiši kg hraniva i postotak u gnojivu.',
    'active' => 'alati',
    'ads' => true,
])

@section('content')
    <a href="{{ route('alati') }}" class="text-sm text-gray-500 hover:text-green-700 no-print">← Svi alati</a>
    <h1 class="text-4xl font-bold text-gray-900 mt-3 mb-3">🌱 Kalkulator gnojiva</h1>
    <p class="text-lg text-gray-500 mb-8">Preporuke gnojidbe pišu se u kg čistog hraniva po hektaru. Kalkulator ih pretvara u kilograme (i vreće) gnojiva.</p>

    <div class="border border-gray-200 rounded-2xl p-6">
        <div class="grid sm:grid-cols-2 gap-5">
            <div class="fld"><label for="gn">Gnojivo</label>
                <select id="gn">
                    <option value="27">KAN — 27 % N</option>
                    <option value="46">UREA — 46 % N</option>
                    <option value="15">NPK 15-15-15 — 15 % hraniva</option>
                    <option value="7">NPK 7-20-30 — 7 % N</option>
                    <option value="20">NPK 7-20-30 — 20 % P₂O₅</option>
                    <option value="30">NPK 7-20-30 — 30 % K₂O</option>
                    <option value="0">drugo (upiši postotak)</option>
                </select></div>
            <div class="fld"><label for="pct">Udio hraniva u gnojivu, %</label><input id="pct" type="number" step="0.1" value="27" inputmode="decimal"><div class="hint">piše na vreći, npr. „27 % N"</div></div>
            <div class="fld"><label for="hr">Željena količina hraniva</label><input id="hr" type="number" step="5" value="80" inputmode="decimal"><div class="hint">kg/ha čistog hraniva (N, P₂O₅ ili K₂O)</div></div>
            <div class="fld"><label for="pov">Površina</label><input id="pov" type="number" step="0.1" value="3" inputmode="decimal"><div class="hint">ha</div></div>
            <div class="fld"><label for="vr">Vreća</label><input id="vr" type="number" step="5" value="50" inputmode="decimal"><div class="hint">kg po vreći</div></div>
        </div>
        <div class="result" id="rez"></div>
    </div>

    <div class="prose-agro mt-10">
        <h2>Formula</h2>
        <p><strong>kg gnojiva/ha = kg hraniva/ha × 100 ÷ % hraniva u gnojivu</strong></p>
        <p>Primjer: za 80 kg N/ha KAN-om (27 % N) treba 80 × 100 ÷ 27 ≈ 296 kg KAN-a po hektaru.</p>
        <div class="box-note">
            Kod NPK gnojiva računaj prema hranivu koje ti je najvažnije (najčešće P ili K), a ostala hraniva provjeri:
            iz iste količine gnojiva dobit ćeš i N u omjeru s vreće.
        </div>
        <h2>Primjeri izračuna</h2>
        <table class="tbl">
            <tr><th>Cilj</th><th>Gnojivo</th><th class="num">kg/ha</th><th class="num">vreća 50 kg za 5 ha</th></tr>
            <tr><td>80 kg N/ha</td><td>KAN 27 % N</td><td class="num">296</td><td class="num">30</td></tr>
            <tr><td>80 kg N/ha</td><td>UREA 46 % N</td><td class="num">174</td><td class="num">18</td></tr>
            <tr><td>60 kg P₂O₅/ha</td><td>NPK 7-20-30</td><td class="num">300</td><td class="num">30</td></tr>
            <tr><td>45 kg N, P₂O₅ i K₂O/ha</td><td>NPK 15-15-15</td><td class="num">300</td><td class="num">30</td></tr>
        </table>
        <p>
            U trećem primjeru 300 kg NPK 7-20-30 uz 60 kg P₂O₅ donosi i 21 kg N (300 × 7 %) i 90 kg K₂O (300 × 30 %).
            Ako kultura treba više dušika, razliku dodaješ KAN-om ili UREOM u prihrani: npr. do 120 kg N treba još
            99 kg N, odnosno oko 367 kg KAN-a po hektaru, najbolje u dva obroka.
        </p>

        <h2>Kako pročitati brojke na vreći</h2>
        <p>
            Tri broja na vreći NPK gnojiva (npr. 7-20-30) su postoci <strong>dušika (N)</strong>, <strong>fosfora izraženog kao
            P₂O₅</strong> i <strong>kalija izraženog kao K₂O</strong>. Preporuke gnojidbe i nalazi analize tla u Hrvatskoj
            također se iskazuju u P₂O₅ i K₂O, pa ih možeš izravno uspoređivati. Vreća od 50 kg NPK 7-20-30 sadrži
            3,5 kg N, 10 kg P₂O₅ i 15 kg K₂O.
        </p>

        <h2>Česta pitanja</h2>
        <h3>KAN ili UREA?</h3>
        <p>
            UREA ima više dušika (46 %), pa je po kilogramu dušika često jeftinija i trebaš manje vreća. Dušik iz uree
            mora se najprije pretvoriti u tlu, a na površini dio može ishlapjeti kao amonijak, osobito na toplom i
            vlažnom vremenu ako se ne unese u tlo. KAN djeluje brže i manje je osjetljiv na gubitke, pa se češće koristi
            u prihrani. Usporedi cijenu po kilogramu <em>dušika</em>, ne po vreći.
        </p>
        <h3>Koliko hraniva treba moja kultura?</h3>
        <p>
            Ovisi o kulturi, očekivanom prinosu, predusjevu i opskrbljenosti tla. Najpouzdanija polazna točka je
            <a href="{{ route('vodic', 'analiza-tla-uzimanje-uzorka') }}">analiza tla</a> s preporukom gnojidbe. Na ranjivim
            područjima propisane su i najveće dopuštene količine dušika po kulturi — vidi vodič
            <a href="{{ route('vodic', 'nitrati-ranjiva-podrucja-zabrana-gnojidbe') }}">nitrati i ranjiva područja</a>.
        </p>
        <h3>Kako uračunati stajski gnoj?</h3>
        <p>
            Procijeni dušik iz gnoja (npr. goveđi stajski gnoj ima oko 0,5 % N, pa 20 t/ha donosi oko 100 kg N) i oduzmi
            ga od ukupne potrebe; za ostatak upiši kilograme hraniva u kalkulator. Dio dušika iz gnoja biljka iskoristi
            tek sljedećih godina.
        </p>
        <p>
            Svaku gnojidbu upiši u evidenciju — vidi <a href="{{ route('vodic', 'evidencija-gnojidbe-sto-upisati') }}">evidencija gnojidbe: što upisati</a>.
        </p>

        <div class="box-warn">
            Količinu hraniva treba odrediti prema analizi tla, kulturi i očekivanom prinosu. U <strong>ranjivim područjima</strong>
            vrijede ograničenja Akcijskog programa zaštite voda od nitrata, uključujući vođenje evidencije gnojidbe.
        </div>
        <p>Više u vodiču <a href="{{ route('vodic', 'gnojidba-dusikom-kan-urea-npk-izracun') }}">Gnojidba dušikom: kako iz kg hraniva izračunati KAN, UREU ili NPK</a>.</p>
    </div>

    @include('alati._ostali')
@endsection

@push('scripts')
<script>
(function () {
    var f = new Intl.NumberFormat('hr-HR', { maximumFractionDigits: 1 });
    var $ = function (id) { return document.getElementById(id); };
    function v(id) { return Math.max(0, parseFloat(String($(id).value).replace(',', '.')) || 0); }
    $('gn').addEventListener('change', function () { if (this.value !== '0') { $('pct').value = this.value; } calc(); });
    function calc() {
        var pct = v('pct'), h = v('hr'), p = v('pov'), vr = v('vr');
        if (!pct) { $('rez').innerHTML = '<div class="lbl">Upiši postotak hraniva u gnojivu.</div>'; return; }
        var poHa = h * 100 / pct, uk = poHa * p;
        $('rez').innerHTML = '<div class="lbl">Po hektaru</div><div class="big">' + f.format(poHa) + ' kg gnojiva</div>' +
            '<div class="grid sm:grid-cols-2 gap-4 mt-4"><div><div class="lbl">Ukupno za ' + f.format(p) + ' ha</div><strong>' + f.format(uk) + ' kg</strong></div>' +
            (vr ? '<div><div class="lbl">Vreća od ' + f.format(vr) + ' kg</div><strong>' + f.format(uk / vr) + ' vreća (' + Math.ceil(uk / vr - 1e-9) + ' kupiti)</strong></div>' : '') + '</div>';
    }
    ['pct', 'hr', 'pov', 'vr'].forEach(function (id) { $(id).addEventListener('input', calc); });
    calc();
})();
</script>
@endpush
