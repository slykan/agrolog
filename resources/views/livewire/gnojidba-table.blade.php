<div>
    @if(session('success'))
        <div class="mb-3 p-2 bg-green-100 text-green-800 rounded text-sm">{{ session('success') }}</div>
    @endif

    {{-- Zaglavlje obrasca --}}
    <table class="w-full border-collapse text-sm mb-4 form-header-table">
        <tr>
            <td colspan="2" class="border border-gray-400 text-center font-bold py-2 uppercase tracking-wide text-base">
                Evidencija korištenja gnojiva {{ date('Y') }}. godina
            </td>
        </tr>
        <tr>
            <td class="border border-gray-400 px-3 py-1 font-bold uppercase w-48">Naziv gospodarstva</td>
            <td class="border border-gray-400 px-3 py-1">{{ auth()->user()->naziv_gospodarstva }}</td>
        </tr>
        <tr>
            <td class="border border-gray-400 px-3 py-1 font-bold uppercase">MIPG</td>
            <td class="border border-gray-400 px-3 py-1">{{ auth()->user()->mipg }}</td>
        </tr>
    </table>

    {{-- Akcije (samo ekran) --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4 no-print">
        <div class="flex gap-2 no-print">
            <button wire:click="addRow" class="btn-primary">+ Dodaj</button>
            <button onclick="window.print()" class="btn-secondary">&#128424; Print / PDF</button>
        </div>
    </div>

    {{-- Odabir vidljivih stupaca + search --}}
    <div class="no-print flex flex-wrap gap-3 mb-3 text-sm items-center bg-gray-50 p-2 rounded">
        <span class="font-semibold text-gray-600 mr-1">Stupci:</span>
        @foreach(['arkod' => 'ARKOD', 'povrsina' => 'Površina', 'kultura' => 'Kultura', 'datum' => 'Datum', 'gnojivo' => 'Tip gnojiva', 'kolicina' => 'Kol. kg/ha'] as $key => $label)
            <label class="flex items-center gap-1 cursor-pointer select-none">
                <input type="checkbox" wire:click="toggleColumn('{{ $key }}')" @checked(in_array($key, $visibleColumns)) class="rounded">
                {{ $label }}
            </label>
        @endforeach
        <div class="ml-auto">
            <input type="text" wire:model.live="search" placeholder="Pretraži..." class="border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm agrolog-table">
            <thead>
                <tr class="bg-gray-100 text-gray-700 text-xs uppercase tracking-wide">
                    <th class="border border-gray-300 px-2 py-2 text-center w-8">Rb.</th>
                    @if(in_array('arkod', $visibleColumns))
                    <th class="border border-gray-300 px-3 py-2 text-left">
                        ARKOD Parcela
                        <input type="text" wire:model.live="filters.arkod" class="filter-input no-print mt-1 block w-full" placeholder="filter...">
                    </th>
                    @endif
                    @if(in_array('povrsina', $visibleColumns))
                    <th class="border border-gray-300 px-3 py-2 text-right">Površina/ha</th>
                    @endif
                    @if(in_array('kultura', $visibleColumns))
                    <th class="border border-gray-300 px-3 py-2 text-left">
                        Kultura
                        <input type="text" wire:model.live="filters.kultura" class="filter-input no-print mt-1 block w-full" placeholder="filter...">
                    </th>
                    @endif
                    @if(in_array('datum', $visibleColumns))
                    <th class="border border-gray-300 px-3 py-2 text-left">
                        Datum
                        <input type="date" wire:model.live="filters.datum" class="filter-input no-print mt-1 block w-full">
                    </th>
                    @endif
                    @if(in_array('gnojivo', $visibleColumns))
                    <th class="border border-gray-300 px-3 py-2 text-left">
                        Tip gnojiva (naziv)
                        <input type="text" wire:model.live="filters.gnojivo" class="filter-input no-print mt-1 block w-full" placeholder="filter...">
                    </th>
                    @endif
                    @if(in_array('kolicina', $visibleColumns))
                    <th class="border border-gray-300 px-3 py-2 text-right">Količina kg/ha</th>
                    @endif
                    <th class="border border-gray-300 px-3 py-2 no-print text-center">Akcije</th>
                </tr>
            </thead>
            <tbody>
                {{-- Forma za novi red --}}
                @if($showForm)
                <tr class="bg-yellow-50 no-print">
                    <td class="p-1 border border-gray-200 text-center text-gray-400">*</td>
                    @if(in_array('arkod', $visibleColumns) || in_array('povrsina', $visibleColumns) || in_array('kultura', $visibleColumns))
                    <td class="p-1 border border-gray-200" colspan="{{ count(array_intersect(['arkod','povrsina','kultura'], $visibleColumns)) }}">
                        <div class="space-y-1">
                            <button type="button" wire:click="openKulturaPicker" class="form-input w-full bg-white flex items-center justify-between gap-2 text-left">
                                <span>
                                    @if($this->selectedKultura)
                                        {{ $this->selectedKultura->arkod_broj }} — {{ $this->selectedKultura->naziv }} ({{ $this->selectedKultura->posadjena_povrsina_ha }} ha)
                                    @else
                                        <span class="text-gray-400">-- odaberi jednu --</span>
                                    @endif
                                </span>
                                <span class="text-xs text-gray-500 shrink-0">Traži</span>
                            </button>

                            @if($showKulturaPicker)
                                <div class="flex gap-1">
                                    <input type="text" wire:model.live.debounce.500ms="formKulturaSearch" placeholder="Traži po ARKOD-u ili kulturi..." class="form-input flex-1" autofocus>
                                    @if($formKulturaSearch)
                                    <button wire:click="saveMultiple" class="btn-xs-green whitespace-nowrap">
                                        Dodaj sve ({{ $this->filteredKulture->count() }})
                                    </button>
                                    @endif
                                </div>
                                <div class="max-h-48 overflow-y-auto border border-gray-200 bg-white rounded shadow-sm">
                                    @forelse($this->filteredKulture as $k)
                                        <button type="button" wire:key="gno-opt-{{ $k->id }}" wire:click="selectKultura({{ $k->id }})" class="block w-full text-left px-2 py-1 text-sm hover:bg-gray-50">
                                            {{ $k->arkod_broj }} — {{ $k->naziv }} ({{ $k->posadjena_povrsina_ha }} ha)
                                        </button>
                                    @empty
                                        <div class="px-2 py-2 text-sm text-gray-400">Nema rezultata.</div>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                        @error('form.kultura_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        @error('formKulturaSearch')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </td>
                    @endif
                    @if(in_array('datum', $visibleColumns))
                    <td class="p-1 border border-gray-200">
                        <input type="date" wire:model="form.datum" class="form-input w-full">
                        @error('form.datum')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </td>
                    @endif
                    @if(in_array('gnojivo', $visibleColumns))
                    <td class="p-1 border border-gray-200">
                        <input type="text" wire:model="form.tip_gnojiva" placeholder="npr. KAN 27%N" class="form-input w-full" list="tipovi-gnojiva">
                        <datalist id="tipovi-gnojiva">
                            @foreach($this->tipoviGnojiva as $tip)
                                <option value="{{ $tip }}">
                            @endforeach
                        </datalist>
                        @error('form.tip_gnojiva')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </td>
                    @endif
                    @if(in_array('kolicina', $visibleColumns))
                    <td class="p-1 border border-gray-200">
                        <input type="number" wire:model="form.kolicina_kg_ha" step="0.01" placeholder="250" class="form-input w-full">
                        @error('form.kolicina_kg_ha')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </td>
                    @endif
                    <td class="p-1 border border-gray-200 whitespace-nowrap">
                        <button wire:click="saveNew" class="btn-xs-green">Spremi</button>
                        @if($formKulturaSearch)
                        <button wire:click="saveMultiple" class="btn-xs-green" title="Dodaj sve filtrirane kulture kao zasebne zapise">
                            Dodaj sve ({{ $this->filteredKulture->count() }})
                        </button>
                        @endif
                        <button type="button" wire:click="cancelAdd" class="btn-xs-gray">Odustani</button>
                    </td>
                </tr>
                @endif

                {{-- Postojeći redovi --}}
                @forelse($this->gnojidbe as $row)
                <tr wire:key="gnojidba-{{ $row->id }}" class="border-b border-gray-200 hover:bg-gray-50 @if($editingId === $row->id) bg-blue-50 @endif">
                    @if($editingId === $row->id)
                        {{-- Edit mode --}}
                        <td class="px-2 py-2 border border-gray-200 text-center text-gray-400">*</td>
                        @if(in_array('arkod', $visibleColumns) || in_array('povrsina', $visibleColumns) || in_array('kultura', $visibleColumns))
                        <td class="p-1 border border-gray-200" colspan="{{ count(array_intersect(['arkod','povrsina','kultura'], $visibleColumns)) }}">
                            <select wire:model="editForm.kultura_id" class="form-input w-full">
                                @foreach($this->kulture as $k)
                                    <option value="{{ $k->id }}">{{ $k->parcela->arkod_broj }} — {{ $k->naziv }} ({{ $k->posadjena_povrsina_ha }} ha)</option>
                                @endforeach
                            </select>
                        </td>
                        @endif
                        @if(in_array('datum', $visibleColumns))
                        <td class="p-1 border border-gray-200">
                            <input type="date" wire:model="editForm.datum" class="form-input w-full">
                        </td>
                        @endif
                        @if(in_array('gnojivo', $visibleColumns))
                        <td class="p-1 border border-gray-200">
                            <input type="text" wire:model="editForm.tip_gnojiva" class="form-input w-full" list="tipovi-gnojiva">
                            <datalist id="tipovi-gnojiva">
                                @foreach($this->tipoviGnojiva as $tip)
                                    <option value="{{ $tip }}">
                                @endforeach
                            </datalist>
                        </td>
                        @endif
                        @if(in_array('kolicina', $visibleColumns))
                        <td class="p-1 border border-gray-200">
                            <input type="number" wire:model="editForm.kolicina_kg_ha" step="0.01" class="form-input w-full">
                        </td>
                        @endif
                        <td class="p-1 border border-gray-200 no-print whitespace-nowrap">
                            <button wire:click="saveEdit" class="btn-xs-green">Spremi</button>
                            <button wire:click="cancelEdit" class="btn-xs-gray">Odustani</button>
                        </td>
                    @else
                        {{-- View mode --}}
                        <td class="px-2 py-2 border border-gray-200 text-center">{{ $loop->iteration }}</td>
                        @if(in_array('arkod', $visibleColumns))<td class="px-3 py-2 border border-gray-200">{{ $row->arkod_broj }}</td>@endif
                        @if(in_array('povrsina', $visibleColumns))<td class="px-3 py-2 border border-gray-200 text-right">{{ number_format($row->posadjena_povrsina_ha, 2) }}</td>@endif
                        @if(in_array('kultura', $visibleColumns))<td class="px-3 py-2 border border-gray-200 uppercase">{{ $row->kultura_naziv }}</td>@endif
                        @if(in_array('datum', $visibleColumns))<td class="px-3 py-2 border border-gray-200">{{ $row->datum->format('d.m.Y') }}</td>@endif
                        @if(in_array('gnojivo', $visibleColumns))<td class="px-3 py-2 border border-gray-200">{{ $row->tip_gnojiva }}</td>@endif
                        @if(in_array('kolicina', $visibleColumns))<td class="px-3 py-2 border border-gray-200 text-right font-medium">{{ number_format($row->kolicina_kg_ha, 2) }}</td>@endif
                        <td class="px-2 py-2 border border-gray-200 no-print whitespace-nowrap text-center">
                            <button wire:click="startEdit({{ $row->id }})" wire:confirm="Urediti ovaj zapis?" class="btn-icon-green" title="Uredi">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6.586-6.586a2 2 0 112.828 2.828L11.828 13.828A2 2 0 0110 14H8v-2a2 2 0 01.586-1.414z"/>
                                </svg>
                            </button>
                            <button wire:click="copyRow({{ $row->id }})" wire:confirm="Kopirati ovaj zapis?" class="btn-icon-green" title="Kopiraj">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                            <button wire:click="deleteRow({{ $row->id }})" wire:confirm="Obrisati ovaj zapis?" class="btn-icon-red" title="Obriši">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-10 text-gray-400 italic">
                        Nema unesenih gnojidbi. Kliknite <strong>+ Dodaj</strong> za unos.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
