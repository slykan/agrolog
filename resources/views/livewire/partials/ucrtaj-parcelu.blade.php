{{-- Parcela bez ARKOD-a: naziv, površina i oblik na karti (ParcelaManager, resources/js/ucrtavanje.js) --}}
<div>
    <p class="mb-3 rounded border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
        Ručno ucrtana parcela služi za vašu evidenciju i nije službena ARKOD parcela. U ispisima i izvozima tako je i označena.
    </p>

    <div class="flex flex-wrap gap-3 items-start mb-3">
        <div>
            <label class="block text-xs text-gray-600 mb-1">Naziv parcele</label>
            <input type="text" wire:model="parcelaForm.arkod_broj" maxlength="60" placeholder="npr. Iza kuće" class="form-input w-56">
            @error('parcelaForm.arkod_broj')<p class="text-red-500 text-xs mt-1 max-w-xs">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-xs text-gray-600 mb-1">Površina (ha)</label>
            <input type="number" wire:key="povrsina-rucno-{{ $parcelaForm['povrsina_ha'] }}" wire:model="parcelaForm.povrsina_ha" step="0.01" placeholder="izračuna se" class="form-input w-28">
            @error('parcelaForm.povrsina_ha')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-wrap items-center gap-2 sm:mt-6">
            <button wire:click="saveParcela" class="btn-xs-green">Spremi</button>
            <button wire:click="cancelParcelaEdit" class="btn-xs-gray">Odustani</button>
            <button type="button" wire:click="imamArkodBroj" class="text-xs text-green-700 underline">Imam ARKOD broj</button>
        </div>
    </div>
    @error('rucniPoligon')<p class="text-red-600 text-sm mb-2">{{ $message }}</p>@enderror

    <div wire:ignore wire:key="ucrtaj-{{ $editParcelaId ?? 'nova' }}" x-data="ucrtavanjeParcele(@js($rucniPoligon), @js($this->okvirKarte), @js(config('services.arcgis.key')))">
        <p class="mb-2 text-sm text-gray-700">Nađite parcelu na karti pa redom kliknite ili dotaknite njene uglove. Površina se računa sama.</p>

        <div class="relative mb-2 flex flex-wrap gap-2">
            <input type="search" x-model="trazi" @keydown.enter.prevent="traziMjesto()" placeholder="Adresa ili mjesto, npr. Ferenca Kiša 15, Hrastin" class="form-input flex-1 min-w-[10rem]">
            <button type="button" @click="traziMjesto()" :disabled="trazim" class="btn-xs-blue" x-text="trazim ? 'Tražim…' : 'Traži'"></button>
            <button type="button" @click="mojaLokacija()" class="btn-xs-gray">Moja lokacija</button>
            <ul x-show="rezultati.length" x-cloak class="absolute left-0 top-full z-[900] mt-1 w-full max-w-md overflow-hidden rounded-lg bg-white text-sm shadow-lg ring-1 ring-gray-200">
                <template x-for="m in rezultati" :key="m.id">
                    <li>
                        <button type="button" @click="idiNa(m)" class="block w-full px-3 py-2 text-left hover:bg-green-50">
                            <b x-text="m.naziv"></b> <span class="text-gray-500" x-text="m.opis"></span>
                        </button>
                    </li>
                </template>
            </ul>
        </div>

        <div x-ref="karta" class="h-[60vh] min-h-[320px] max-h-[560px] rounded-lg ring-1 ring-gray-200 bg-gray-100"></div>

        <p x-show="poruka" x-cloak x-text="poruka" class="mt-2 text-sm text-amber-800" role="status"></p>

        <div class="mt-2 flex flex-wrap items-center gap-2">
            <span class="text-sm text-gray-600">Točaka: <b x-text="tocke.length"></b></span>
            <button type="button" @click="ponisti()" :disabled="! tocke.length" class="btn-xs-gray disabled:opacity-50">Poništi zadnju točku</button>
            <button type="button" @click="obrisi()" :disabled="! tocke.length" class="btn-xs-gray disabled:opacity-50">Kreni ispočetka</button>
            <button type="button" @click="tockaGdjeStojim()" class="btn-xs-blue" title="Za hodanje po rubu parcele s mobitelom">Dodaj točku gdje stojim</button>
        </div>
    </div>
</div>
