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
