<div class="space-y-5">
    @php
        $eur = fn ($v) => number_format((float) $v, 2, ',', '.') . ' €';
        $broj = fn ($v) => rtrim(rtrim(number_format((float) $v, 3, ',', '.'), '0'), ',');
    @endphp

    {{-- Godina, filter i dodavanje --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-2">
            <select wire:model.live="godina" aria-label="Godina" class="form-input w-28">
                @foreach($godine as $g)
                    <option value="{{ $g }}">{{ $g }}.</option>
                @endforeach
            </select>
            <select wire:model.live="kulturaFilter" aria-label="Kultura" class="form-input w-64 max-w-full">
                <option value="">Sve kulture</option>
                @foreach($kulture as $k)
                    <option value="{{ $k->id }}">{{ $k->arkod_broj }} · {{ $k->naziv }}</option>
                @endforeach
            </select>
        </div>
        <button wire:click="dodaj" class="btn-primary">+ Dodaj berbu</button>
    </div>

    {{-- Unos --}}
    @if($showForm)
        <div class="rounded-lg border border-green-200 bg-green-50 p-4">
            <p class="text-sm font-semibold text-gray-800 mb-3">{{ $editId ? 'Uredi berbu' : 'Nova berba / žetva' }}</p>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <label class="block text-xs text-gray-600 sm:col-span-2">Kultura
                    <select wire:model.live="form.kultura_id" class="form-input w-full mt-1 block">
                        <option value="">Odaberite…</option>
                        @foreach($kulture as $k)
                            <option value="{{ $k->id }}">{{ $k->arkod_broj }} · {{ $k->naziv }}{{ $k->sorta ? ' (' . $k->sorta . ')' : '' }} · {{ number_format($k->posadjena_povrsina_ha, 2, ',', '.') }} ha{{ $k->zavrsena_at ? ' · završena' : '' }}</option>
                        @endforeach
                    </select>
                    @error('form.kultura_id')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>
                <label class="block text-xs text-gray-600">Datum
                    <input type="date" wire:model.live="form.datum" class="form-input w-full mt-1 block">
                    @error('form.datum')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>
                <div class="block text-xs text-gray-600">Količina
                    <div class="mt-1 flex gap-2">
                        <input type="number" step="0.01" wire:model="form.kolicina" aria-label="Količina" placeholder="npr. 1200" class="form-input w-full min-w-0">
                        <select wire:model.live="form.jedinica" aria-label="Jedinica" class="form-input w-20 shrink-0">
                            <option value="kg">kg</option>
                            <option value="t">t</option>
                        </select>
                    </div>
                    @error('form.kolicina')<span class="text-red-500">{{ $message }}</span>@enderror
                </div>
                <label class="block text-xs text-gray-600">Cijena (€/{{ $form['jedinica'] ?? 'kg' }}) <span class="text-gray-400">neob.</span>
                    <input type="number" step="0.01" wire:model="form.cijena" class="form-input w-full mt-1 block">
                    @error('form.cijena')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>
                <label class="block text-xs text-gray-600">Sorta <span class="text-gray-400">neob.</span>
                    <input type="text" wire:model="form.sorta" class="form-input w-full mt-1 block">
                    @error('form.sorta')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>
                <label class="block text-xs text-gray-600">Klasa <span class="text-gray-400">neob.</span>
                    <input type="text" wire:model="form.klasa" placeholder="npr. I. klasa" class="form-input w-full mt-1 block">
                    @error('form.klasa')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>
                <label class="block text-xs text-gray-600">Pobrana površina (ha) <span class="text-gray-400">neob.</span>
                    <input type="number" step="0.01" wire:model="form.povrsina_ha" class="form-input w-full mt-1 block">
                    @error('form.povrsina_ha')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>
                <label class="block text-xs text-gray-600 sm:col-span-2">Kupac <span class="text-gray-400">neob.</span>
                    <input type="text" wire:model="form.kupac" class="form-input w-full mt-1 block">
                    @error('form.kupac')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>
                <label class="block text-xs text-gray-600 sm:col-span-2">Napomena <span class="text-gray-400">neob.</span>
                    <input type="text" wire:model="form.napomena" class="form-input w-full mt-1 block">
                    @error('form.napomena')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>
            </div>

            {{-- Karenca prema upisanim prskanjima --}}
            @php $pk = $this->provjeraKarence; @endphp
            @if($pk && $pk['krsi'])
                <p class="mt-3 flex gap-2 rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-sm font-medium text-amber-900">
                    <span aria-hidden="true">⚠</span>
                    <span>Karenca nije prošla: {{ $pk['krsi']['sredstvo'] }}, prskano {{ $pk['krsi']['prskano']->format('d.m.Y.') }}, karenca {{ $pk['krsi']['dana'] }} dana traje do {{ $pk['krsi']['do']->format('d.m.Y.') }} Zapis se može spremiti, ali provjerite prije berbe.</span>
                </p>
            @elseif($pk && $pk['provjereno'] > 0 && ! $pk['neprovjereno'])
                <p class="mt-3 flex gap-2 text-sm text-green-800"><span aria-hidden="true">✓</span><span>Prema upisanim prskanjima i registru FIS-a karenca je prošla.</span></p>
            @endif
            @if($pk && $pk['neprovjereno'])
                <p class="mt-2 text-xs text-gray-600">Karencu nije moguće provjeriti za: {{ implode(', ', $pk['neprovjereno']) }}. Provjerite je na etiketi.</p>
            @endif

            <div class="mt-4 flex gap-2">
                <button wire:click="spremi" class="btn-xs-green">Spremi</button>
                <button wire:click="odustani" class="btn-xs-gray">Odustani</button>
            </div>
        </div>
    @endif

    {{-- Zbroj --}}
    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <p class="text-sm font-medium text-gray-600">Ubrano u {{ $godina }}.</p>
            <p class="text-2xl font-bold text-gray-900">{{ $masa($ukupnoKg) }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <p class="text-sm font-medium text-gray-600">Broj berbi</p>
            <p class="text-2xl font-bold text-gray-900">{{ $berbe->count() }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <p class="text-sm font-medium text-gray-600">Vrijednost</p>
            <p class="text-2xl font-bold text-gray-900">{{ $eur($ukupnoIznos) }}</p>
            @if($bezCijene)<p class="text-xs text-amber-800">{{ $bezCijene }} bez cijene nije uračunato</p>@endif
        </div>
    </div>

    {{-- Po kulturi --}}
    @if($poKulturi->count() > 1)
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="w-full text-sm mob-kartice">
                <thead>
                    <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <th class="px-4 py-2 text-left">Kultura</th><th class="px-4 py-2 text-left">ARKOD</th>
                        <th class="px-4 py-2 text-right">Berbi</th><th class="px-4 py-2 text-right">Ubrano</th>
                        <th class="px-4 py-2 text-right">t/ha</th><th class="px-4 py-2 text-right">Vrijednost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($poKulturi as $r)
                        <tr class="border-t border-gray-100">
                            <td class="mob-puna px-4 py-2 font-medium text-gray-800">{{ $r['naziv'] }}</td>
                            <td data-label="ARKOD" class="px-4 py-2 text-gray-600">{{ $r['arkod'] }}</td>
                            <td data-label="Berbi" class="px-4 py-2 text-right text-gray-600">{{ $r['broj'] }}</td>
                            <td data-label="Ubrano" class="px-4 py-2 text-right text-gray-800">{{ $masa($r['kg']) }}</td>
                            <td data-label="t/ha" class="px-4 py-2 text-right text-gray-600">{{ $r['t_ha'] !== null ? number_format($r['t_ha'], 2, ',', '.') : '—' }}</td>
                            <td data-label="Vrijednost" class="px-4 py-2 text-right text-gray-800">{{ $eur($r['iznos']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Zapisi --}}
    @if($berbe->isEmpty())
        <div class="text-center py-10 text-gray-500">
            <p>Za {{ $godina }}. nema upisanih berbi.</p>
            <p class="text-sm mt-1">Kliknite <strong>+ Dodaj berbu</strong>. Jedna kultura može imati koliko god berbi.</p>
        </div>
    @else
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="w-full text-sm mob-kartice">
                <thead>
                    <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <th class="px-4 py-2 text-left">Datum</th><th class="px-4 py-2 text-left">ARKOD</th><th class="px-4 py-2 text-left">Kultura</th>
                        <th class="px-4 py-2 text-right">Količina</th><th class="px-4 py-2 text-right">Cijena</th><th class="px-4 py-2 text-right">Iznos</th>
                        <th class="px-4 py-2 text-left">Kupac</th><th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($berbe as $b)
                        <tr wire:key="berba-{{ $b->id }}" class="border-t border-gray-100 hover:bg-gray-50">
                            <td data-label="Datum" class="px-4 py-2 whitespace-nowrap">{{ $b->datum->format('d.m.Y.') }}</td>
                            <td data-label="ARKOD" class="px-4 py-2 text-gray-600">{{ $b->kultura?->parcela?->arkod_broj }}</td>
                            <td class="mob-puna px-4 py-2 font-medium text-gray-800">
                                {{ $b->kultura?->naziv }}
                                @if($b->sorta || $b->klasa)<span class="font-normal text-gray-500"> · {{ collect([$b->sorta, $b->klasa])->filter()->implode(', ') }}</span>@endif
                                @if($b->karenca)
                                    <span class="block text-xs font-medium text-amber-800"><span aria-hidden="true">⚠</span> u karenci do {{ $b->karenca['do']->format('d.m.Y.') }} ({{ $b->karenca['sredstvo'] }})</span>
                                @endif
                                @if($b->napomena)<span class="block text-xs font-normal text-gray-500">{{ $b->napomena }}</span>@endif
                            </td>
                            <td data-label="Količina" class="px-4 py-2 text-right whitespace-nowrap">{{ $broj($b->kolicina) }} {{ $b->jedinica }}</td>
                            <td data-label="Cijena" class="px-4 py-2 text-right whitespace-nowrap text-gray-600">{{ $b->cijena !== null ? number_format($b->cijena, 2, ',', '.') . ' €/' . $b->jedinica : '—' }}</td>
                            <td data-label="Iznos" class="px-4 py-2 text-right whitespace-nowrap">{{ $b->iznos !== null ? $eur($b->iznos) : '—' }}</td>
                            <td data-label="Kupac" class="px-4 py-2 text-gray-600">{{ $b->kupac ?: '—' }}</td>
                            <td class="mob-puna mob-akcije px-4 py-2 text-right whitespace-nowrap">
                                <button wire:click="uredi({{ $b->id }})" class="btn-xs-blue">Uredi</button>
                                <button wire:click="obrisi({{ $b->id }})" wire:confirm="Obrisati ovu berbu?" class="btn-xs-red" aria-label="Obriši">&#10005;</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
