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
                    <td class="p-1 border border-gray-200" colspan="{{ count(array_intersect(['arkod','povrsina','kultura'], $visibleColumns)) }}"
                        x-data="{
                            search: '',
                            kulture: @json($this->kulturaOptions),
                            get filtered() {
                                if (!this.search) return this.kulture;
                                const s = this.search.toLowerCase();
                                return this.kulture.filter(k => k.arkod.toLowerCase().includes(s) || k.naziv.toLowerCase().includes(s));
                            }
                        }">
                        <div class="flex gap-1 mb-1">
                            <input type="text" x-model="search" @input.debounce.300ms="$wire.set('formKulturaSearch', search)" placeholder="Traži po ARKOD-u ili kulturi..." class="form-input flex-1">
                            <button x-show="search && filtered.length" @click="$wire.saveMultiple()" class="btn-xs-green whitespace-nowrap">
                                Dodaj sve (<span x-text="filtered.length"></span>)
                            </button>
                        </div>
                        <select wire:model="form.kultura_id" class="form-input w-full" size="4">
                            <option value="">-- odaberi jednu --</option>
                            <template x-for="k in filtered" :key="k.id">
                                <option :value="k.id" x-text="`${k.arkod} — ${k.naziv} (${k.povrsina} ha)`"></option>
                            </template>
                        </select>
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
                        <button wire:click="$set('showForm', false)" class="btn-xs-gray">Odustani</button>
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
                            <button wire:click="startEdit({{ $row->id }})" class="btn-xs-blue">Uredi</button>
                            <button wire:click="deleteRow({{ $row->id }})" wire:confirm="Sigurno obrisati ovaj zapis?" class="btn-xs-red">&#10005;</button>
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
