@extends('layouts.public', [
    'title' => 'Kalkulator škropiva — sredstvo i voda za prskalicu',
    'description' => 'Upiši dozu sredstva po hektaru, utrošak vode i volumen spremnika: kalkulator izračuna koliko sredstva ide u spremnik i koliko punjenja treba za površinu.',
    'active' => 'alati',
    'ads' => true,
])

@section('content')
    <a href="{{ route('alati') }}" class="text-sm text-gray-500 hover:text-green-700 no-print">← Svi alati</a>
    <h1 class="text-4xl font-bold text-gray-900 mt-3 mb-3">💧 Kalkulator škropiva</h1>
    <p class="text-lg text-gray-500 mb-8">Koliko sredstva ide u spremnik, koliko punjenja treba i koliko ukupno sredstva i vode za površinu koju tretiraš.</p>

    <div class="border border-gray-200 rounded-2xl p-6">
        <div class="grid sm:grid-cols-2 gap-5">
            <div class="fld"><label for="doza">Doza sredstva (s etikete)</label><input id="doza" type="number" step="0.01" value="1.5" inputmode="decimal"><div class="hint">l/ha ili kg/ha</div></div>
            <div class="fld"><label for="jed">Jedinica sredstva</label><select id="jed"><option value="l">litre (tekuće)</option><option value="kg">kilogrami (prašivo/granule)</option></select></div>
            <div class="fld"><label for="voda">Utrošak vode</label><input id="voda" type="number" step="10" value="300" inputmode="decimal"><div class="hint">l/ha — prema etiketi i kalibraciji prskalice</div></div>
            <div class="fld"><label for="spremnik">Volumen spremnika</label><input id="spremnik" type="number" step="50" value="600" inputmode="decimal"><div class="hint">litre</div></div>
            <div class="fld"><label for="povrsina">Površina</label><input id="povrsina" type="number" step="0.1" value="5" inputmode="decimal"><div class="hint">hektari (1 ha = 10.000 m²)</div></div>
        </div>
        <div class="result" id="rez"></div>
    </div>

    <div class="prose-agro mt-10">
        <h2>Kako kalkulator računa</h2>
        <ul>
            <li><strong>Površina jednog punjenja</strong> = volumen spremnika ÷ utrošak vode po ha.</li>
            <li><strong>Sredstvo po spremniku</strong> = doza po ha × površina jednog punjenja.</li>
            <li><strong>Ukupno sredstva</strong> = doza × površina; <strong>ukupno vode</strong> = utrošak vode × površina.</li>
        </ul>
        <p>Primjer: doza 1,5 l/ha, 300 l vode/ha, spremnik 600 l → jedno punjenje pokriva 2 ha i u njega ide 3 l sredstva.</p>

        <h2>Još primjera</h2>
        <table class="tbl">
            <tr><th>Situacija</th><th>Doza</th><th>Voda</th><th>Spremnik</th><th>Rezultat</th></tr>
            <tr><td>Herbicid u kukuruzu, 7 ha</td><td>1,2 l/ha</td><td>250 l/ha</td><td>1.000 l</td><td>4,8 l po punjenju; 1 puno + zadnje za 3 ha (750 l vode, 3,6 l)</td></tr>
            <tr><td>Fungicid u pšenici, 12 ha</td><td>0,8 l/ha</td><td>200 l/ha</td><td>2.500 l</td><td>10 l po punjenju za 12,5 ha — dovoljno jedno punjenje od 2.400 l s 9,6 l</td></tr>
            <tr><td>Vinograd, 1,5 ha</td><td>2 kg/ha</td><td>400 l/ha</td><td>400 l</td><td>2 kg po punjenju; ukupno 3 kg i 600 l vode</td></tr>
        </table>

        <h2>Zašto je točan utrošak vode toliko važan</h2>
        <p>
            Kalkulator pretpostavlja da prskalica stvarno izbacuje upisanu količinu vode po hektaru. Ako je stvarni
            utrošak 330 umjesto 300 l/ha (npr. zbog istrošenih sapnica), spremnik se isprazni prije nego što pokriješ
            planiranu površinu, a doza po hektaru je 10 % veća od planirane. Zato prije sezone napravi
            <a href="{{ route('vodic', 'kalibracija-prskalice-korak-po-korak') }}">kalibraciju prskalice</a> i upiši
            izmjereni utrošak, a ne onaj iz prospekta.
        </p>

        <h2>Česta pitanja</h2>
        <h3>Doza je zadana u postotku (npr. 0,3 %). Kako to upisati?</h3>
        <p>
            Koncentracija znači količinu sredstva na 100 l vode: 0,3 % = 300 ml na 100 l. Za spremnik od 600 l to je
            1,8 l sredstva. U kalkulator možeš upisati i dozu po hektaru: 0,3 % × utrošak vode (npr. 1.000 l/ha) = 3 l/ha.
            Provjeri da dobivena količina po hektaru ne prelazi najveću dopuštenu dozu s etikete.
        </p>
        <h3>Smijem li smanjiti količinu vode da manje puta punim?</h3>
        <p>
            Samo unutar raspona koji etiketa dopušta. Premalo vode znači lošiju pokrivenost biljke, a kod nekih sredstava
            i slabije djelovanje. Doza sredstva po hektaru ostaje ista — mijenja se samo koncentracija u spremniku.
        </p>
        <h3>Što s ostatkom u spremniku?</h3>
        <p>
            Najbolje ga je izbjeći točnim izračunom zadnjeg punjenja. Ako ostane, razrijedi ga vodom i poprskaj po već
            tretiranoj površini, poštujući najveću dozu. Ne izlijevaj ga u kanal ili kanalizaciju — vidi
            <a href="{{ route('vodic', 'skladistenje-pesticida-i-ambalaza') }}">skladištenje, ostaci škropiva i ambalaža</a>.
        </p>
        <div class="box-warn">
            Doza i utrošak vode uvijek se uzimaju <strong>s etikete sredstva</strong> za tu kulturu i štetnika. Kalkulator samo
            preračunava — ne zamjenjuje upute proizvođača. Utrošak vode ovisi o kalibraciji prskalice (brzina, tlak, sapnice).
        </div>
        <p>Detaljnije u vodiču <a href="{{ route('vodic', 'kako-izracunati-skropivo-za-prskalicu') }}">Kako izračunati sredstvo i vodu za prskalicu</a>.</p>
    </div>

    @include('alati._ostali')
