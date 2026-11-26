{{-- Vlastiti naziv ARKOD parcele (ParcelaManager) — pokazuje se uz ARKOD broj svugdje u aplikaciji --}}
<div>
    <label class="block text-xs text-gray-600 mb-1">Naziv (vlastiti) <span class="text-gray-400">· iz ARKOD-a ili svoj</span></label>
    <input type="text" wire:key="naziv-{{ md5($parcelaForm['naziv'] ?? '') }}" wire:model="parcelaForm.naziv" maxlength="60" placeholder="npr. Iza kuće" class="form-input w-48">
    @error('parcelaForm.naziv')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
</div>
