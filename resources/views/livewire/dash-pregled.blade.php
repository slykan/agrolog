<div class="bg-white shadow-sm rounded-xl p-5 sm:p-6">
    @php
        $mj = ['sij', 'velj', 'ožu', 'tra', 'svi', 'lip', 'srp', 'kol', 'ruj', 'lis', 'stu', 'pro'];
        $n = count($mjeseci);
        $svaki = max(1, (int) ceil($n / 12));   // kod dugih razdoblja natpis na svakom n-tom mjesecu
        $najveci = collect($mjeseci)->sortByDesc(fn ($m) => $m['prskanje'] + $m['gnojidba'])->keys()->first();
        $mnozina = fn ($n, $jedan, $dva) => ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 12 || $n % 100 > 14)) ? $dva : ($n % 10 === 1 && $n % 100 !== 11 ? $jedan : $jedan);
        $boje = ['gnojidba' => '#008300', 'prskanje' => '#2a78d6'];   // provjereno: CVD ΔE 26,5, kontrast ≥ 3:1
    @endphp

    {{-- naslov i razdoblje --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-5">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Pregled gospodarstva</h3>
            <p class="text-xs text-gray-500">{{ $odDatum->format('d.m.Y.') }} – {{ $doDatum->format('d.m.Y.') }}</p>
        </div>
        <div class="flex flex-col items-start sm:items-end gap-2">
            <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-0.5 text-sm" role="group" aria-label="Razdoblje">
                @foreach(['godina' => 'Ova godina', 'prosla' => 'Prošla', '12mj' => '12 mj.', 'oddo' => 'Od–do'] as $k => $v)
                    <button wire:click="postavi('{{ $k }}')" aria-pressed="{{ $razdoblje === $k ? 'true' : 'false' }}"
                            class="px-3 py-1 rounded-md transition {{ $razdoblje === $k ? 'bg-green-700 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">{{ $v }}</button>
                @endforeach
            </div>
            @if($razdoblje === 'oddo')
                <div class="flex items-center gap-1 text-sm">
                    <input type="date" wire:model.live="od" class="form-input py-0.5">
                    <span class="text-gray-400">–</span>
                    <input type="date" wire:model.live="do" class="form-input py-0.5">
                </div>
            @endif
        </div>
    </div>

    {{-- brojke --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
        <div class="rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Površina</p>
            <p class="text-3xl font-bold text-gray-900 leading-tight">{{ number_format($povrsina, 1, ',', '.') }} <span class="text-base font-medium text-gray-500">ha</span></p>
            <p class="text-xs text-gray-500">{{ $brojParcela }} {{ $mnozina($brojParcela, 'parcela', 'parcele') }} · {{ $brojKultura }} {{ $mnozina($brojKultura, 'kultura', 'kulture') }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Aktivnosti {{ $oznaka }}</p>
            <p class="text-3xl font-bold text-gray-900 leading-tight">{{ $ukupno }}</p>
            <p class="text-xs text-gray-500">
                {{ $prskanja }} prskanja · {{ $gnojidbe }} gnojidbi
                @if($promjena !== null)
                    <br><span class="font-medium text-gray-700">{{ $promjena >= 0 ? '▲ +' : '▼ ' }}{{ $promjena }} %</span> u odnosu na isto razdoblje godinu ranije ({{ $ukupnoPrije }})
                @endif
            </p>
        </div>
        <a href="{{ route('prskanje') }}" class="rounded-xl border p-4 transition {{ $bezPodataka + $bezEppo ? 'border-amber-200 bg-amber-50 hover:bg-amber-100' : 'border-gray-200 hover:bg-gray-50' }}">
            <p class="text-sm text-gray-500">Za dopuniti (evidencija 2027.)</p>
            <p class="text-3xl font-bold leading-tight {{ $bezPodataka + $bezEppo ? 'text-amber-800' : 'text-gray-900' }}">{{ $bezPodataka + $bezEppo }}</p>
            <p class="text-xs text-gray-500">
                @if($bezPodataka + $bezEppo === 0)
                    ✓ Svi zapisi imaju reg. broj, fenofazu i EPPO kod
                @else
                    @if($bezPodataka){{ $bezPodataka }} prskanja bez reg. broja ili fenofaze @endif
                    @if($bezPodataka && $bezEppo)·@endif
                    @if($bezEppo){{ $bezEppo }} {{ $mnozina($bezEppo, 'kultura', 'kulture') }} bez EPPO koda @endif
                @endif
            </p>
        </a>
    </div>

    {{-- graf: aktivnosti po mjesecima --}}
    <div class="rounded-xl border border-gray-200 p-4">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <h4 class="font-semibold text-gray-800">Aktivnosti po mjesecima</h4>
            <div class="flex items-center gap-4 text-sm text-gray-600" aria-hidden="true">
                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm" style="background: {{ $boje['gnojidba'] }}"></span>Gnojidba</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm" style="background: {{ $boje['prskanje'] }}"></span>Prskanje</span>
            </div>
        </div>

        @if($ukupno === 0)
            <p class="text-sm text-gray-500 py-10 text-center">U ovom razdoblju nema zapisa prskanja ni gnojidbe.</p>
        @else
            <div class="flex gap-2" role="img" aria-label="Aktivnosti po mjesecima: {{ $prskanja }} prskanja i {{ $gnojidbe }} gnojidbi">
                {{-- os y --}}
                <div class="relative w-6 h-44 text-[11px] text-gray-400 shrink-0">
                    @for($v = 0; $v <= $vrh; $v += $korak)
                        <span class="absolute right-0 translate-y-1/2 leading-none" style="bottom: {{ $v / $vrh * 100 }}%">{{ $v }}</span>
                    @endfor
                </div>
                <div class="flex-1 min-w-0">
                    <div class="relative h-44">
                        {{-- mreža --}}
                        @for($v = 0; $v <= $vrh; $v += $korak)
                            <div class="absolute inset-x-0 border-t {{ $v === 0 ? 'border-gray-300' : 'border-gray-100' }}" style="bottom: {{ $v / $vrh * 100 }}%"></div>
                        @endfor
                        {{-- stupci --}}
                        <div class="absolute inset-0 flex items-end">
                            @foreach($mjeseci as $i => $m)
                                @php $zbroj = $m['prskanje'] + $m['gnojidba']; @endphp
                                <div class="group relative flex-1 h-full flex flex-col justify-end items-center">
                                    @if($zbroj > 0)
                                        @if($i === $najveci)
                                            <span class="text-[11px] font-semibold text-gray-700 mb-0.5">{{ $zbroj }}</span>
                                        @endif
                                        <div class="w-3/5 max-w-[28px] flex flex-col" style="height: {{ $zbroj / $vrh * 100 }}%">
                                            @if($m['prskanje'])
                                                <div class="w-full {{ $m['gnojidba'] ? 'border-b-2 border-white' : '' }} rounded-t" style="flex: {{ $m['prskanje'] }} 1 0; background: {{ $boje['prskanje'] }}"></div>
                                            @endif
                                            @if($m['gnojidba'])
                                                <div class="w-full {{ $m['prskanje'] ? '' : 'rounded-t' }}" style="flex: {{ $m['gnojidba'] }} 1 0; background: {{ $boje['gnojidba'] }}"></div>
                                            @endif
                                        </div>
                                    @endif
                                    {{-- oblačić: cijeli stupac je meta --}}
                                    <div class="pointer-events-none absolute bottom-full mb-1 left-1/2 -translate-x-1/2 z-10 hidden group-hover:block whitespace-nowrap rounded-lg bg-gray-900 text-white text-xs px-3 py-2 shadow-lg">
                                        <div class="font-semibold mb-1">{{ $mj[$m['mjesec']->month - 1] }} {{ $m['mjesec']->format('Y.') }}</div>
                                        <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-sm" style="background: {{ $boje['prskanje'] }}"></span>Prskanje: {{ $m['prskanje'] }}</div>
                                        <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-sm" style="background: {{ $boje['gnojidba'] }}"></span>Gnojidba: {{ $m['gnojidba'] }}</div>
                                        <div class="text-gray-300 mt-1">Ukupno: {{ $zbroj }}</div>
                                    </div>
                                    <div class="absolute inset-0 group-hover:bg-gray-500/5 rounded"></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    {{-- os x --}}
                    <div class="flex mt-1.5 text-[11px] text-gray-500">
                        @foreach($mjeseci as $i => $m)
                            <div class="flex-1 text-center truncate">
                                @if($i % $svaki === 0){{ $mj[$m['mjesec']->month - 1] }}@if($i === 0 || $m['mjesec']->month === 1)<span class="hidden sm:inline"> {{ $m['mjesec']->format('y') }}</span>@endif @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <details class="mt-3 text-sm">
                <summary class="cursor-pointer text-gray-500 hover:text-gray-700 text-xs">Prikaži kao tablicu</summary>
                <table class="mt-2 w-full text-xs">
                    <thead><tr class="text-gray-500 text-left"><th class="py-1">Mjesec</th><th class="text-right">Prskanje</th><th class="text-right">Gnojidba</th><th class="text-right">Ukupno</th></tr></thead>
                    <tbody class="text-gray-700">
                        @foreach($mjeseci as $m)
                            <tr class="border-t border-gray-100"><td class="py-1">{{ $mj[$m['mjesec']->month - 1] }} {{ $m['mjesec']->format('Y.') }}</td><td class="text-right">{{ $m['prskanje'] }}</td><td class="text-right">{{ $m['gnojidba'] }}</td><td class="text-right font-medium">{{ $m['prskanje'] + $m['gnojidba'] }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </details>
        @endif
    </div>
</div>
