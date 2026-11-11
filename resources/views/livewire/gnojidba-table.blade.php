<div>
    <div wire:key="plan-traka">
    @if($planOpis)
        <div class="mb-3 rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-800">
            <strong>Unos iz kalendara</strong> (planirano: {{ $planOpis }}). Upišite ono što je stvarno napravljeno; spremanjem se plan zatvara.
        </div>
    @endif
    </div>
    <div wire:key="preporuka-traka">
    @if($preporukaOpis)
        <div class="mb-3 rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-sm text-sky-900">
            <strong>Unos iz preporuke</strong> ({{ $preporukaOpis }}). Provjerite datum, količinu i ostale podatke prije spremanja jer evidencija mora pokazivati ono što je stvarno napravljeno.
        </div>
    @endif
    </div>
    @if(session('success'))
        <div class="mb-3 p-2 bg-green-100 text-green-800 rounded text-sm">{{ session('success') }}</div>
    @endif

    {{-- Akcije (samo ekran) --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4 no-print">
        <div class="grid grid-cols-2 sm:flex gap-2 w-full sm:w-auto no-print">
            <button wire:click="addRow" class="btn-primary">+ Dodaj</button>
            @if(\App\Support\Uredjaj::jeMobilni())
                {{-- mobitel i aplikacija: PDF datoteka umjesto ispisa iz preglednika (za druga razdoblja: Izvještaji) --}}
                <a href="{{ route('izvjestaji.pdf', ['vrsta' => 'gnojidba']) }}" class="btn-secondary text-center" title="Evidencija za {{ date('Y') }}. kao PDF datoteka">&#11015; PDF {{ date('Y') }}.</a>
            @else
                <button onclick="window.print()" class="btn-secondary">&#128424; Print / PDF</button>
            @endif
        </div>
    </div>

    {{-- Odabir vidljivih stupaca + search --}}
    <div class="no-print mob-stupci flex flex-wrap gap-3 mb-3 text-sm items-center bg-gray-50 p-2 rounded">
        <span class="font-semibold text-gray-600 mr-1">Stupci:</span>
        @foreach(['datum' => 'Datum', 'arkod' => 'ARKOD', 'kultura' => 'Kultura', 'povrsina' => 'Površina', 'gnojivo' => 'Tip gnojiva', 'kolicina' => 'Kol. kg/ha', 'hraniva' => 'kg N/ha'] as $key => $label)
            <label class="flex items-center gap-1 cursor-pointer select-none">
                <input type="checkbox" wire:click="toggleColumn('{{ $key }}')" @checked(in_array($key, $visibleColumns)) class="rounded">
                {{ $label }}
            </label>
        @endforeach
        <div class="ml-auto">
            <input type="text" wire:model.live="search" placeholder="Pretraži..." class="border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
        </div>
    </div>

    {{-- prijedlozi: gnojiva koja je korisnik već upisivao + uobičajena gnojiva sa sastavom --}}
    <datalist id="tipovi-gnojiva">
        @foreach($this->tipoviGnojiva as $tip)
            <option value="{{ $tip }}">
        @endforeach
    </datalist>

    {{-- Dušik u godini: po kulturi i prosjek gospodarstva iz stajskog gnoja (granica 170 kg N/ha) --}}
    @php
        $bilanca = $this->bilancaDusika;
    @endphp
    @if($bilanca['redovi']->isNotEmpty())
        <div class="no-print mb-4 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm">
            <div class="flex flex-wrap items-baseline justify-between gap-2 mb-2">
                <span class="font-semibold text-gray-700">Dušik {{ $bilanca['godina'] }}. (kg N/ha)</span>
                @if($bilanca['prosjek'] !== null)
                    <span class="{{ $bilanca['prosjek'] > $bilanca['granica'] ? 'text-red-700 font-semibold' : 'text-gray-500' }}">
                        Stajski gnoj, prosjek gospodarstva: {{ number_format($bilanca['prosjek'], 1, ',', '.') }} / {{ $bilanca['granica'] }} kg N/ha
                    </span>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($bilanca['redovi'] as $r)
                    @php
                        $preko = (float) $r->n_stajski > $bilanca['granica'];
                    @endphp
                    <span class="rounded-md border px-2 py-1 {{ $preko ? 'border-red-300 bg-red-50 text-red-800' : 'border-gray-200 bg-white text-gray-700' }}"
                          title="{{ $preko ? 'Iz stajskog gnoja preko ' . $bilanca['granica'] . ' kg N/ha' : '' }}">
                        <b>{{ $r->arkod_broj }}</b> {{ $r->naziv }}:
                        {{ number_format((float) $r->n_ukupno, 0, ',', '.') }}
                        @if((float) $r->n_stajski > 0)<span class="text-xs">(stajski {{ number_format((float) $r->n_stajski, 0, ',', '.') }})</span>@endif
                    </span>
                @endforeach
            </div>
            <p class="mt-2 text-xs text-gray-400">Izračun iz količine i udjela dušika u gnojivu. Za stajski gnoj i gnojovku koriste se prosječni udjeli ako ne upišete svoje iz analize.</p>
        </div>
    @endif

    {{-- zaglavlje obrasca odmah iznad tablice, kao kod prskanja --}}
    {{-- Zaglavlje obrasca --}}
    <table class="w-full border-collapse text-sm form-header-table">
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

    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm agrolog-table mob-kartice">
            <thead>
                <tr class="bg-gray-100 text-gray-700 text-xs uppercase tracking-wide">
                    <th class="border border-gray-300 px-2 py-2 text-center w-8">Rb.</th>
                    @if(in_array('datum', $visibleColumns))
                    <th class="border border-gray-300 px-3 py-2 text-left">
                        Datum
                        <input type="date" wire:model.live="filters.datum" class="filter-input no-print mt-1 block w-full">
                    </th>
                    @endif
                    @if(in_array('arkod', $visibleColumns))
                    <th class="border border-gray-300 px-3 py-2 text-left">
                        ARKOD Parcela
                        <input type="text" wire:model.live="filters.arkod" class="filter-input no-print mt-1 block w-full" placeholder="filter...">
                    </th>
                    @endif
                    @if(in_array('kultura', $visibleColumns))
                    <th class="border border-gray-300 px-3 py-2 text-left">
                        Kultura
                        <input type="text" wire:model.live="filters.kultura" class="filter-input no-print mt-1 block w-full" placeholder="filter...">
                    </th>
                    @endif
                    @if(in_array('povrsina', $visibleColumns))
                    <th class="border border-gray-300 px-3 py-2 text-right">Površina/ha</th>
                    @endif
                    @if(in_array('gnojivo', $visibleColumns))
                    <th class="border border-gray-300 px-3 py-2 text-left min-w-[14rem]">
                        Tip gnojiva (naziv)
                        <input type="text" wire:model.live="filters.gnojivo" class="filter-input no-print mt-1 block w-full" placeholder="filter...">
                    </th>
                    @endif
                    @if(in_array('kolicina', $visibleColumns))
                    <th class="border border-gray-300 px-3 py-2 text-right">Količina kg/ha</th>
                    @endif
                    @if(in_array('hraniva', $visibleColumns))
                    <th class="border border-gray-300 px-3 py-2 text-right" title="Udjeli N-P₂O₅-K₂O (%) i kg dušika po hektaru">N-P-K % / kg N/ha</th>
                    @endif
                    <th class="border border-gray-300 px-3 py-2 no-print text-center">Akcije</th>
                </tr>
            </thead>
            <tbody>
                {{-- Forma za novi red --}}
                @if($showForm)
                <tr class="bg-yellow-50 no-print">
                    <td class="mob-skrij p-1 border border-gray-200 text-center text-gray-400">*</td>
                    @if(in_array('datum', $visibleColumns))
                    <td data-label="Datum" class="p-1 border border-gray-200">
                        <input type="date" wire:model="form.datum" class="form-input w-full">
                        @error('form.datum')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </td>
                    @endif
                    @if(in_array('arkod', $visibleColumns) || in_array('povrsina', $visibleColumns) || in_array('kultura', $visibleColumns))
                    <td data-label="Parcela i kultura" class="mob-puna p-1 border border-gray-200" colspan="{{ count(array_intersect(['arkod','povrsina','kultura'], $visibleColumns)) }}">
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
                                    <button type="button" wire:click="saveMultiple" class="btn-xs-green whitespace-nowrap">
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
                        @if($errors->any())
                            <div class="mt-1 rounded border border-red-200 bg-red-50 px-2 py-1 text-xs text-red-700">
                                {{ $errors->first() }}
                            </div>
                        @endif
                    </td>
                    @endif
                    @if(in_array('gnojivo', $visibleColumns))
                    <td data-label="Gnojivo" class="mob-puna p-1 border border-gray-200">
                        <input type="text" wire:model.live.blur="form.tip_gnojiva" placeholder="npr. KAN 27% N" class="form-input w-full" list="tipovi-gnojiva" autocomplete="off">
                        <div class="mt-1 flex flex-wrap items-center gap-1 text-xs text-gray-500">
                            <span class="whitespace-nowrap" title="udio dušika, fosfora (P₂O₅) i kalija (K₂O) u %">N-P-K %</span>
                            <input type="number" step="0.1" wire:key="n-form-{{ $form['n_posto'] ?? '' }}" wire:model.live.blur="form.n_posto" placeholder="N" class="form-input w-14 px-1">
                            <input type="number" step="0.1" wire:key="p-form-{{ $form['p_posto'] ?? '' }}" wire:model="form.p_posto" placeholder="P" class="form-input w-14 px-1">
                            <input type="number" step="0.1" wire:key="k-form-{{ $form['k_posto'] ?? '' }}" wire:model="form.k_posto" placeholder="K" class="form-input w-14 px-1">
                            <label class="ml-1 flex items-center gap-1 whitespace-nowrap" title="Stajski gnoj, gnojovka, gnojnica — broji se u granicu 170 kg N/ha">
                                <input type="checkbox" wire:key="o-form-{{ (int) ($form['organsko'] ?? 0) }}" wire:model="form.organsko" class="rounded"> stajski
                            </label>
                        </div>
                        <p class="mt-1 text-[11px] leading-snug text-gray-400">N-P-K = % dušika, fosfora i kalija. Upiše se sam kad odaberete gnojivo s popisa; za drugo gnojivo prepišite tri broja s vreće (npr. 15-15-15) ili ostavite prazno.</p>
                        @error('form.tip_gnojiva')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        @if($this->skladista->count() > 1)<label class="mt-1 flex items-center gap-1 text-xs text-gray-500"><span class="whitespace-nowrap">Iz skladišta</span><select wire:model.live="form.skladiste_id" class="form-input flex-1 py-0.5 text-xs">@foreach($this->skladista as $skl)<option value="{{ $skl->id }}">{{ $skl->naziv }}</option>@endforeach</select></label>@endif
                        @if($sk = \App\Support\Skladiste::napomena(auth()->user(), 'gnojivo', $form['tip_gnojiva'] ?? null, $form['kolicina_kg_ha'] ?? null, $this->selectedKultura?->posadjena_povrsina_ha, $form['skladiste_id'] ?? null))<p class="text-xs mt-1 {{ $sk['minus'] ? 'text-red-600' : 'text-green-700' }}">{{ $sk['tekst'] }}</p>@endif
                    </td>
                    @endif
                    @if(in_array('kolicina', $visibleColumns))
                    <td data-label="Količina kg/ha" class="p-1 border border-gray-200">
                        <input type="number" wire:model.live.blur="form.kolicina_kg_ha" step="0.01" placeholder="250" class="form-input w-full">
                        @error('form.kolicina_kg_ha')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </td>
                    @endif
                    @if(in_array('hraniva', $visibleColumns))
                    <td data-label="N-P-K / kg N/ha" class="p-1 border border-gray-200 text-right text-gray-600 whitespace-nowrap">
                        {{ ($x = \App\Support\Gnojiva::kgHraniva($form['kolicina_kg_ha'] ?? null, $form['n_posto'] ?? null)) !== null ? number_format($x, 1, ',', '.') . ' kg N' : '—' }}
                    </td>
                    @endif
                    <td class="mob-puna mob-akcije p-1 border border-gray-200 whitespace-nowrap">
                        <button type="button" wire:click="saveNew" wire:loading.attr="disabled" class="btn-xs-green">Spremi</button>
                        @if($formKulturaSearch)
                        <button type="button" wire:click="saveMultiple" wire:loading.attr="disabled" class="btn-xs-green" title="Dodaj sve filtrirane kulture kao zasebne zapise">
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
                        <td class="mob-skrij px-2 py-2 border border-gray-200 text-center text-gray-400">*</td>
                        @if(in_array('datum', $visibleColumns))
                        <td data-label="Datum" class="p-1 border border-gray-200">
                            <input type="date" wire:model="editForm.datum" class="form-input w-full">
                        </td>
                        @endif
                        @if(in_array('arkod', $visibleColumns) || in_array('povrsina', $visibleColumns) || in_array('kultura', $visibleColumns))
                        <td data-label="Parcela i kultura" class="mob-puna p-1 border border-gray-200" colspan="{{ count(array_intersect(['arkod','povrsina','kultura'], $visibleColumns)) }}">
                            <select wire:model="editForm.kultura_id" class="form-input w-full">
                                @foreach($this->kulture as $k)
                                    <option value="{{ $k->id }}">{{ $k->arkod_broj }} — {{ $k->naziv }} {{ $k->godina }} ({{ $k->posadjena_povrsina_ha }} ha){{ $k->zavrsena_at ? ' — završena' : '' }}</option>
                                @endforeach
                            </select>
                        </td>
                        @endif
                        @if(in_array('gnojivo', $visibleColumns))
                        <td data-label="Gnojivo" class="mob-puna p-1 border border-gray-200">
                            <input type="text" wire:model.live.blur="editForm.tip_gnojiva" class="form-input w-full" list="tipovi-gnojiva" autocomplete="off">
                        @if($this->skladista->count() > 1)<label class="mt-1 flex items-center gap-1 text-xs text-gray-500"><span class="whitespace-nowrap">Iz skladišta</span><select wire:model.live="editForm.skladiste_id" class="form-input flex-1 py-0.5 text-xs">@foreach($this->skladista as $skl)<option value="{{ $skl->id }}">{{ $skl->naziv }}</option>@endforeach</select></label>@endif
                            <div class="mt-1 flex flex-wrap items-center gap-1 text-xs text-gray-500">
                                <span class="whitespace-nowrap" title="udio dušika, fosfora (P₂O₅) i kalija (K₂O) u %">N-P-K %</span>
                                <input type="number" step="0.1" wire:key="n-editForm-{{ $editForm['n_posto'] ?? '' }}" wire:model.live.blur="editForm.n_posto" placeholder="N" class="form-input w-14 px-1">
                                <input type="number" step="0.1" wire:key="p-editForm-{{ $editForm['p_posto'] ?? '' }}" wire:model="editForm.p_posto" placeholder="P" class="form-input w-14 px-1">
                                <input type="number" step="0.1" wire:key="k-editForm-{{ $editForm['k_posto'] ?? '' }}" wire:model="editForm.k_posto" placeholder="K" class="form-input w-14 px-1">
                                <label class="ml-1 flex items-center gap-1 whitespace-nowrap" title="Stajski gnoj, gnojovka, gnojnica — broji se u granicu 170 kg N/ha">
                                    <input type="checkbox" wire:key="o-editForm-{{ (int) ($editForm['organsko'] ?? 0) }}" wire:model="editForm.organsko" class="rounded"> stajski
                                </label>
                            </div>
                        </td>
                        @endif
                        @if(in_array('kolicina', $visibleColumns))
                        <td data-label="Količina kg/ha" class="p-1 border border-gray-200">
                            <input type="number" wire:model.live.blur="editForm.kolicina_kg_ha" step="0.01" class="form-input w-full">
                        </td>
                        @endif
                        @if(in_array('hraniva', $visibleColumns))
                        <td data-label="N-P-K / kg N/ha" class="p-1 border border-gray-200 text-right text-gray-600 whitespace-nowrap">
                            {{ ($x = \App\Support\Gnojiva::kgHraniva($editForm['kolicina_kg_ha'] ?? null, $editForm['n_posto'] ?? null)) !== null ? number_format($x, 1, ',', '.') . ' kg N' : '—' }}
                        </td>
                        @endif
                        <td class="mob-puna mob-akcije p-1 border border-gray-200 no-print whitespace-nowrap">
                            <button wire:click="saveEdit" class="btn-xs-green">Spremi</button>
                            <button wire:click="cancelEdit" class="btn-xs-gray">Odustani</button>
                        </td>
                    @else
                        {{-- View mode --}}
                        <td class="mob-skrij px-2 py-2 border border-gray-200 text-center">{{ $loop->iteration }}</td>
                        @if(in_array('datum', $visibleColumns))<td data-label="Datum" class="px-3 py-2 border border-gray-200">{{ $row->datum->format('d.m.Y') }}</td>@endif
                        @if(in_array('arkod', $visibleColumns))<td data-label="ARKOD" class="px-3 py-2 border border-gray-200">{{ $row->arkod_broj }}</td>@endif
                        @if(in_array('kultura', $visibleColumns))<td data-label="Kultura" class="px-3 py-2 border border-gray-200 uppercase">{{ $row->kultura_naziv }}</td>@endif
                        @if(in_array('povrsina', $visibleColumns))<td data-label="Površina (ha)" class="px-3 py-2 border border-gray-200 text-right">{{ number_format($row->posadjena_povrsina_ha, 2) }}</td>@endif
                        @if(in_array('gnojivo', $visibleColumns))<td data-label="Gnojivo" class="mob-puna px-3 py-2 border border-gray-200">{{ $row->tip_gnojiva }}</td>@endif
                        @if(in_array('kolicina', $visibleColumns))<td data-label="Količina kg/ha" class="px-3 py-2 border border-gray-200 text-right font-medium">{{ number_format($row->kolicina_kg_ha, 2) }}</td>@endif
                        @if(in_array('hraniva', $visibleColumns))
                        <td data-label="N-P-K / kg N/ha" class="px-3 py-2 border border-gray-200 text-right whitespace-nowrap">
                            {{ $row->kg_n_ha !== null ? number_format($row->kg_n_ha, 1, ',', '.') : '—' }}
                            @if($row->n_posto !== null)<div class="text-xs text-gray-500">{{ \App\Support\Gnojiva::postotak($row->n_posto) }}-{{ \App\Support\Gnojiva::postotak($row->p_posto) }}-{{ \App\Support\Gnojiva::postotak($row->k_posto) }}{{ $row->organsko ? ' · stajski' : '' }}</div>@endif
                        </td>
                        @endif
                        <td class="mob-puna mob-akcije px-2 py-2 border border-gray-200 no-print whitespace-nowrap text-center">
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
                    <td colspan="8" class="mob-puna text-center py-10 text-gray-400 italic">
                        Nema unesenih gnojidbi. Kliknite <strong>+ Dodaj</strong> za unos.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
