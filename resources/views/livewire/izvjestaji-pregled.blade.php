<div class="space-y-5">
    @php
        $b = fn ($v, $d = 1) => \App\Support\Skladiste::broj($v, $d);
        $masa = fn ($kg) => $kg >= 1000 ? [$b($kg / 1000, 1), 't'] : [$b($kg, 0), 'kg'];
        [$dusikV, $dusikJ] = $masa($dusik);
        [$gnojivaV, $gnojivaJ] = $masa($gnojiva);
        $gumb = 'inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 whitespace-nowrap';
        $oznaka = 'inline-block rounded-md border border-gray-300 px-2 py-0.5 text-xs font-semibold text-gray-600';
    @endphp

    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-sm text-gray-600">Evidencije za ispis, PDF ili Excel</p>
            <p class="text-xs text-gray-500">Razdoblje {{ $odDatum->format('d.m.Y.') }} – {{ $doDatum->format('d.m.Y.') }}</p>
        </div>
        @include('partials.razdoblje')
    </div>

    {{-- brojke --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Sredstva za zaštitu</p>
            <p class="text-3xl font-bold text-gray-900 leading-tight">{{ $b($sredstva, 1) }} <span class="text-base font-medium text-gray-500">L / kg</span></p>
            <p class="text-xs text-gray-500">{{ $brojPrskanja }} {{ $brojPrskanja === 1 ? 'prskanje' : 'prskanja' }} · doza × tretirana površina</p>
        </div>
        <div class="rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Gnojiva</p>
            <p class="text-3xl font-bold text-gray-900 leading-tight">{{ $gnojivaV }} <span class="text-base font-medium text-gray-500">{{ $gnojivaJ }}</span></p>
            <p class="text-xs text-gray-500">{{ $brojGnojidbi }} {{ $brojGnojidbi === 1 ? 'gnojidba' : 'gnojidbi' }} · kg/ha × površina kulture</p>
        </div>
        <div class="rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Dušik</p>
            <p class="text-3xl font-bold text-gray-900 leading-tight">{{ $dusikV }} <span class="text-base font-medium text-gray-500">{{ $dusikJ }} N</span></p>
            <p class="text-xs text-gray-500">iz gnojidbe s poznatim udjelom dušika</p>
        </div>
    </div>

    {{-- izvoz --}}
    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
        <h4 class="px-4 py-3 font-semibold text-gray-800 border-b border-gray-100">Izvoz za odabrano razdoblje</h4>
        <ul class="divide-y divide-gray-100">
            <li class="px-4 py-3 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="font-medium text-gray-800">Evidencija o primjeni sredstava za zaštitu bilja</p>
                    <p class="text-xs text-gray-500">{{ $brojPrskanja }} {{ $brojPrskanja === 1 ? 'prskanje' : 'prskanja' }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('izvjestaji.ispis', ['vrsta' => 'prskanje'] + $upit) }}" target="_blank" class="{{ $gumb }}">🖨 Ispis / PDF</a>
                    <a href="{{ route('prskanje.xlsx', $upit) }}" class="{{ $gumb }}"><span class="{{ $oznaka }}">XLSX</span> Excel (Ministarstvo)</a>
                    <a href="{{ route('prskanje.csv', $upit) }}" class="{{ $gumb }}"><span class="{{ $oznaka }}">CSV</span> Preuzmi</a>
                </div>
            </li>
            <li class="px-4 py-3 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="font-medium text-gray-800">Evidencija gnojidbe</p>
                    <p class="text-xs text-gray-500">{{ $brojGnojidbi }} {{ $brojGnojidbi === 1 ? 'gnojidba' : 'gnojidbi' }}, s N-P-K i kg dušika</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('izvjestaji.ispis', ['vrsta' => 'gnojidba'] + $upit) }}" target="_blank" class="{{ $gumb }}">🖨 Ispis / PDF</a>
                    <a href="{{ route('izvjestaji.gnojidba.csv', $upit) }}" class="{{ $gumb }}"><span class="{{ $oznaka }}">CSV</span> Preuzmi</a>
                </div>
            </li>
            <li class="px-4 py-3 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="font-medium text-gray-800">Promet skladišta i troškovi</p>
                    <p class="text-xs text-gray-500">
                        @if($imaSkladiste){{ $brojPrometa }} {{ $brojPrometa === 1 ? 'redak' : 'redaka' }}: nabava, inventura, prijenos i potrošnja s iznosom u €
                        @else Dostupno kad dodate <a href="{{ route('skladiste') }}" class="text-green-700 underline">skladište</a>. @endif
                    </p>
                </div>
                @if($imaSkladiste)
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('izvjestaji.skladiste.csv', $upit) }}" class="{{ $gumb }}"><span class="{{ $oznaka }}">CSV</span> Preuzmi</a>
                    </div>
                @endif
            </li>
        </ul>
        <p class="px-4 py-3 text-xs text-gray-500 border-t border-gray-100 bg-gray-50">
            Ispis se otvara u novoj kartici: u dijalogu ispisa odaberite „Spremi kao PDF” ili pisač (A4 položeno).
            CSV se otvara u Excelu s ispravnim slovima (č, ć, š, ž, đ) i decimalnim zarezom.
        </p>
    </div>
</div>
