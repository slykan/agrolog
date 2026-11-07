<div class="space-y-5">
    @php
        $mj = ['sij', 'velj', 'ožu', 'tra', 'svi', 'lip', 'srp', 'kol', 'ruj', 'lis', 'stu', 'pro'];
        $n = count($mjeseci);
        $svaki = max(1, (int) ceil($n / 12));
        $najveci = collect($mjeseci)->sortByDesc(fn ($m) => $m['szb'] + $m['gnojivo'])->keys()->first();
        $boje = ['gnojivo' => '#008300', 'szb' => '#2a78d6', 'radovi' => \App\Support\DashKartice::RADOVI];   // iste boje kao na početnoj
        $nazivi = ['szb' => 'Zaštita bilja', 'gnojivo' => 'Gnojivo', 'radovi' => 'Radovi i ostalo'];
        $sveukupno = $utroseno + $meh['ukupno'] + $opci_nevezano;
        $eur = fn ($v, $d = 2) => number_format((float) $v, $d, ',', '.') . ' €';
    @endphp

    {{-- naslov i razdoblje --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-sm text-gray-600">Nabava iz skladišta, utrošak u prskanju i gnojidbi, ostali radovi i opći troškovi</p>
            <p class="text-xs text-gray-500">{{ $odDatum->format('d.m.Y.') }} – {{ $doDatum->format('d.m.Y.') }}</p>
        </div>
        @include('partials.razdoblje')
    </div>

    @if(! $ima_skladiste || ! $ima_cijena)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
            <p class="font-semibold mb-1">Troškovi se računaju iz cijena nabave na skladištu.</p>
            <p>
                @if(! $ima_skladiste)
                    Dodajte skladište i upišite nabavu sredstava i gnojiva s cijenom.
                @else
                    Na skladištu još nema nabave s upisanom cijenom. Uz nabavu upišite "Cijena ukupno (€)".
                @endif
                AgroLog tada sam izračuna koliko vas po parceli i hektaru stoji zaštita i gnojidba.
            </p>
            <a href="{{ route('skladiste') }}" class="inline-block mt-3 btn-primary">Otvori skladište</a>
        </div>
    @endif

    {{-- brojke --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <div class="rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Nabava {{ $oznaka }}</p>
            <p class="text-3xl font-bold text-gray-900 leading-tight">{{ $eur($nabavaUkupno, 0) }}</p>
            <p class="text-xs text-gray-500">{{ $nabava->count() }} {{ $nabava->count() === 1 ? 'ulaz' : 'ulaza' }} s cijenom</p>
        </div>
        <div class="rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Utrošeno na parcelama</p>
            <p class="text-3xl font-bold text-gray-900 leading-tight">{{ $eur($utroseno, 0) }}</p>
            <p class="text-xs text-gray-500">sredstva, gnojiva, radovi i troškovi vezani uz kulturu</p>
        </div>
        <div class="rounded-xl border border-gray-200 p-4">
            <p class="text-sm text-gray-500">Prosječno po hektaru</p>
            <p class="text-3xl font-bold text-gray-900 leading-tight">{{ $poHa !== null ? $eur($poHa, 0) : '—' }}</p>
            <p class="text-xs text-gray-500">utrošeno / površina kultura s troškom</p>
        </div>
        <a href="#mehanizacija-troskovi" class="rounded-xl border border-gray-200 p-4 hover:bg-gray-50 transition">
            <p class="text-sm text-gray-500">Trošak mehanizacije</p>
            <p class="text-3xl font-bold text-gray-900 leading-tight">{{ $eur($meh['ukupno'], 0) }}</p>
            <p class="text-xs text-gray-500">servisi, registracije, osiguranja</p>
        </a>
        <a href="#opci-troskovi" class="rounded-xl border border-gray-200 p-4 hover:bg-gray-50 transition">
            <p class="text-sm text-gray-500">Opći troškovi</p>
            <p class="text-3xl font-bold text-gray-900 leading-tight">{{ $eur($opci_nevezano, 0) }}</p>
            <p class="text-xs text-gray-500">vožnja, zakup, osiguranje… za cijelo gospodarstvo</p>
        </a>
        <div class="rounded-xl border border-green-200 bg-green-50 p-4">
            <p class="text-sm font-medium text-green-800">Sveukupni trošak</p>
            <p class="text-3xl font-bold text-green-900 leading-tight">{{ $eur($sveukupno, 0) }}</p>
            <p class="text-xs text-green-800/80">parcele + mehanizacija + opći troškovi</p>
        </div>
    </div>

    {{-- nabava po mjesecima --}}
    <div class="rounded-xl border border-gray-200 p-4 bg-white">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <h4 class="font-semibold text-gray-800">Nabava po mjesecima</h4>
            <div class="flex items-center gap-4 text-sm text-gray-600" aria-hidden="true">
                @foreach(['gnojivo', 'szb'] as $v)
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm" style="background: {{ $boje[$v] }}"></span>{{ $nazivi[$v] }}</span>
                @endforeach
            </div>
        </div>
        @if($nabavaUkupno <= 0)
            <p class="text-sm text-gray-500 py-10 text-center">U ovom razdoblju nema nabave s cijenom.</p>
        @else
            <div class="flex gap-2" role="img" aria-label="Nabava po mjesecima: zaštita bilja {{ $eur($poVrsti['szb'], 0) }}, gnojivo {{ $eur($poVrsti['gnojivo'], 0) }}">
                <div class="relative w-14 h-44 text-[11px] text-gray-400 shrink-0">
                    @for($v = 0; $v <= $vrh; $v += $korak)
                        <span class="absolute right-0 translate-y-1/2 leading-none" style="bottom: {{ $v / $vrh * 100 }}%">{{ $eur($v, 0) }}</span>
                    @endfor
                </div>
                <div class="flex-1 min-w-0">
                    <div class="relative h-44">
                        @for($v = 0; $v <= $vrh; $v += $korak)
                            <div class="absolute inset-x-0 border-t {{ $v === 0 ? 'border-gray-300' : 'border-gray-100' }}" style="bottom: {{ $v / $vrh * 100 }}%"></div>
                        @endfor
                        <div class="absolute inset-0 flex items-end">
                            @foreach($mjeseci as $i => $m)
                                @php $zbroj = $m['szb'] + $m['gnojivo']; @endphp
                                <div class="group relative flex-1 h-full flex flex-col justify-end items-center">
                                    @if($zbroj > 0)
                                        @if($i === $najveci)<span class="text-[11px] font-semibold text-gray-700 mb-0.5 whitespace-nowrap">{{ $eur($zbroj, 0) }}</span>@endif
                                        <div class="w-3/5 max-w-[28px] flex flex-col" style="height: {{ $zbroj / $vrh * 100 }}%">
                                            @if($m['szb'] > 0)
                                                <div class="w-full {{ $m['gnojivo'] > 0 ? 'border-b-2 border-white' : '' }} rounded-t" style="flex: {{ $m['szb'] }} 1 0; background: {{ $boje['szb'] }}"></div>
                                            @endif
                                            @if($m['gnojivo'] > 0)
                                                <div class="w-full {{ $m['szb'] > 0 ? '' : 'rounded-t' }}" style="flex: {{ $m['gnojivo'] }} 1 0; background: {{ $boje['gnojivo'] }}"></div>
                                            @endif
                                        </div>
                                    @endif
                                    <div class="pointer-events-none absolute bottom-full mb-1 left-1/2 -translate-x-1/2 z-10 hidden group-hover:block whitespace-nowrap rounded-lg bg-gray-900 text-white text-xs px-3 py-2 shadow-lg">
                                        <div class="font-semibold mb-1">{{ $mj[$m['mjesec']->month - 1] }} {{ $m['mjesec']->format('Y.') }}</div>
                                        <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-sm" style="background: {{ $boje['szb'] }}"></span>Zaštita bilja: {{ $eur($m['szb']) }}</div>
                                        <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-sm" style="background: {{ $boje['gnojivo'] }}"></span>Gnojivo: {{ $eur($m['gnojivo']) }}</div>
                                        <div class="text-gray-300 mt-1">Ukupno: {{ $eur($zbroj) }}</div>
                                    </div>
                                    <div class="absolute inset-0 group-hover:bg-gray-500/5 rounded"></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
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
                    <thead><tr class="text-gray-500 text-left"><th class="py-1">Mjesec</th><th class="text-right">Zaštita bilja</th><th class="text-right">Gnojivo</th><th class="text-right">Ukupno</th></tr></thead>
                    <tbody class="text-gray-700">
                        @foreach($mjeseci as $m)
                            <tr class="border-t border-gray-100"><td class="py-1">{{ $mj[$m['mjesec']->month - 1] }} {{ $m['mjesec']->format('Y.') }}</td><td class="text-right">{{ $eur($m['szb']) }}</td><td class="text-right">{{ $eur($m['gnojivo']) }}</td><td class="text-right font-medium">{{ $eur($m['szb'] + $m['gnojivo']) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </details>
        @endif
    </div>

    {{-- nabava po vrsti --}}
    @if($nabavaUkupno > 0)
        <div class="rounded-xl border border-gray-200 p-4 bg-white">
            <h4 class="font-semibold text-gray-800 mb-4">Nabava po vrsti</h4>
            <div class="space-y-4">
                @foreach(collect($poVrsti)->sortDesc() as $v => $iznos)
                    @php $udio = $nabavaUkupno > 0 ? $iznos / $nabavaUkupno * 100 : 0; @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-800">{{ $nazivi[$v] }}</span>
                            <span class="text-gray-800">{{ $eur($iznos, 0) }} <span class="text-gray-400 ml-2">{{ round($udio) }} %</span></span>
                        </div>
                        <div class="h-2.5 rounded-full bg-gray-100 overflow-hidden"><div class="h-full rounded-full" style="width: {{ $udio }}%; background: {{ $boje[$v] }}"></div></div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- trošak po parceli ili po kulturi --}}
    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
        <div class="px-4 pt-4 pb-2 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h4 class="font-semibold text-gray-800">Trošak po {{ $grupa === 'kultura' ? 'kulturi' : 'parceli' }}</h4>
                <p class="text-xs text-gray-500">
                    @if($grupa === 'kultura')
                        Ista kultura sa svih parcela zajedno; € / ha na hektare te kulture.
                    @else
                        Sve kulture na parceli zajedno; € / ha na površinu parcele.
                    @endif
                    Utrošak = doza × površina po prosječnoj nabavnoj cijeni. Radovi i ostalo = ostali radovi i opći troškovi vezani uz kulturu.
                </p>
            </div>
            <div class="inline-flex shrink-0 rounded-lg border border-gray-200 bg-gray-50 p-0.5 text-sm" role="group" aria-label="Trošak po">
                @foreach(['parcela' => 'Parcela', 'kultura' => 'Kultura'] as $k => $v)
                    <button wire:click="grupiraj('{{ $k }}')" aria-pressed="{{ $grupa === $k ? 'true' : 'false' }}"
                            class="px-4 py-1 rounded-md transition {{ $grupa === $k ? 'bg-green-700 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">{{ $v }}</button>
                @endforeach
            </div>
        </div>
        @if($grupe->isEmpty())
            <p class="text-sm text-gray-500 px-4 pb-6 pt-2">U ovom razdoblju nema utroška s poznatom cijenom.</p>
        @else
            <div class="overflow-x-auto">
            <table class="w-full text-sm mob-kartice mob-troskovi">
                <thead>
                    <tr class="text-xs text-gray-500 uppercase bg-gray-50">
                        <th class="px-4 py-2 text-left">{{ $grupa === 'kultura' ? 'Kultura' : 'Parcela' }}</th>
                        <th class="px-4 py-2 text-left">{{ $grupa === 'kultura' ? 'Parcele' : 'Kulture' }}</th>
                        <th class="px-4 py-2 text-right">ha</th>
                        <th class="px-4 py-2 text-right">Zaštita bilja</th>
                        <th class="px-4 py-2 text-right">Gnojivo</th>
                        <th class="px-4 py-2 text-right">Radovi i ostalo</th>
                        <th class="px-4 py-2 text-right">Trošak</th>
                        <th class="px-4 py-2 text-right">€ / ha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($grupe as $r)
                        <tr>
                            <td data-label="{{ $grupa === 'kultura' ? 'Kultura' : 'Parcela' }}" class="px-4 py-2 font-medium text-gray-800">{{ $r['naziv'] }}</td>
                            <td data-label="{{ $grupa === 'kultura' ? 'Parcele' : 'Kulture' }}" class="px-4 py-2 text-gray-600">{{ $r['opis'] }}</td>
                            <td data-label="ha" class="px-4 py-2 text-right text-gray-600">{{ number_format($r['ha'], 2, ',', '.') }}</td>
                            <td data-label="Zaštita bilja" class="px-4 py-2 text-right text-gray-700">{{ $eur($r['szb']) }}</td>
                            <td data-label="Gnojivo" class="px-4 py-2 text-right text-gray-700">{{ $eur($r['gnojivo']) }}</td>
                            <td data-label="Radovi i ostalo" class="px-4 py-2 text-right text-gray-700">{{ $eur($r['radovi']) }}</td>
                            <td data-label="Trošak" class="px-4 py-2 text-right text-gray-900">{{ $eur($r['ukupno']) }}</td>
                            <td data-label="€ / ha" class="px-4 py-2 text-right font-semibold text-gray-900">{{ $r['po_ha'] !== null ? $eur($r['po_ha']) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 font-semibold text-gray-900">
                        <td class="mob-puna px-4 py-2" colspan="2">Ukupno</td>
                        <td data-label="ha" class="px-4 py-2 text-right">{{ number_format($haGrupe, 2, ',', '.') }}</td>
                        <td data-label="Zaštita bilja" class="px-4 py-2 text-right">{{ $eur($grupe->sum('szb')) }}</td>
                        <td data-label="Gnojivo" class="px-4 py-2 text-right">{{ $eur($grupe->sum('gnojivo')) }}</td>
                        <td data-label="Radovi i ostalo" class="px-4 py-2 text-right">{{ $eur($grupe->sum('radovi')) }}</td>
                        <td data-label="Trošak" class="px-4 py-2 text-right">{{ $eur($utroseno) }}</td>
                        <td data-label="€ / ha" class="px-4 py-2 text-right">{{ $poHaGrupe !== null ? $eur($poHaGrupe) : '—' }}</td>
                    </tr>
                </tfoot>
            </table>
            </div>

            {{-- graf: trošak po parceli / kulturi, vodoravne trake (zaštita + gnojivo), € / ha na kraju --}}
            @php
                $najvise = max(0.01, (float) $grupe->max('ukupno'));
            @endphp
            <div class="px-4 pt-4 pb-4 border-t border-gray-100">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                    <h5 class="text-sm font-semibold text-gray-700">Trošak po {{ $grupa === 'kultura' ? 'kulturi' : 'parceli' }}</h5>
                    <div class="flex items-center gap-4 text-xs text-gray-600" aria-hidden="true">
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm" style="background: {{ $boje['szb'] }}"></span>Zaštita bilja</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm" style="background: {{ $boje['gnojivo'] }}"></span>Gnojivo</span>
                        @if($grupe->sum('radovi') > 0)<span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm" style="background: {{ $boje['radovi'] }}"></span>Radovi i ostalo</span>@endif
                    </div>
                </div>
                <div class="space-y-2" role="img" aria-label="Trošak po {{ $grupa === 'kultura' ? 'kulturi' : 'parceli' }}">
                    @foreach($grupe as $r)
                        <div class="group relative grid grid-cols-[6.5rem_1fr_auto] sm:grid-cols-[9rem_1fr_9rem] items-center gap-3 text-sm">
                            <span class="truncate text-gray-800" title="{{ $r['opis'] }}">{{ $r['naziv'] }}</span>
                            <div class="h-5 flex rounded-r overflow-hidden" style="width: {{ max(1, $r['ukupno'] / $najvise * 100) }}%">
                                @if($r['szb'] > 0)<div class="h-full {{ $r['gnojivo'] > 0 || $r['radovi'] > 0 ? 'border-r-2 border-white' : '' }}" style="flex: {{ $r['szb'] }} 1 0; background: {{ $boje['szb'] }}"></div>@endif
                                @if($r['gnojivo'] > 0)<div class="h-full {{ $r['radovi'] > 0 ? 'border-r-2 border-white' : '' }}" style="flex: {{ $r['gnojivo'] }} 1 0; background: {{ $boje['gnojivo'] }}"></div>@endif
                                @if($r['radovi'] > 0)<div class="h-full" style="flex: {{ $r['radovi'] }} 1 0; background: {{ $boje['radovi'] }}"></div>@endif
                            </div>
                            <span class="text-right whitespace-nowrap"><span class="font-medium text-gray-900">{{ $eur($r['ukupno'], 0) }}</span> <span class="text-xs text-gray-500">· {{ $r['po_ha'] !== null ? $eur($r['po_ha'], 0) . '/ha' : '—' }}</span></span>
                            <div class="pointer-events-none absolute left-28 sm:left-40 bottom-full mb-1 z-10 hidden group-hover:block whitespace-nowrap rounded-lg bg-gray-900 text-white text-xs px-3 py-2 shadow-lg">
                                <div class="font-semibold mb-1">{{ $r['naziv'] }} <span class="font-normal text-gray-300">{{ $r['opis'] }}</span></div>
                                <div>Zaštita bilja: {{ $eur($r['szb']) }}</div>
                                <div>Gnojivo: {{ $eur($r['gnojivo']) }}</div>
                                @if($r['radovi'] > 0)<div>Radovi i ostalo: {{ $eur($r['radovi']) }}</div>@endif
                                <div class="text-gray-300 mt-1">{{ $eur($r['ukupno']) }} · {{ number_format($r['ha'], 2, ',', '.') }} ha · {{ $r['po_ha'] !== null ? $eur($r['po_ha']) . '/ha' : '—' }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        @if($bez_cijene)
            <p class="text-xs text-amber-800 bg-amber-50 px-4 py-2 border-t border-amber-100">{{ $bez_cijene }} {{ $bez_cijene === 1 ? 'zapis troši artikl' : 'zapisa troše artikle' }} bez upisane cijene, pa {{ $bez_cijene === 1 ? 'nije uračunat' : 'nisu uračunati' }}. Cijenu dopunite uz nabavu na skladištu.</p>
        @endif
    </div>

    {{-- prihod i dobit (prinos × cijena + potpore − trošak) --}}
    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
        <div class="px-4 pt-4 pb-2 flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h4 class="font-semibold text-gray-800">Prihod i dobit po {{ $grupa === 'kultura' ? 'kulturi' : 'parceli' }}</h4>
                <p class="text-xs text-gray-500">Prihod = prinos × površina × cijena + potpore. Trošak = sredstva, gnojiva i radovi iz evidencije + opći troškovi vezani uz kulturu + sjeme i ostali troškovi upisani uz kulturu.</p>
            </div>
            @php $ukDobit = $dobit->whereNotNull('dobit')->sum('dobit'); @endphp
            @if($dobit->whereNotNull('dobit')->isNotEmpty())
                <span class="shrink-0 text-sm font-semibold px-3 py-1 rounded-full {{ $ukDobit >= 0 ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-700' }}">Ukupno: {{ $ukDobit >= 0 ? '+' : '−' }}{{ $eur(abs($ukDobit), 0) }}</span>
            @endif
        </div>
        @if($dobit->isEmpty() || $dobit->whereNotNull('prihod')->isEmpty())
            <div class="px-4 pb-5 pt-2 text-sm text-gray-600">
                Upišite prinos i cijenu uz kulturu: <a href="{{ route('parcele') }}" class="text-green-700 underline">Parcele &amp; Kulture</a> → gumb <strong>Prinos</strong> uz kulturu.
                AgroLog onda izračuna prihod, dobit i dobit po hektaru.
            </div>
        @else
            <div class="overflow-x-auto">
            <table class="w-full text-sm mob-kartice mob-troskovi">
                <thead>
                    <tr class="text-xs text-gray-500 uppercase bg-gray-50">
                        <th class="px-4 py-2 text-left">{{ $grupa === 'kultura' ? 'Kultura' : 'Parcela' }}</th>
                        <th class="px-4 py-2 text-left">{{ $grupa === 'kultura' ? 'Parcele' : 'Kulture' }}</th>
                        <th class="px-4 py-2 text-right">ha</th>
                        <th class="px-4 py-2 text-right">Prihod</th>
                        <th class="px-4 py-2 text-right">Trošak</th>
                        <th class="px-4 py-2 text-right">Dobit</th>
                        <th class="px-4 py-2 text-right">Dobit / ha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($dobit as $r)
                        <tr>
                            <td data-label="{{ $grupa === 'kultura' ? 'Kultura' : 'Parcela' }}" class="px-4 py-2 font-medium text-gray-800">{{ $r['naziv'] }}</td>
                            <td data-label="{{ $grupa === 'kultura' ? 'Parcele' : 'Kulture' }}" class="px-4 py-2 text-gray-600">{{ $r['opis'] }}@if($r['bez_prihoda'] && $r['prihod'] !== null)<span class="text-xs text-amber-700"> · {{ $r['bez_prihoda'] }} bez prinosa</span>@endif</td>
                            <td data-label="ha" class="px-4 py-2 text-right text-gray-600">{{ number_format($r['ha'], 2, ',', '.') }}</td>
                            <td data-label="Prihod" class="px-4 py-2 text-right text-gray-700">{{ $r['prihod'] !== null ? $eur($r['prihod']) : '—' }}</td>
                            <td data-label="Trošak" class="px-4 py-2 text-right text-gray-700">{{ $eur($r['trosak']) }}</td>
                            <td data-label="Dobit" class="px-4 py-2 text-right font-semibold {{ $r['dobit'] === null ? 'text-gray-400' : ($r['dobit'] >= 0 ? 'text-green-800' : 'text-red-700') }}">
                                {{ $r['dobit'] === null ? 'upišite prinos' : ($r['dobit'] >= 0 ? '+' : '−') . $eur(abs($r['dobit'])) }}</td>
                            <td data-label="Dobit / ha" class="px-4 py-2 text-right font-semibold {{ $r['dobit_ha'] === null ? 'text-gray-400' : ($r['dobit_ha'] >= 0 ? 'text-green-800' : 'text-red-700') }}">
                                {{ $r['dobit_ha'] === null ? '—' : ($r['dobit_ha'] >= 0 ? '+' : '−') . $eur(abs($r['dobit_ha'])) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            @if($dobit->sum('berbe_bez_cijene') > 0)
                <p class="px-4 pt-3 text-xs text-amber-800">Berbi bez cijene: {{ $dobit->sum('berbe_bez_cijene') }}. Nisu uračunate u prihod, cijenu možete dopisati na stranici <a href="{{ route('berba') }}" class="underline">Berba</a>.</p>
            @endif

            {{-- graf: dobit po hektaru, trake lijevo (gubitak) i desno (dobit) od nule --}}
            @php
                $sDobiti = $dobit->whereNotNull('dobit_ha');
                $najvise = max(1, (float) $sDobiti->max(fn ($r) => abs($r['dobit_ha'])));
                $imaGubitak = $sDobiti->contains(fn ($r) => $r['dobit_ha'] < 0);
            @endphp
            @if($sDobiti->isNotEmpty())
                <div class="px-4 pt-4 pb-4 border-t border-gray-100">
                    <h5 class="text-sm font-semibold text-gray-700 mb-3">Dobit po hektaru</h5>
                    <div class="space-y-2" role="img" aria-label="Dobit po hektaru po {{ $grupa === 'kultura' ? 'kulturi' : 'parceli' }}">
                        @foreach($sDobiti as $r)
                            @php $udio = abs($r['dobit_ha']) / $najvise * 100; @endphp
                            <div class="grid grid-cols-[6.5rem_1fr_auto] sm:grid-cols-[9rem_1fr_7rem] items-center gap-3 text-sm">
                                <span class="truncate text-gray-800" title="{{ $r['opis'] }}">{{ $r['naziv'] }}</span>
                                <div class="flex h-5 {{ $imaGubitak ? '' : '' }}">
                                    @if($imaGubitak)
                                        <div class="w-1/2 flex justify-end border-r border-gray-300">
                                            @if($r['dobit_ha'] < 0)<div class="h-full rounded-l bg-red-600/80" style="width: {{ max(1, $udio) }}%"></div>@endif
                                        </div>
                                        <div class="w-1/2">
                                            @if($r['dobit_ha'] >= 0)<div class="h-full rounded-r bg-green-600" style="width: {{ max(1, $udio) }}%"></div>@endif
                                        </div>
                                    @else
                                        <div class="h-full rounded-r bg-green-600" style="width: {{ max(1, $udio) }}%"></div>
                                    @endif
                                </div>
                                <span class="text-right whitespace-nowrap font-medium {{ $r['dobit_ha'] >= 0 ? 'text-green-800' : 'text-red-700' }}">{{ $r['dobit_ha'] >= 0 ? '+' : '−' }}{{ $eur(abs($r['dobit_ha']), 0) }}/ha</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-3 text-xs text-gray-500">Mehanizacija (registracije, servisi) i opći troškovi bez kulture nisu raspodijeljeni po kulturama i nisu uključeni u dobit po hektaru.</p>
                </div>
            @endif
        @endif
    </div>
    {{-- utrošak po sredstvu / gnojivu --}}
    @if($artikli->isNotEmpty())
        <details class="rounded-xl border border-gray-200 bg-white p-4">
            <summary class="cursor-pointer font-semibold text-gray-800">Utrošak po sredstvu i gnojivu</summary>
            <table class="mt-3 w-full text-sm">
                <tbody class="divide-y divide-gray-100">
                    @foreach($artikli as $a)
                        <tr>
                            <td class="py-1.5"><span class="inline-block w-2 h-2 rounded-sm mr-1.5" style="background: {{ $boje[$a['vrsta']] }}"></span>{{ $a['naziv'] }}</td>
                            <td class="py-1.5 text-right text-gray-600">{{ \App\Support\Skladiste::broj($a['kolicina'], 2) }} {{ $a['jedinica'] }}</td>
                            <td class="py-1.5 text-right font-medium">{{ $eur($a['iznos']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </details>
    @endif
{{-- mehanizacija: servisi i rokovi s iznosom --}}
    <div id="mehanizacija-troskovi" class="rounded-xl border border-gray-200 bg-white overflow-hidden scroll-mt-4">
        <div class="px-4 pt-4 pb-2 flex flex-wrap items-start justify-between gap-2">
            <div>
                <h4 class="font-semibold text-gray-800">Mehanizacija</h4>
                <p class="text-xs text-gray-500">Servisi s troškom i iznosi uz rokove (registracija, osiguranje, tehnički, pregled), na datum plaćanja.</p>
            </div>
            <a href="{{ route('mehanizacija') }}" class="text-sm text-green-700 underline underline-offset-2 whitespace-nowrap">Otvori mehanizaciju →</a>
        </div>
        @if($meh['stavke']->isEmpty())
            <p class="text-sm text-gray-500 px-4 pb-6 pt-2">
                U ovom razdoblju nema troškova mehanizacije. Iznos upišite uz servis ("Trošak") ili uz rok ("Iznos") u
                <a href="{{ route('mehanizacija') }}" class="text-green-700 underline">Mehanizaciji</a>.
            </p>
        @else
            <div class="overflow-x-auto">
            <table class="w-full text-sm mob-kartice mob-troskovi">
                <thead>
                    <tr class="text-xs text-gray-500 uppercase bg-gray-50">
                        <th class="px-4 py-2 text-left">Stroj / vozilo</th>
                        <th class="px-4 py-2 text-right">Servisi</th>
                        <th class="px-4 py-2 text-right">Registracija, osiguranje, pregledi</th>
                        <th class="px-4 py-2 text-right">Ukupno</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($meh['po_stroju'] as $r)
                        <tr>
                            <td data-label="Stroj / vozilo" class="mob-puna px-4 py-2 font-medium text-gray-800">{{ $r['stroj'] }}</td>
                            <td data-label="Servisi" class="px-4 py-2 text-right text-gray-700">{{ $eur($r['servisi']) }}</td>
                            <td data-label="Rokovi" class="px-4 py-2 text-right text-gray-700">{{ $eur($r['rokovi']) }}</td>
                            <td data-label="Ukupno" class="px-4 py-2 text-right font-semibold text-gray-900">{{ $eur($r['ukupno']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 font-semibold text-gray-900">
                        <td class="mob-puna px-4 py-2">Ukupno</td>
                        <td data-label="Servisi" class="px-4 py-2 text-right">{{ $eur($meh['po_stroju']->sum('servisi')) }}</td>
                        <td data-label="Rokovi" class="px-4 py-2 text-right">{{ $eur($meh['po_stroju']->sum('rokovi')) }}</td>
                        <td data-label="Ukupno" class="px-4 py-2 text-right">{{ $eur($meh['ukupno']) }}</td>
                    </tr>
                </tfoot>
            </table>
            </div>
            <details class="border-t border-gray-100 px-4 py-3">
                <summary class="cursor-pointer text-sm text-gray-600">Sve stavke ({{ $meh['stavke']->count() }})</summary>
                <table class="mt-2 w-full text-sm">
                    <tbody class="divide-y divide-gray-100">
                        @foreach($meh['stavke'] as $st)
                            <tr>
                                <td class="py-1.5 pr-3 text-gray-500 whitespace-nowrap">{{ $st['datum']->format('d.m.Y.') }}</td>
                                <td class="py-1.5 pr-3 text-gray-800">{{ $st['stroj'] }}</td>
                                <td class="py-1.5 pr-3 text-gray-600">{{ $st['opis'] }}</td>
                                <td class="py-1.5 text-right font-medium whitespace-nowrap">{{ $eur($st['iznos']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </details>
        @endif
    </div>
</div>
