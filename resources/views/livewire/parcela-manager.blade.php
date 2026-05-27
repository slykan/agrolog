<div>
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-gray-800">Moje ARCOD parcele i kulture</h2>
        <button wire:click="$set('showParcelaForm', true)" class="btn-primary">+ Nova parcela</button>
    </div>

    {{-- Forma za parcelu --}}
    @if($showParcelaForm)
    <div class="bg-yellow-50 border border-yellow-200 rounded p-4 mb-4">
        <h3 class="font-semibold text-gray-700 mb-3">{{ $editParcelaId ? 'Uredi parcelu' : 'Nova parcela' }}</h3>
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-600 mb-1">ARKOD broj</label>
                <input type="text" wire:model="parcelaForm.arkod_broj" placeholder="npr. 1708275" class="form-input w-40">
                @error('parcelaForm.arkod_broj')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">Površina (ha)</label>
                <input type="number" wire:model="parcelaForm.povrsina_ha" step="0.01" placeholder="1.60" class="form-input w-28">
                @error('parcelaForm.povrsina_ha')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-2">
                <button wire:click="saveParcela" class="btn-xs-green">Spremi</button>
                <button wire:click="$set('showParcelaForm', false)" class="btn-xs-gray">Odustani</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Forma za kulturu --}}
    @if($showKulturaForm)
    <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-4">
        <h3 class="font-semibold text-gray-700 mb-3">{{ $editKulturaId ? 'Uredi kulturu' : 'Dodaj kulturu na parcelu' }}</h3>
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-600 mb-1">Kultura</label>
                <input type="text" wire:model="kulturaForm.naziv" placeholder="npr. Pšenica"
                       list="kulture-prijedlozi" autocomplete="off" class="form-input w-40">
                <datalist id="kulture-prijedlozi">
                    @foreach($this->postojeceKulture as $naziv)
                        <option value="{{ $naziv }}">
                    @endforeach
                </datalist>
                @error('kulturaForm.naziv')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">Posađena površina (ha)</label>
                <input type="number" wire:model="kulturaForm.posadjena_povrsina_ha" step="0.01" placeholder="1.60" class="form-input w-28">
                @error('kulturaForm.posadjena_povrsina_ha')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">Godina</label>
                <input type="number" wire:model="kulturaForm.godina" min="2000" max="2100" class="form-input w-24">
                @error('kulturaForm.godina')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-2">
                <button wire:click="saveKultura(true)" class="btn-xs-green">Spremi i nova</button>
                <button wire:click="saveKultura" class="btn-xs-blue">Završi</button>
                <button wire:click="$set('showKulturaForm', false)" class="btn-xs-gray">Odustani</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Lista parcela --}}
    <div class="space-y-3">
        @forelse($this->parcele as $parcela)
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            {{-- Parcela header --}}
            <div class="flex items-center justify-between bg-green-50 px-4 py-2">
                <div class="flex items-center gap-4">
                    <span class="font-bold text-green-800 text-lg">{{ $parcela->arkod_broj }}</span>
                    <span class="text-sm text-gray-600">{{ number_format($parcela->povrsina_ha, 2) }} ha</span>
                    <span class="text-xs text-gray-400">{{ $parcela->kulture->count() }} kultura</span>
                </div>
                <div class="flex gap-2">
                    <button wire:click="openKulturaForm({{ $parcela->id }})" class="btn-xs-blue">+ Kultura</button>
                    <button wire:click="editParcela({{ $parcela->id }})" class="btn-xs-gray">Uredi</button>
                    <button wire:click="deleteParcela({{ $parcela->id }})" wire:confirm="Obrisati parcelu i sve kulture?" class="btn-xs-red">&#10005;</button>
                </div>
            </div>

            {{-- Inline edit forma --}}
            @if($editParcelaId === $parcela->id)
            <div class="bg-yellow-50 border-t border-yellow-200 px-4 py-3">
                <div class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">ARKOD broj</label>
                        <input type="text" wire:model="parcelaForm.arkod_broj" placeholder="npr. 1708275" class="form-input w-40">
                        @error('parcelaForm.arkod_broj')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Površina (ha)</label>
                        <input type="number" wire:model="parcelaForm.povrsina_ha" step="0.01" placeholder="1.60" class="form-input w-28">
                        @error('parcelaForm.povrsina_ha')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="saveParcela" class="btn-xs-green">Spremi</button>
                        <button wire:click="cancelParcelaEdit" class="btn-xs-gray">Odustani</button>
                    </div>
                </div>
            </div>
            @endif

            {{-- Kulture na parceli --}}
            @if($parcela->kulture->isNotEmpty())
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <th class="px-4 py-1 text-left">Kultura</th>
                        <th class="px-4 py-1 text-right">Posađena površina (ha)</th>
                        <th class="px-4 py-1 text-center">Godina</th>
                        <th class="px-4 py-1 text-center">Gnojidbi</th>
                        <th class="px-4 py-1 text-center">Tretmana</th>
                        <th class="px-4 py-1 text-center">Akcije</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parcela->kulture as $kultura)
                    <tr class="border-t border-gray-100 hover:bg-gray-50">
                        <td class="px-4 py-2 font-medium uppercase">{{ $kultura->naziv }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($kultura->posadjena_povrsina_ha, 2) }}</td>
                        <td class="px-4 py-2 text-center">{{ $kultura->godina }}</td>
                        <td class="px-4 py-2 text-center text-gray-500">{{ $kultura->gnojidbe_count ?? $kultura->gnojidbe()->count() }}</td>
                        <td class="px-4 py-2 text-center text-gray-500">{{ $kultura->prskanja_count ?? $kultura->prskanja()->count() }}</td>
                        <td class="px-4 py-2 text-center whitespace-nowrap">
                            <button wire:click="editKultura({{ $kultura->id }})" class="btn-xs-blue">Uredi</button>
                            <button wire:click="deleteKultura({{ $kultura->id }})" wire:confirm="Obrisati kulturu i sve zapise?" class="btn-xs-red">&#10005;</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="px-4 py-3 text-sm text-gray-400 italic">Nema kultura na ovoj parceli. Dodajte klikom na + Kultura.</p>
            @endif
        </div>
        @empty
        <div class="text-center py-12 text-gray-400">
            <p class="text-lg">Nema dodanih parcela.</p>
            <p class="text-sm mt-1">Kliknite <strong>+ Nova parcela</strong> za početak.</p>
        </div>
        @endforelse
    </div>
</div>
