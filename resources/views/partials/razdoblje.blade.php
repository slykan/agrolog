{{-- Izbor razdoblja (Livewire komponenta s traitom App\Livewire\Concerns\Razdoblje) --}}
<div class="flex flex-col items-start sm:items-end gap-2">
    <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-0.5 text-sm" role="group" aria-label="Razdoblje">
        @foreach(['godina' => 'Ova godina', 'prosla' => 'Prošla', '12mj' => '12 mj.', 'oddo' => 'Od–do'] as $k => $v)
            <button wire:click="postavi('{{ $k }}')" aria-pressed="{{ $razdoblje === $k ? 'true' : 'false' }}"
                    class="px-3 py-1 rounded-md transition {{ $razdoblje === $k ? 'bg-green-700 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">{{ $v }}</button>
        @endforeach
    </div>
    @if($razdoblje === 'oddo')
        <div class="flex items-center gap-1 text-sm">
            <input type="date" wire:model.live="od" class="form-input py-0.5">
            <span class="text-gray-400">–</span>
            <input type="date" wire:model.live="do" class="form-input py-0.5">
        </div>
    @endif
</div>