@endsection

@push('scripts')
<script>
(function () {
    var f = new Intl.NumberFormat('hr-HR', { maximumFractionDigits: 2 });
    var $ = function (id) { return document.getElementById(id); };
    function v(id) { return Math.max(0, parseFloat(String($(id).value).replace(',', '.')) || 0); }
    function calc() {
        var d = v('doza'), w = v('voda'), t = v('spremnik'), p = v('povrsina'), j = $('jed').value;
        if (!w || !t) { $('rez').innerHTML = '<div class="lbl">Upiši utrošak vode i volumen spremnika.</div>'; return; }
        var haPoPunjenju = t / w, sredPoSpr = d * haPoPunjenju, punjenja = p / haPoPunjenju;
        var pune = Math.floor(punjenja + 1e-9), ostatakHa = p - pune * haPoPunjenju;
        $('rez').innerHTML =
            '<div class="lbl">U pun spremnik od ' + f.format(t) + ' l ide</div><div class="big">' + f.format(sredPoSpr) + ' ' + j + ' sredstva</div>' +
            '<div class="grid sm:grid-cols-3 gap-4 mt-4">' +
            '<div><div class="lbl">Jedno punjenje pokriva</div><strong>' + f.format(haPoPunjenju) + ' ha</strong></div>' +
            '<div><div class="lbl">Broj punjenja za ' + f.format(p) + ' ha</div><strong>' + f.format(punjenja) + '</strong>' +
            (ostatakHa > 0.001 ? '<div class="text-xs text-gray-600">' + pune + ' puna + zadnje za ' + f.format(ostatakHa) + ' ha (' + f.format(ostatakHa * w) + ' l vode, ' + f.format(ostatakHa * d) + ' ' + j + ')</div>' : '') + '</div>' +
            '<div><div class="lbl">Ukupno</div><strong>' + f.format(d * p) + ' ' + j + ' · ' + f.format(w * p) + ' l vode</strong></div></div>';
    }
    ['doza', 'jed', 'voda', 'spremnik', 'povrsina'].forEach(function (id) { $(id).addEventListener('input', calc); });
    calc();
})();
</script>
@endpush
