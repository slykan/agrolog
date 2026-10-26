@extends('layouts.public', [
    'title' => 'Kalkulator karence — najraniji datum berbe',
    'description' => 'Upiši datum zadnjeg tretiranja i karencu sredstva za zaštitu bilja u danima — kalkulator pokaže najraniji dopušteni datum berbe.',
    'active' => 'alati',
    'ads' => true,
])

@section('content')
    <a href="{{ route('alati') }}" class="text-sm text-gray-500 hover:text-green-700 no-print">← Svi alati</a>
    <h1 class="text-4xl font-bold text-gray-900 mt-3 mb-3">📅 Kalkulator karence</h1>
    <p class="text-lg text-gray-500 mb-8">Kada najranije smiješ brati nakon tretiranja? Upiši datum i karencu s etikete sredstva.</p>

    <div class="border border-gray-200 rounded-2xl p-6">
        <div class="grid sm:grid-cols-2 gap-5">
            <div class="fld"><label for="dat">Datum zadnjeg tretiranja</label><input id="dat" type="date"></div>
            <div class="fld"><label for="kar">Karenca (dana)</label><input id="kar" type="number" min="0" step="1" value="14" inputmode="numeric"><div class="hint">s etikete sredstva, za tu kulturu</div></div>
        </div>
        <div class="result" id="rez"></div>
    </div>

    <div class="prose-agro mt-10">
        <h2>Kako se računa</h2>
        <p>
            Karenca se broji u danima od dana tretiranja. Ako je tretirano 1. lipnja, a karenca je 14 dana, najraniji
            datum berbe je 15. lipnja. Ako si istu kulturu tretirao s više sredstava, mjerodavna je <strong>najduža</strong>
            karenca od zadnjih primjena.
        </p>
        <h2>Primjeri</h2>
        <table class="tbl">
            <tr><th>Situacija</th><th>Tretiranja</th><th>Najranija berba</th></tr>
            <tr><td>Jedno sredstvo</td><td>fungicid 3. srpnja, karenca 21 dan</td><td>24. srpnja</td></tr>
            <tr><td>Dva sredstva isti dan</td><td>5. kolovoza: insekticid 7 dana i fungicid 14 dana</td><td>19. kolovoza (vrijedi duža)</td></tr>
            <tr><td>Kasnije tretiranje s kraćom karencom</td><td>1. lipnja: 28 dana; 10. lipnja: 7 dana</td><td>29. lipnja (prvo ističe kasnije)</td></tr>
        </table>
        <p>
            Treći primjer je čest izvor pogreške: nije uvijek mjerodavno <em>zadnje</em> tretiranje. Za svako sredstvo
            izračunaj datum posebno i uzmi <strong>najkasniji</strong>. Kalkulator zato pokreni za svako sredstvo zasebno.
        </p>

        <h2>Česta pitanja</h2>
        <h3>Računa li se dan tretiranja?</h3>
        <p>
            Karenca se broji u danima od dana tretiranja: uz tretiranje 1. lipnja i karencu od 7 dana, berba je dopuštena
            8. lipnja. Kalkulator računa upravo tako. Ako si na granici, dan više nikad nije pogreška.
        </p>
        <h3>Što znači „karenca osigurana vremenom primjene"?</h3>
        <p>
            Kod nekih sredstava etiketa umjesto broja dana navodi fazu razvoja do koje se sredstvo smije primijeniti
            (npr. do cvatnje). Ako se ta faza poštuje, između primjene i berbe sigurno prođe dovoljno vremena. Tu
            kalkulator ne pomaže — mjerodavna je faza s etikete.
        </p>
        <h3>Vrijedi li karenca i za ispašu i krmu?</h3>
        <p>
            Da, ako je na etiketi navedena — tada se računa do ispaše ili košnje za krmu, jednako kao do berbe.
        </p>
        <h3>Smijem li ući u nasad prije isteka karence?</h3>
        <p>
            Karenca štiti potrošača i odnosi se na berbu. Za ulazak ljudi u tretirani nasad etiketa može propisati
            posebno vrijeme ponovnog ulaska ili zaštitnu opremu — to je zaštita radnika i treba ga posebno provjeriti.
        </p>
        <h3>Kako dokazati da je karenca poštovana?</h3>
        <p>
            Evidencijom o uporabi sredstava: datum, sredstvo, kultura i doza za svako tretiranje, uz datum berbe.
            Otkupljivači to sve češće traže. Vidi vodič
            <a href="{{ route('vodic', 'elektronicka-evidencija-sredstava-za-zastitu-bilja') }}">elektronička evidencija sredstava za zaštitu bilja</a>.
        </p>

        <div class="box-warn">
            Karenca je propisana za svaku kulturu posebno i navedena na etiketi sredstva. Kalkulator samo zbraja dane —
            kod dvojbe provjeri etiketu ili savjetodavnu službu.
        </div>
        <p>Više u vodiču <a href="{{ route('vodic', 'karenca-sto-je-i-kako-je-racunati') }}">Karenca: što je, kako se računa i zašto je važna</a>.</p>
    </div>

    @include('alati._ostali')
@endsection

@push('scripts')
<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var df = new Intl.DateTimeFormat('hr-HR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    var d = new Date(); $('dat').value = d.toISOString().slice(0, 10);
    function calc() {
        var s = $('dat').value, k = parseInt($('kar').value, 10) || 0;
        if (!s) { $('rez').innerHTML = '<div class="lbl">Odaberi datum tretiranja.</div>'; return; }
        var p = s.split('-'), t = new Date(+p[0], +p[1] - 1, +p[2]); t.setDate(t.getDate() + k);
        var danas = new Date(); danas.setHours(0, 0, 0, 0);
        var ost = Math.round((t - danas) / 86400000);
        $('rez').innerHTML = '<div class="lbl">Najraniji dopušteni datum berbe</div><div class="big">' + df.format(t) + '</div>' +
            '<div class="lbl mt-2">' + (ost > 0 ? 'Još ' + ost + (ost === 1 ? ' dan' : ' dana') : 'Karenca je istekla — berba je dopuštena') + '</div>';
    }
    ['dat', 'kar'].forEach(function (id) { $(id).addEventListener('input', calc); });
    calc();
})();
</script>
@endpush
