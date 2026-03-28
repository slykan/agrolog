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
                        <select wire:model="form.kultura_id" class="form-input w-full">
                            <option value="">-- odaberi (ARKOD — kultura) --</option>
                            @foreach($this->kulture as $k)
                                <option value="{{ $k->id }}">{{ $k->parcela->arkod_broj }} — {{ $k->naziv }} ({{ $k->posadjena_povrsina_ha }} ha)</option>
                            @endforeach
                        </select>
                        @error('form.kultura_id')<p class="text-red-500 text-xs">{{ $message }}</p>@enderror
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
                        <button wire:click="$set('showForm', false)" class="btn-xs-gray">Odustani</button>
                    </td>
                </tr>
                @endif

                @forelse($this->prskanja as $row)
                <tr class="border-b border-gray-200 hover:bg-gray-50 @if($editingId === $row->id) bg-blue-50 @endif">
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
                        <td class="p-1 border border-gray-200"><input type="text" wire:model="editForm.trgovacki_naziv_sredstva" class="form-input w-full" list="trgovacki-nazivi"></td>
                        <datalist id="trgovacki-nazivi">
                            @foreach($this->trgovackiNazivi as $naziv)
                                <option value="{{ $naziv }}">
                            @endforeach
                        </datalist>
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
                        @if(in_array('kolicina', $visibleColumns))<td class="px-2 py-2 border border-gray-200 text-right">{{ $row->kolicina_sredstva_l_ha }}</td>@endif
                        @if(in_array('vrijeme', $visibleColumns))<td class="px-2 py-2 border border-gray-200 text-center">{{ $row->vrijeme_od ? substr($row->vrijeme_od,0,5).'-'.substr($row->vrijeme_do,0,5) : '' }}</td>@endif
                        @if(in_array('voda', $visibleColumns))<td class="px-2 py-2 border border-gray-200 text-right">{{ $row->kolicina_vode_l_ha ? number_format($row->kolicina_vode_l_ha, 0) : '' }}</td>@endif
                        <td class="px-2 py-2 border border-gray-200 no-print whitespace-nowrap text-center">
                            <button wire:click="startEdit({{ $row->id }})" class="btn-xs-blue">Uredi</button>
                            <button wire:click="deleteRow({{ $row->id }})" wire:confirm="Sigurno obrisati?" class="btn-xs-red">&#10005;</button>
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
