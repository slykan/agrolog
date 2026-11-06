{{-- Nova kultura ili zamjena, unutar kartice parcele kojoj se dodaje --}}
@php
    $stara = $zamjenaZaId ? \App\Models\Kultura::with('parcela')->find($zamjenaZaId) : null;
@endphp
<div class="bg-blue-50 border-t border-blue-200 p-4">
    @if($stara)
        <h3 class="font-semibold text-gray-700 mb-1">Zamijeni kulturu na parceli {{ $stara->parcela?->arkod_broj }}</h3>
        <p class="text-sm text-gray-600 mb-3">
            <strong class="uppercase">{{ $stara->naziv }} ({{ $stara->godina }})</strong> bit će označena kao završena i
            ostaje u evidenciji sa svim zapisima gnojidbe i prskanja. Nova kultura dobiva svoju, praznu evidenciju.
        </p>
    @else
        <h3 class="font-semibold text-gray-700 mb-3">Dodaj kulturu na parcelu</h3>
    @endif
    @include('livewire.partials.kultura-nasad')
    <div class="flex flex-wrap gap-3 items-start">
        @if($stara)
        <div>
            <label class="block text-xs text-gray-600 mb-1">{{ $stara->naziv }} završena (žetva/berba)</label>
            <input type="date" wire:model="zavrsenaDatum" class="form-input w-40">
            @error('zavrsenaDatum')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        @endif
        <div>
            <label class="block text-xs text-gray-600 mb-1">Kultura</label>
            <input type="text" wire:model.live.blur="kulturaForm.naziv" placeholder="npr. Pšenica"
                   list="kulture-prijedlozi" autocomplete="off" class="form-input w-40">
            @error('kulturaForm.naziv')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-xs text-gray-600 mb-1" title="Naziv usjeva s Ministarstvova popisa — iz njega se uzima EPPO kod za evidenciju SZB">Usjev za evidenciju (EPPO)</label>
            <input type="text" wire:key="eppo-{{ $kulturaForm['eppo_naziv'] ?? '' }}" wire:model="kulturaForm.eppo_naziv" placeholder="prepozna se iz naziva"
                   list="eppo-popis" autocomplete="off" class="form-input w-56">
            @error('kulturaForm.eppo_naziv')<p class="text-red-500 text-xs mt-1 max-w-xs">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-xs text-gray-600 mb-1">Posađena površina (ha)</label>
            <input type="number" wire:key="ha-{{ $kulturaForm['posadjena_povrsina_ha'] ?? '' }}" wire:model="kulturaForm.posadjena_povrsina_ha" step="0.01" placeholder="1.60" class="form-input w-28">
            @if($this->slobodno !== null)<p class="text-[11px] text-gray-500 mt-1">slobodno {{ number_format($this->slobodno, 2, ',', '.') }} ha</p>@endif
            @error('kulturaForm.posadjena_povrsina_ha')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        @if($kulturaForm['trajni'] ?? false)
            @include('livewire.partials.kultura-nasad-polja')
        @else
        <div>
            <label class="block text-xs text-gray-600 mb-1">Godina</label>
            <input type="number" wire:model.live.blur="kulturaForm.godina" min="2000" max="2100" class="form-input w-24">
            @error('kulturaForm.godina')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        @endif
        <div class="w-full flex gap-2">
            @if($stara)
                <button wire:click="saveKultura" class="btn-xs-green">Spremi novu kulturu</button>
            @else
                <button wire:click="saveKultura(true)" class="btn-xs-green">Spremi i nova</button>
                <button wire:click="saveKultura" class="btn-xs-blue">Završi</button>
            @endif
            <button wire:click="closeKulturaForm" class="btn-xs-gray">Odustani</button>
        </div>
    </div>
</div>
