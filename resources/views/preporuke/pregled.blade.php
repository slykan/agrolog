<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pregled prije slanja</h2>
    </x-slot>
    @php
        $gosp = $redovi->groupBy('vlasnik_id');
        $upozorenja = $redovi->filter(fn ($r) => collect($r['provjera'] ?? [])->contains('razina', 'upozorenje'))->count();
    @endphp
    <div class="py-3 sm:py-6">
        <div class="max-w-3xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-4 sm:p-6 space-y-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ \App\Models\Preporuka::VRSTE[$d['vrsta']] }}@if(! empty($d['sredstvo'])): {{ $d['sredstvo'] }}@endif</h3>
                    <p class="text-sm text-gray-600">
                        @if(! empty($d['doza']))Doza {{ str_replace('.', ',', rtrim(rtrim(number_format((float) $d['doza'], 3, '.', ''), '0'), '.')) }} {{ $d['jedinica'] ?? '' }} · @endif
                        @if(! empty($d['rok']))Rok {{ \Illuminate\Support\Carbon::parse($d['rok'])->format('d.m.Y.') }} · @endif
                        Gospodarstava: {{ $gosp->count() }}
                    </p>
                    @if(! empty($d['napomena']))<p class="mt-2 text-sm text-gray-800 whitespace-pre-line">{{ $d['napomena'] }}</p>@endif
                </div>

                @if($upozorenja)
                    <p class="rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-900"><strong>Provjerite prije slanja:</strong> registar FIS-a javlja upozorenja na parcelama: {{ $upozorenja }}. Gospodarstvo će ih vidjeti uz preporuku.</p>
                @endif

                <ul class="divide-y divide-gray-100 rounded-lg border border-gray-200">
                    @foreach($redovi as $r)
                        <li class="px-3 py-2 text-sm">
                            <b>{{ $r['gospodarstvo'] }}</b>
                            @if(! empty($r['kultura_naziv']))<span class="text-gray-500">{{ $r['kultura_naziv'] }} · {{ $r['parcela_oznaka'] }}</span>@endif
                            @foreach($r['provjera'] ?? [] as $s)
                                <p class="mt-0.5 text-xs {{ $s['razina'] === 'upozorenje' ? 'font-medium text-amber-800' : 'text-gray-500' }}">{{ $s['tekst'] }}</p>
                            @endforeach
                        </li>
                    @endforeach
                </ul>

                <form method="post" action="{{ route('klijenti.preporuke.posalji') }}" class="flex flex-wrap items-center gap-3">
                    @csrf
                    <input type="hidden" name="potvrda" value="1">
                    @foreach(['vrsta', 'sredstvo', 'doza', 'jedinica', 'rok', 'napomena'] as $polje)
                        @if(isset($d[$polje]))<input type="hidden" name="{{ $polje }}" value="{{ $d[$polje] }}">@endif
                    @endforeach
                    @foreach($d['kultura_ids'] ?? [] as $id)<input type="hidden" name="kultura_ids[]" value="{{ $id }}">@endforeach
                    @foreach($d['vlasnik_ids'] ?? [] as $id)<input type="hidden" name="vlasnik_ids[]" value="{{ $id }}">@endforeach
                    <x-primary-button>Pošalji</x-primary-button>
                    <a href="{{ route('klijenti.preporuke') }}" class="text-sm text-gray-600 underline hover:text-gray-900">Odustani</a>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
