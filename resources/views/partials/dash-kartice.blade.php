{{-- Šest kartica na početnoj: jednobojna ikona, naslov i opis u redu, ispod brojka i mali graf --}}
@php
    $ikone = [
        'parcele' => 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7',
        'gnojidba' => 'M12 21c-4-3-7-7-7-11a7 7 0 0114 0c0 4-3 8-7 11zm0-11v6m0-6c-1.5-2-3.5-2.5-5-2m5 2c1.5-2 3.5-2.5 5-2',
        'prskanje' => 'M12 3c3 4 6 7.5 6 11a6 6 0 01-12 0c0-3.5 3-7 6-11z',
        'berba' => 'M12 8c-1-2.5-3-4-5.5-4 0 2.5 1.5 4.5 4 5M12 8c3-2 8.5-1 8.5 5 0 4.5-3.5 8-6 8-1.2 0-1.8-.5-2.500-.5s-1.300.5-2.500.5c-2.500 0-6-3.500-6-8 0-6 5.500-7 8.500-5z',
        'skladiste' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        'mehanizacija' => 'M5 17a2 2 0 104 0 2 2 0 00-4 0zm10 0a2 2 0 104 0 2 2 0 00-4 0M5 17H3v-4l2-5h7l2 5h3a2 2 0 012 2v2h-2M9 17h6M12 8V5',
        'troskovi' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    ];
@endphp
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach(\App\Support\DashKartice::za(auth()->user()) as $k)
        <a href="{{ route($k['ruta']) }}" class="{{ $loop->first ? 'lg:col-span-2' : '' }} {{ $loop->last ? 'sm:col-span-2' : '' }} flex flex-col bg-white shadow-sm rounded-xl p-5 hover:shadow-md transition group border border-transparent hover:border-green-200">
            <div class="flex items-start gap-3">
                <span class="w-11 h-11 shrink-0 rounded-xl bg-green-50 ring-1 ring-green-100 flex items-center justify-center text-green-700 group-hover:bg-green-100 transition">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikone[$k['ikona']] }}"/></svg>
                </span>
                <div class="min-w-0">
                    <h4 class="font-semibold text-gray-800 group-hover:text-green-700 transition">{{ $k['naslov'] }}</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">{{ $k['opis'] }}</p>
                </div>
            </div>

            <div class="mt-4 flex items-baseline justify-between gap-3">
                <span class="text-xl font-bold text-gray-900 whitespace-nowrap">{{ $k['brojka'] }}</span>
                <span class="text-xs text-gray-500 text-right">{{ $k['pod'] }}</span>
            </div>

            <div class="mt-3">
                @if($k['prazno'])
                    <p class="text-xs font-medium text-green-700">{{ $k['prazno'] }}</p>
                @elseif($k['graf'] === 'stupci')
                    <div class="flex items-end gap-0.5 h-12" role="img" aria-label="{{ $k['naslov'] }} po mjesecima">
                        @foreach($k['stupci'] as $s)
                            <span class="flex-1 h-full flex items-end" title="{{ $s['oznaka'] }}: {{ number_format($s['broj'], 0, ',', '.') }}{{ $k['jedinica'] ?? '' }}">
                                @if($s['broj'] > 0)
                                    <span class="w-full rounded-t" style="height: {{ max(8, round($s['broj'] / $k['vrh'] * 100)) }}%; background: {{ $k['boja'] }}"></span>
                                @else
                                    <span class="w-full h-0.5 bg-gray-200"></span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                    <div class="mt-1 flex justify-between text-[10px] text-gray-400">
                        <span>{{ $k['stupci'][0]['oznaka'] }}</span>
                        <span>{{ end($k['stupci'])['oznaka'] }}</span>
                    </div>
                @elseif($k['graf'] === 'kulture')
                    <div class="space-y-1.5">
                        @foreach($k['redovi'] as $r)
                            <div class="grid grid-cols-[5.5rem_1fr_3.5rem] items-center gap-2 text-xs" title="{{ $r['naziv'] }}: {{ number_format($r['ha'], 2, ',', '.') }} ha">
                                <span class="text-gray-600 truncate">{{ $r['naziv'] }}</span>
                                <span class="block h-2 rounded-full bg-green-600" style="width: {{ max(3, $r['posto']) }}%"></span>
                                <span class="text-right text-gray-700">{{ number_format($r['ha'], 1, ',', '.') }} ha</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    @php
                        $uk = max(array_sum(array_column($k['segmenti'], 'broj')), 0.01);
                        $vidljivi = array_filter($k['segmenti'], fn ($s) => $s['broj'] > 0);
                    @endphp
                    <div class="flex gap-0.5 h-2.5" role="img" aria-label="{{ $k['naslov'] }}: raspodjela">
                        @foreach($vidljivi as $s)
                            <span class="first:rounded-l-full last:rounded-r-full" style="width: {{ max(3, round($s['broj'] / $uk * 100, 1)) }}%; background: {{ $s['boja'] }}" title="{{ $s['naziv'] }}"></span>
                        @endforeach
                    </div>
                    <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-600">
                        @foreach($vidljivi as $s)
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full" style="background: {{ $s['boja'] }}"></span>
                                {{ $s['naziv'] }} {{ ! empty($k['novac']) ? number_format($s['broj'], 0, ',', '.') . ' €' : (int) $s['broj'] }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </a>
    @endforeach
</div>
