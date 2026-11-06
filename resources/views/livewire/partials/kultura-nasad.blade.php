{{-- Trajni nasad u formi kulture: voćnjak, vinograd, maslinik --}}
<div class="w-full flex flex-wrap items-end gap-3 rounded-md bg-white/70 px-3 py-2 ring-1 ring-gray-200">
    <label class="flex items-center gap-2 pb-1.5 text-sm text-gray-700">
        <input type="checkbox" wire:model.live="kulturaForm.trajni" class="rounded">
        Trajni nasad <span class="text-xs text-gray-500">voćnjak, vinograd, maslinik: ostaje na parceli svake godine</span>
    </label>
    @if($kulturaForm['trajni'] ?? false)
        <label class="block text-xs text-gray-600">Sorta <span class="text-gray-400">neob.</span>
            <input type="text" wire:model="kulturaForm.sorta" placeholder="npr. Idared" class="form-input w-40 mt-1 block">
            @error('kulturaForm.sorta')<span class="text-red-500">{{ $message }}</span>@enderror
        </label>
        <label class="block text-xs text-gray-600">Godina sadnje <span class="text-gray-400">neob.</span>
            <input type="number" wire:model="kulturaForm.godina_sadnje" min="1900" max="2100" class="form-input w-24 mt-1 block">
            @error('kulturaForm.godina_sadnje')<span class="text-red-500">{{ $message }}</span>@enderror
        </label>
        <label class="block text-xs text-gray-600">Broj stabala <span class="text-gray-400">neob.</span>
            <input type="number" wire:model="kulturaForm.broj_stabala" min="0" class="form-input w-28 mt-1 block">
            @error('kulturaForm.broj_stabala')<span class="text-red-500">{{ $message }}</span>@enderror
        </label>
    @endif
</div>
