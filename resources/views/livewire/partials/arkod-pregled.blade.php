{{-- Pregled parcele iz javnog ARKOD-a nakon upisa broja (ParcelaManager::updatedParcelaFormArkodBroj) --}}
@if($arkodNadjen)
    <div class="mt-3 grid gap-3 md:grid-cols-[1fr_280px]">
        <div wire:ignore wire:key="arkod-pregled-{{ $arkodNadjen['id'] }}"
             x-data x-init="agroKarta.nacrtaj($el, @js($arkodNadjen['poligon']))"
             class="h-64 rounded-lg ring-1 ring-gray-200 bg-gray-100"></div>
        <div class="text-sm space-y-2">
            <p class="font-semibold text-green-800">Pronađeno u ARKOD-u — je li ovo vaša parcela?</p>
            <dl class="space-y-1">
                <div class="flex justify-between gap-3"><dt class="text-gray-500">ARKOD ID</dt><dd class="font-semibold">{{ $arkodNadjen['id'] }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-gray-500">Površina</dt><dd class="font-semibold">{{ number_format($arkodNadjen['povrsina_ha'], 2, ',', '.') }} ha</dd></div>
                @if($arkodNadjen['vrsta_uporabe'])<div class="flex justify-between gap-3"><dt class="text-gray-500">Uporaba</dt><dd>{{ $arkodNadjen['vrsta_uporabe'] }}</dd></div>@endif
                @if($arkodNadjen['naziv'])<div class="flex justify-between gap-3"><dt class="text-gray-500">Lokalni naziv</dt><dd>{{ $arkodNadjen['naziv'] }}</dd></div>@endif
            </dl>
            @if((float) ($parcelaForm['povrsina_ha'] ?: 0) !== (float) $arkodNadjen['povrsina_ha'])
                <button type="button" wire:click="preuzmiArkodPovrsinu" class="btn-xs-blue">Preuzmi površinu {{ number_format($arkodNadjen['povrsina_ha'], 2, ',', '.') }} ha</button>
            @endif
            @if($arkodNadjen['vodozastita'] || $arkodNadjen['natura2000'])
                <p class="rounded bg-amber-50 border border-amber-200 px-2 py-1 text-xs text-amber-800">
                    {{ $arkodNadjen['vodozastita'] ? 'Parcela je u zoni zaštite izvorišta (' . $arkodNadjen['vodozastita'] . ').' : '' }}
                    {{ $arkodNadjen['natura2000'] ? 'Parcela je u području Natura 2000.' : '' }}
                    Pazite na ograničenja pri gnojidbi i zaštiti bilja.
                </p>
            @endif
            <p class="text-xs text-gray-400">Izvor: ARKOD, Agencija za plaćanja (javni WMS).</p>
        </div>
    </div>
@elseif($arkodPoruka)
    <p class="mt-2 text-xs text-amber-700">{{ $arkodPoruka }}</p>
@endif
