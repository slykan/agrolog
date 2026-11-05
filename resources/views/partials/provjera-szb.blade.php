{{-- Provjera prskanja prema registraciji u FIS-u (App\Support\ProvjeraSzb): ✓ / ⚠ / i, i način primjene iz registracije --}}
@if($provjera)
    <div class="mt-1.5 rounded-md border {{ collect($provjera['stavke'])->contains('razina', 'upozorenje') ? 'border-amber-300 bg-amber-50' : 'border-green-200 bg-green-50' }} px-2 py-1.5 text-[11px] leading-snug space-y-0.5">
        <p class="font-semibold text-gray-600">Provjera prema registru FIS-a</p>
        @foreach($provjera['stavke'] as $st)
            <p class="flex gap-1 {{ ['ok' => 'text-green-800', 'upozorenje' => 'text-amber-800 font-medium', 'info' => 'text-gray-700'][$st['razina']] }}">
                <span aria-hidden="true">{{ ['ok' => '✓', 'upozorenje' => '⚠', 'info' => 'ℹ'][$st['razina']] }}</span><span>{{ $st['tekst'] }}</span>
            </p>
        @endforeach
        @if(! empty($provjera['primjene']))
            <details class="pt-0.5">
                <summary class="cursor-pointer text-green-800">Doza i način primjene za {{ $provjera['kultura_fis'] }}</summary>
                <ul class="mt-1 space-y-1 text-gray-700">
                    @foreach($provjera['primjene'] as $pr)
                        <li><strong>{{ $pr['organizam'] }}</strong>@if($pr['max']) · najviše {{ $pr['max'] }}×@endif
                            @if($pr['uporaba'])<br>{{ \Illuminate\Support\Str::limit($pr['uporaba'], 260) }}@endif
                            @if($pr['napomena'])<br><span class="text-gray-500">{{ \Illuminate\Support\Str::limit($pr['napomena'], 200) }}</span>@endif
                        </li>
                    @endforeach
                </ul>
                <p class="mt-1 text-gray-500">Iz registracije u FIS-u; mjerodavna je etiketa sredstva.</p>
            </details>
        @endif
    </div>
@endif
