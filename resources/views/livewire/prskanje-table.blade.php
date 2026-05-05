<div>
    @if(session('success'))
        <div class="mb-3 p-2 bg-green-100 text-green-800 rounded text-sm">{{ session('success') }}</div>
    @endif

    {{-- Zaglavlje obrasca --}}
    <table class="w-full border-collapse text-sm mb-4 form-header-table">
        <tr>
            <td colspan="2" class="border border-gray-400 text-center font-bold py-2 uppercase tracking-wide text-base">
                Evidencija o upotrebi sredstava za zaštitu bilja
            </td>
        </tr>
        <tr>
            <td colspan="2" class="border border-gray-400 px-3 py-1 text-sm">
                Ime i prezime korisnika, vlasnika bilja koja je provela tretiranje:
                <strong>{{ auth()->user()->name }}</strong>
                @if(auth()->user()->oib), OIB: <strong>{{ auth()->user()->oib }}</strong>@endif
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
        <div class="flex gap-2">
            <button wire:click="addRow" class="btn-primary">+ Dodaj</button>
            <button onclick="window.print()" class="btn-secondary">&#128424; Print / PDF</button>
        </div>
    </div>

    <div class="no-print flex flex-wrap gap-3 mb-3 text-sm items-center bg-gray-50 p-2 rounded">
        <span class="font-semibold text-gray-600 mr-1">Stupci:</span>
        @foreach(['datum' => 'Datum', 'arkod' => 'ARKOD', 'kultura' => 'Kultura', 'vel_povrsina' => 'Vel. površine', 'tret_povrsina' => 'Tretirana', 'sredstvo' => 'Sredstvo', 'kolicina' => 'Kol. L/ha', 'vrijeme' => 'Vrijeme', 'voda' => 'Voda L/ha'] as $key => $label)
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
                    @if(in_array('datum', $visibleColumns))
                    <th class="border border-gray-300 px-2 py-2 text-left">
                        Datum tretiranja
                        <input type="date" wire:model.live="filters.datum" class="filter-input no-print mt-1 block w-full">
                    </th>
                    @endif
                    @if(in_array('arkod', $visibleColumns))
                    <th class="border border-gray-300 px-2 py-2 text-left">
                        ARKOD broj
                        <input type="text" wire:model.live="filters.arkod" class="filter-input no-print mt-1 block w-full" placeholder="filter...">
                    </th>
                    @endif
                    @if(in_array('kultura', $visibleColumns))
                    <th class="border border-gray-300 px-2 py-2 text-left">
                        Biljni proizvod
                        <input type="text" wire:model.live="filters.kultura" class="filter-input no-print mt-1 block w-full" placeholder="filter...">
                    </th>
                    @endif
                    @if(in_array('vel_povrsina', $visibleColumns))
                    <th class="border border-gray-300 px-2 py-2 text-right">Veličina površine/ha</th>
                    @endif
                    @if(in_array('tret_povrsina', $visibleColumns))
                    <th class="border border-gray-300 px-2 py-2 text-right">Tretirana površina/ha</th>
                    @endif
                    @if(in_array('sredstvo', $visibleColumns))
                    <th class="border border-gray-300 px-2 py-2 text-left">
                        Trgovački naziv sredstva
                        <input type="text" wire:model.live="filters.sredstvo" class="filter-input no-print mt-1 block w-full" placeholder="filter...">
                    </th>
                    @endif
                    @if(in_array('kolicina', $visibleColumns))
                    <th class="border border-gray-300 px-2 py-2 text-right">Količina sredstva L/ha</th>
                    @endif
                    @if(in_array('vrijeme', $visibleColumns))
                    <th class="border border-gray-300 px-2 py-2 text-center">Vrijeme tretiranja od-do</th>
                    @endif
                    @if(in_array('voda', $visibleColumns))
                    <th class="border border-gray-300 px-2 py-2 text-right">Količina vode L/ha</th>
                    @endif
                    <th class="border border-gray-300 px-2 py-2 no-print text-center">Akcije</th>
                </tr>
            </thead>
            <tbody>
                @if($showForm)
                <tr class="bg-yellow-50 no-print">
                    <td class="p-1 border border-gray-200 text-center text-gray-400">*</td>
                    @if(in_array('datum', $visibleColumns))
                    <td class="p-1 border border-gray-200">
                        <input type="date" wire:model="form.datum_tretiranja" class="form-input w-full">
                        @error('form.datum_tretiranja')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror
                    </td>
                    @endif
                    @if(in_array('arkod', $visibleColumns) || in_array('kultura', $visibleColumns) || in_array('vel_povrsina', $visibleColumns))
                    <td class="p-1 border border-gray-200" colspan="{{ count(array_intersect(['arkod','kultura','vel_povrsina'], $visibleColumns)) }}">
                        <div class="space-y-1">
                            <div class="flex gap-1">
                                <div class="form-input w-full bg-white">
                                    @if($this->selectedKultura)
                                        {{ $this->selectedKultura->arkod_broj }} — {{ $this->selectedKultura->naziv }} ({{ $this->selectedKultura->posadjena_povrsina_ha }} ha)
                                    @else
                                        <span class="text-gray-400">-- odaberi jednu --</span>
                                    @endif
                                </div>
                                <button type="button" wire:click="openKulturaPicker" class="btn-xs-gray shrink-0">Traži</button>
                            </div>

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
                                        <button type="button" wire:key="prs-opt-{{ $k->id }}" wire:click="selectKultura({{ $k->id }})" class="block w-full text-left px-2 py-1 text-sm hover:bg-gray-50">
                                            {{ $k->arkod_broj }} — {{ $k->naziv }} ({{ $k->posadjena_povrsina_ha }} ha)
                                        </button>
                                    @empty
                                        <div class="px-2 py-2 text-sm text-gray-400">Nema rezultata.</div>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                        @error('form.kultura_id')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror
                        @error('formKulturaSearch')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror
                    </td>
                    @endif
                    @if(in_array('tret_povrsina', $visibleColumns))
                    <td class="p-1 border border-gray-200">
                        <input type="number" wire:model="form.tretirana_povrsina_ha" step="0.01" placeholder="1.60" class="form-input w-full">
                        @error('form.tretirana_povrsina_ha')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror
                    </td>
                    @endif
                    @if(in_array('sredstvo', $visibleColumns))
                    <td class="p-1 border border-gray-200">
                        <input type="text" wire:model="form.trgovacki_naziv_sredstva" placeholder="npr. Roundup" class="form-input w-full" list="trgovacki-nazivi">
                        <datalist id="trgovacki-nazivi">
                            @foreach($this->trgovackiNazivi as $naziv)
                                <option value="{{ $naziv }}">
                            @endforeach
                        </datalist>
                        @error('form.trgovacki_naziv_sredstva')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror
                    </td>
                    @endif
                    @if(in_array('kolicina', $visibleColumns))
                    <td class="p-1 border border-gray-200">
                        <input type="number" wire:model="form.kolicina_sredstva_l_ha" step="0.001" placeholder="2.5" class="form-input w-full">
                        @error('form.kolicina_sredstva_l_ha')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror
                    </td>
                    @endif
                    @if(in_array('vrijeme', $visibleColumns))
                    <td class="p-1 border border-gray-200">
                        <div class="flex gap-1 items-center">
                            <input type="time" wire:model="form.vrijeme_od" class="form-input flex-1" placeholder="10:00">
                            <span class="text-gray-400 text-xs">-</span>
                            <input type="time" wire:model="form.vrijeme_do" class="form-input flex-1" placeholder="16:00">
                        </div>
                    </td>
                    @endif
                    @if(in_array('voda', $visibleColumns))
                    <td class="p-1 border border-gray-200">
                        <input type="number" wire:model="form.kolicina_vode_l_ha" step="0.01" placeholder="200" class="form-input w-full">
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

                @forelse($this->prskanja as $row)
                <tr wire:key="prskanje-{{ $row->id }}" class="border-b border-gray-200 hover:bg-gray-50 @if($editingId === $row->id) bg-blue-50 @endif">
                    @if($editingId === $row->id)
                        <td class="px-2 py-2 border border-gray-200 text-center text-gray-400">*</td>
                        @if(in_array('datum', $visibleColumns))
                        <td class="p-1 border border-gray-200"><input type="date" wire:model="editForm.datum_tretiranja" class="form-input w-full"></td>
                        @endif
                        @if(in_array('arkod', $visibleColumns) || in_array('kultura', $visibleColumns) || in_array('vel_povrsina', $visibleColumns))
                        <td class="p-1 border border-gray-200" colspan="{{ count(array_intersect(['arkod','kultura','vel_povrsina'], $visibleColumns)) }}">
                            <select wire:model="editForm.kultura_id" class="form-input w-full">
                                @foreach($this->kulture as $k)
                                    <option value="{{ $k->id }}">{{ $k->parcela->arkod_broj }} — {{ $k->naziv }}</option>
                                @endforeach
                            </select>
                        </td>
                        @endif
                        @if(in_array('tret_povrsina', $visibleColumns))
                        <td class="p-1 border border-gray-200"><input type="number" wire:model="editForm.tretirana_povrsina_ha" step="0.01" class="form-input w-full"></td>
                        @endif
                        @if(in_array('sredstvo', $visibleColumns))
                        <td class="p-1 border border-gray-200">
                            <input type="text" wire:model="editForm.trgovacki_naziv_sredstva" class="form-input w-full" list="trgovacki-nazivi">
                        </td>
                        @endif
                        @if(in_array('kolicina', $visibleColumns))
                        <td class="p-1 border border-gray-200"><input type="number" wire:model="editForm.kolicina_sredstva_l_ha" step="0.001" class="form-input w-full"></td>
                        @endif
                        @if(in_array('vrijeme', $visibleColumns))
                        <td class="p-1 border border-gray-200">
                            <div class="flex gap-1 items-center">
                                <input type="time" wire:model="editForm.vrijeme_od" class="form-input flex-1">
                                <span>-</span>
                                <input type="time" wire:model="editForm.vrijeme_do" class="form-input flex-1">
                            </div>
                        </td>
                        @endif
                        @if(in_array('voda', $visibleColumns))
                        <td class="p-1 border border-gray-200"><input type="number" wire:model="editForm.kolicina_vode_l_ha" step="0.01" class="form-input w-full"></td>
                        @endif
                        <td class="p-1 border border-gray-200 no-print whitespace-nowrap">
                            <button wire:click="saveEdit" class="btn-xs-green">Spremi</button>
                            <button wire:click="cancelEdit" class="btn-xs-gray">Odustani</button>
                        </td>
                    @else
                        <td class="px-2 py-2 border border-gray-200 text-center">{{ $loop->iteration }}</td>
                        @if(in_array('datum', $visibleColumns))<td class="px-2 py-2 border border-gray-200">{{ $row->datum_tretiranja->format('d.m.Y') }}</td>@endif
                        @if(in_array('arkod', $visibleColumns))<td class="px-2 py-2 border border-gray-200">{{ $row->arkod_broj }}</td>@endif
                        @if(in_array('kultura', $visibleColumns))<td class="px-2 py-2 border border-gray-200 uppercase">{{ $row->kultura_naziv }}</td>@endif
                        @if(in_array('vel_povrsina', $visibleColumns))<td class="px-2 py-2 border border-gray-200 text-right">{{ number_format($row->posadjena_povrsina_ha, 2) }}</td>@endif
                        @if(in_array('tret_povrsina', $visibleColumns))<td class="px-2 py-2 border border-gray-200 text-right">{{ number_format($row->tretirana_povrsina_ha, 2) }}</td>@endif
                        @if(in_array('sredstvo', $visibleColumns))<td class="px-2 py-2 border border-gray-200">{{ $row->trgovacki_naziv_sredstva }}</td>@endif
                        @if(in_array('kolicina', $visibleColumns))<td class="px-2 py-2 border border-gray-200 text-right">{{ number_format($row->kolicina_sredstva_l_ha, 2) }}</td>@endif
                        @if(in_array('vrijeme', $visibleColumns))<td class="px-2 py-2 border border-gray-200 text-center">{{ $row->vrijeme_od ? substr($row->vrijeme_od,0,5).'-'.substr($row->vrijeme_do,0,5) : '' }}</td>@endif
                        @if(in_array('voda', $visibleColumns))<td class="px-2 py-2 border border-gray-200 text-right">{{ $row->kolicina_vode_l_ha ? number_format($row->kolicina_vode_l_ha, 2) : '' }}</td>@endif
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
                    <td colspan="12" class="text-center py-10 text-gray-400 italic">
                        Nema unesenih tretmana. Kliknite <strong>+ Dodaj</strong> za unos.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
