{{-- Polja koja vrijede samo za trajni nasad; običnoj kulturi umjesto njih ide godina --}}
<div wire:key="nasad-sorta">
    <label class="block text-xs text-gray-600 mb-1">Sorta <span class="text-gray-400">neob.</span></label>
    <input type="text" wire:model="kulturaForm.sorta" placeholder="npr. Idared" class="form-input w-40">
    @error('kulturaForm.sorta')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
</div>
<div wire:key="nasad-sadnja">
    <label class="block text-xs text-gray-600 mb-1">Godina sadnje <span class="text-gray-400">neob.</span></label>
    <input type="number" wire:model="kulturaForm.godina_sadnje" min="1900" max="2100" class="form-input w-28">
    @error('kulturaForm.godina_sadnje')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
</div>
<div wire:key="nasad-stabla">
    <label class="block text-xs text-gray-600 mb-1">Broj stabala <span class="text-gray-400">neob.</span></label>
    <input type="number" wire:model="kulturaForm.broj_stabala" min="0" class="form-input w-28">
    @error('kulturaForm.broj_stabala')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
</div>
