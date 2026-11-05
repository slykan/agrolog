<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.favicon')
    <title>{{ $vrsta === 'prskanje' ? 'Evidencija SZB' : 'Evidencija gnojidbe' }} {{ $od->format('d.m.Y.') }}–{{ $do->format('d.m.Y.') }}</title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, Helvetica, sans-serif; color: #111; font-size: 10px; background: #f3f4f6; }
        .list { background: #fff; max-width: 297mm; margin: 16px auto; padding: 12mm; box-shadow: 0 1px 4px rgba(0,0,0,.1); }
        .zaglavlje { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #111; padding-bottom: 6px; margin-bottom: 8px; }
        .mala { font-size: 9px; letter-spacing: .12em; text-transform: uppercase; color: #555; font-weight: bold; }
        h1 { margin: 2px 0 0; font-size: 18px; font-weight: normal; font-family: Georgia, 'Times New Roman', serif; }
        .desno { text-align: right; font-size: 11px; }
        .podaci { display: flex; gap: 24px; font-size: 10px; margin-bottom: 8px; color: #333; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 3px 4px; vertical-align: top; }
        th { background: #eee; font-size: 8.5px; text-align: left; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        td.br { text-align: right; white-space: nowrap; }
        tr { break-inside: avoid; }
        thead { display: table-header-group; }
        tfoot td { font-weight: bold; background: #f7f7f7; }
        .podnozje { margin-top: 10px; display: flex; justify-content: space-between; font-size: 9px; color: #555; }
        .potpis { margin-top: 24px; font-size: 10px; }
        .alati { max-width: 297mm; margin: 16px auto 0; display: flex; gap: 8px; align-items: center; font-size: 13px; }
        .alati button { background: #19653a; color: #fff; border: 0; border-radius: 8px; padding: 8px 16px; font-size: 14px; cursor: pointer; }
        .prazno { padding: 30px; text-align: center; color: #666; font-size: 12px; }
        @media print { body { background: #fff; } .list { box-shadow: none; margin: 0; padding: 0; max-width: none; } .alati { display: none; } }
    </style>
</head>
<body>
    <div class="alati">
        <button onclick="window.print()">🖨 Ispis / Spremi kao PDF</button>
        <span>U dijalogu ispisa odaberite „Spremi kao PDF” ili pisač.</span>
    </div>

    <div class="list">
        <div class="zaglavlje">
            <div>
                <div class="mala">Evidencija</div>
                <h1>{{ $vrsta === 'prskanje' ? 'Primjena sredstava za zaštitu bilja' : 'Gnojidba' }}</h1>
            </div>
            <div class="desno">
                <strong>{{ $user->naziv_gospodarstva ?: $user->name }}</strong><br>
                {{ $od->format('d.m.Y.') }} – {{ $do->format('d.m.Y.') }}
            </div>
        </div>
        <div class="podaci">
            <span>Korisnik: <strong>{{ $user->name }}</strong></span>
            @if($user->mipg)<span>MIPG: <strong>{{ $user->mipg }}</strong></span>@endif
            @if($user->oib)<span>OIB: <strong>{{ $user->oib }}</strong></span>@endif
        </div>

        @php $b = fn ($v, $d = 2) => $v === null || $v === '' ? '' : \App\Support\Skladiste::broj($v, $d); @endphp

        @if($redovi->isEmpty())
            <div class="prazno">U ovom razdoblju nema zapisa.</div>
        @elseif($vrsta === 'prskanje')
            <table>
                <thead>
                    <tr>
                        <th>Rb.</th><th>Datum</th><th>Vrijeme</th><th>ARKOD</th><th>Kultura (EPPO)</th><th>Tretirano ha</th>
                        <th>Trgovački naziv sredstva</th><th>Reg. broj</th><th>Doza L/kg po ha</th><th>Ukupno</th><th>Voda L/ha</th>
                        <th>BBCH</th><th>Vrsta uporabe</th><th>Sustav</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($redovi as $r)
                        <tr>
                            <td class="br">{{ $loop->iteration }}</td>
                            <td>{{ $r->datum_tretiranja->format('d.m.Y.') }}</td>
                            <td>{{ $r->vrijeme_od ? substr($r->vrijeme_od, 0, 5) : '' }}{{ $r->vrijeme_do ? '–' . substr($r->vrijeme_do, 0, 5) : '' }}</td>
                            <td>{{ $r->arkod_broj }}</td>
                            <td>{{ $r->kultura_naziv }}@if($r->eppo_kod) ({{ $r->eppo_kod }})@endif</td>
                            <td class="br">{{ $b($r->povrsina) }}</td>
                            <td>{{ $r->trgovacki_naziv_sredstva }}</td>
                            <td>{{ $r->registracijski_broj }}</td>
                            <td class="br">{{ $b($r->kolicina_sredstva_l_ha, 3) }}</td>
                            <td class="br">{{ $b($r->ukupno, 2) }}</td>
                            <td class="br">{{ $b($r->kolicina_vode_l_ha, 0) }}</td>
                            <td>{{ $r->bbch }}</td>
                            <td>{{ $vrste[$r->vrsta_uporabe] ?? '' }}</td>
                            <td>{{ $sustavi[$r->sustav_proizvodnje] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Rb.</th><th>Datum</th><th>ARKOD</th><th>Kultura</th><th>Površina ha</th><th>Gnojivo</th>
                        <th>N-P-K %</th><th>kg/ha</th><th>Ukupno kg</th><th>kg N/ha</th><th>Ukupno kg N</th><th>Stajski</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($redovi as $r)
                        <tr>
                            <td class="br">{{ $loop->iteration }}</td>
                            <td>{{ $r->datum->format('d.m.Y.') }}</td>
                            <td>{{ $r->arkod_broj }}</td>
                            <td>{{ $r->kultura_naziv }}</td>
                            <td class="br">{{ $b($r->povrsina) }}</td>
                            <td>{{ $r->tip_gnojiva }}</td>
                            <td>{{ $r->n_posto !== null ? \App\Support\Gnojiva::postotak($r->n_posto) . '-' . \App\Support\Gnojiva::postotak($r->p_posto) . '-' . \App\Support\Gnojiva::postotak($r->k_posto) : '' }}</td>
                            <td class="br">{{ $b($r->kolicina_kg_ha) }}</td>
                            <td class="br">{{ $b($r->ukupno) }}</td>
                            <td class="br">{{ $b($r->kg_n_ha, 1) }}</td>
                            <td class="br">{{ $b($r->kg_n, 1) }}</td>
                            <td>{{ $r->organsko ? 'da' : '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="8">Ukupno</td>
                        <td class="br">{{ $b($redovi->sum('ukupno')) }}</td>
                        <td></td>
                        <td class="br">{{ $b($redovi->sum('kg_n'), 1) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        @endif

        <div class="potpis">Potpis odgovorne osobe: ______________________________</div>
        <div class="podnozje">
            <span>Broj zapisa: {{ $redovi->count() }}</span>
            <span>Izrađeno u AgroLogu {{ now()->format('d.m.Y.') }}</span>
        </div>
    </div>

    @if($automatski)
        <script>window.addEventListener('load', () => setTimeout(() => window.print(), 400));</script>
    @endif
</body>
</html>
