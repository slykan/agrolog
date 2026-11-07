<div class="space-y-5">
    @php
        $eur = fn ($v) => number_format((float) $v, 2, ',', '.') . ' €';
        $broj = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', '.'), '0'), ',');
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
        <button wire:click="dodaj" class="btn-primary">+ Dodaj rad</button>
    </div>

    {{-- Unos --}}
    @if($showForm)
        <div class="rounded-lg border border-green-200 bg-green-50 p-4">
            <p class="text-sm font-semibold text-gray-800 mb-3">{{ $editId ? 'Uredi rad' : 'Novi rad' }}</p>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <label class="block text-xs text-gray-600 sm:col-span-2">Kultura
                    <select wire:model.live="form.kultura_id" class="form-input w-full mt-1 block">
                        <option value="">Odaberite…</option>
                        @foreach($kulture as $k)
                            <option value="{{ $k->id }}">{{ $k->arkod_broj }} · {{ $k->naziv }} · {{ number_format($k->posadjena_povrsina_ha, 2, ',', '.') }} ha{{ $k->zavrsena_at ? ' · završena' : '' }}</option>
                        @endforeach
                    </select>
                    @error('form.kultura_id')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>
                <label class="block text-xs text-gray-600">Datum
                    <input type="date" wire:model="form.datum" class="form-input w-full mt-1 block">
                    @error('form.datum')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>
                <label class="block text-xs text-gray-600">Vrsta rada
                    <input type="text" id="vrsta-rada" wire:model.live.debounce.400ms="form.vrsta" list="vrste-radova" placeholder="npr. Oranje" maxlength="100" class="form-input w-full mt-1 block">
                    <datalist id="vrste-radova">
                        @foreach($vrste as $v)<option value="{{ $v }}">@endforeach
                    </datalist>
                    @error('form.vrsta')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>
            </div>

            {{-- brzi izbor vrste prema kulturi --}}
            <div class="mt-3">
                <p class="text-xs text-gray-500 mb-1.5">{{ $skupina }}: kliknite vrstu ili upišite svoju, a ona vam se nudi i sljedeći put.</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($vrste as $v)
                        <button type="button" x-on:click="const i = document.getElementById('vrsta-rada'); i.value = @js($v); i.dispatchEvent(new Event('input', { bubbles: true }))" wire:key="vrsta-{{ md5($v) }}"
                                class="rounded-full border px-2.5 py-0.5 text-xs transition {{ mb_strtolower($form['vrsta'] ?? '') === mb_strtolower($v) ? 'border-green-700 bg-green-700 text-white' : 'border-gray-300 bg-white text-gray-700 hover:border-green-600' }}">{{ $v }}</button>
                    @endforeach
                </div>
            </div>

            {{-- izvedba --}}
            <div class="mt-4 inline-flex rounded-lg border border-gray-200 bg-white p-0.5 text-sm" role="group" aria-label="Tko je radio">
                @foreach(['vlastito' => 'Vlastiti rad', 'usluga' => 'Usluga (plaćeno drugome)'] as $k => $v)
                    <button type="button" wire:click="$set('form.izvedba', '{{ $k }}')" aria-pressed="{{ ($form['izvedba'] ?? '') === $k ? 'true' : 'false' }}"
                            class="px-3 py-1 rounded-md transition {{ ($form['izvedba'] ?? '') === $k ? 'bg-green-700 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">{{ $v }}</button>
                @endforeach
            </div>

            <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @if(($form['izvedba'] ?? '') === 'usluga')
                    <div class="block text-xs text-gray-600">Cijena usluge
                        <div class="mt-1 flex gap-2">
                            <input type="number" step="0.01" wire:model.live.debounce.400ms="form.usluga_cijena" aria-label="Cijena usluge" placeholder="npr. 60" class="form-input w-full min-w-0">
                            <select wire:model.live="form.usluga_po" aria-label="Obračun" class="form-input w-28 shrink-0">
                                <option value="ha">€/ha</option>
                                <option value="ukupno">€ ukupno</option>
                            </select>
                        </div>
                        @error('form.usluga_cijena')<span class="text-red-500">{{ $message }}</span>@enderror
                    </div>
                    <label class="block text-xs text-gray-600">Obrađena površina (ha) <span class="text-gray-400">neob.</span>
                        <input type="number" step="0.01" wire:model.live.debounce.400ms="form.povrsina_ha" placeholder="{{ $haKulture !== null ? 'cijela: ' . number_format($haKulture, 2, ',', '.') : '' }}" class="form-input w-full mt-1 block">
                        @error('form.povrsina_ha')<span class="text-red-500">{{ $message }}</span>@enderror
                    </label>
                    <label class="block text-xs text-gray-600 sm:col-span-2">Izvođač <span class="text-gray-400">neob.</span>
                        <input type="text" wire:model="form.izvodac" placeholder="npr. susjed, zadruga" class="form-input w-full mt-1 block">
                        @error('form.izvodac')<span class="text-red-500">{{ $message }}</span>@enderror
                    </label>
                @else
                    <label class="block text-xs text-gray-600">Stroj <span class="text-gray-400">neob.</span>
                        <select wire:model="form.stroj_id" class="form-input w-full mt-1 block">
                            <option value="">—</option>
                            @foreach($strojevi as $s)
                                <option value="{{ $s->id }}">{{ $s->naziv }}</option>
                            @endforeach
                        </select>
                        @error('form.stroj_id')<span class="text-red-500">{{ $message }}</span>@enderror
                    </label>
                    <label class="block text-xs text-gray-600">Sati rada stroja <span class="text-gray-400">neob.</span>
                        <input type="number" step="0.1" wire:model="form.sati" class="form-input w-full mt-1 block">
                        @error('form.sati')<span class="text-red-500">{{ $message }}</span>@enderror
                    </label>
                    <label class="block text-xs text-gray-600">Obrađena površina (ha) <span class="text-gray-400">neob.</span>
                        <input type="number" step="0.01" wire:model="form.povrsina_ha" placeholder="{{ $haKulture !== null ? 'cijela: ' . number_format($haKulture, 2, ',', '.') : '' }}" class="form-input w-full mt-1 block">
                        @error('form.povrsina_ha')<span class="text-red-500">{{ $message }}</span>@enderror
                    </label>
                    <div class="hidden lg:block"></div>
                    <div class="block text-xs text-gray-600 sm:col-span-2">Gorivo <span class="text-gray-400">neob.</span>
                        <div class="mt-1 flex items-center gap-2">
                            <input type="number" step="0.1" wire:model.live.debounce.400ms="form.gorivo_l" aria-label="Gorivo (L)" placeholder="L" class="form-input w-full min-w-0">
                            <span class="text-gray-400">×</span>
                            <input type="number" step="0.01" wire:model.live.debounce.400ms="form.cijena_goriva" aria-label="Cijena goriva (€/L)" placeholder="€/L" class="form-input w-full min-w-0">
                        </div>
                        @error('form.gorivo_l')<span class="text-red-500">{{ $message }}</span>@enderror
                        @error('form.cijena_goriva')<span class="text-red-500">{{ $message }}</span>@enderror
                    </div>
                    <div class="block text-xs text-gray-600 sm:col-span-2">Rad: radnici × sati i cijena sata <span class="text-gray-400">neob.</span>
                        <div class="mt-1 flex items-center gap-2">
                            <input type="number" step="0.5" wire:model.live.debounce.400ms="form.radni_sati" aria-label="Radni sati ukupno" placeholder="npr. 2 radnika × 8 h = 16" class="form-input w-full min-w-0">
                            <span class="text-gray-400">×</span>
                            <input type="number" step="0.01" wire:model.live.debounce.400ms="form.cijena_sata" aria-label="Cijena sata (€/h)" placeholder="€/h" class="form-input w-full min-w-0">
                        </div>
                        @error('form.radni_sati')<span class="text-red-500">{{ $message }}</span>@enderror
                        @error('form.cijena_sata')<span class="text-red-500">{{ $message }}</span>@enderror
                    </div>
                @endif
                <label class="block text-xs text-gray-600 sm:col-span-2 lg:col-span-4">Napomena <span class="text-gray-400">neob.</span>
                    <input type="text" wire:model="form.napomena" class="form-input w-full mt-1 block">
                    @error('form.napomena')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>
            </div>

            <p class="mt-3 text-sm text-gray-700">
                Trošak: <strong class="text-gray-900">{{ $this->izracun !== null ? $eur($this->izracun) : '—' }}</strong>
                @if($this->izracun === null)<span class="text-xs text-gray-500">(rad se može spremiti i bez troška)</span>@endif
            </p>

            <div class="mt-4 flex gap-2">
                <button wire:click="spremi" class="btn-xs-green">Spremi</button>
                <button wire:click="odustani" class="btn-xs-gray">Odustani</button>
            </div>
        </div>
    @endif

    {{-- Zbroj --}}
    <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <p class="text-sm font-medium text-gray-600">Radova u {{ $godina }}.</p>
            <p class="text-2xl font-bold text-gray-900">{{ $radovi->count() }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <p class="text-sm font-medium text-gray-600">Trošak radova</p>
            <p class="text-2xl font-bold text-gray-900">{{ $eur($ukupno) }}</p>
            @if($bezTroska)<p class="text-xs text-gray-500">{{ $bezTroska }} bez troška</p>@endif
        </div>
        <a href="{{ route('troskovi') }}" class="rounded-xl border border-gray-200 bg-white p-4 hover:bg-gray-50 transition">
            <p class="text-sm font-medium text-gray-600">Ulazi u troškove kulture</p>
            <p class="text-sm text-gray-700 mt-1">Trošak radova se zbraja u €/ha na stranici <span class="text-green-700 underline">Troškovi</span>.</p>
        </a>
    </div>

    @if($poVrsti->count() > 1)
        <div class="flex flex-wrap gap-2 text-sm">
            @foreach($poVrsti as $v)
                <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700">{{ $v['vrsta'] }} <span class="text-gray-500">× {{ $v['broj'] }}</span> · {{ $eur($v['trosak']) }}</span>
            @endforeach
        </div>
    @endif

    {{-- Zapisi --}}
    @if($radovi->isEmpty())
        <div class="text-center py-10 text-gray-500">
            <p>Za {{ $godina }}. nema upisanih radova.</p>
            <p class="text-sm mt-1">Kliknite <strong>+ Dodaj rad</strong> i upišite oranje, rezidbu, košnju ili bilo koji drugi rad.</p>
        </div>
    @else
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="w-full text-sm mob-kartice">
                <thead>
                    <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <th class="px-4 py-2 text-left">Datum</th><th class="px-4 py-2 text-left">Parcela</th><th class="px-4 py-2 text-left">Rad</th>
                        <th class="px-4 py-2 text-left">Izvedba</th><th class="px-4 py-2 text-right">Trošak</th><th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($radovi as $r)
                        <tr wire:key="rad-{{ $r->id }}" class="border-t border-gray-100 hover:bg-gray-50">
                            <td data-label="Datum" class="px-4 py-2 whitespace-nowrap">{{ $r->datum->format('d.m.Y.') }}</td>
                            <td data-label="Parcela" class="px-4 py-2 text-gray-600">{{ $r->kultura?->parcela?->arkod_broj }} · {{ $r->kultura?->naziv }}</td>
                            <td class="mob-puna px-4 py-2 font-medium text-gray-800">
                                {{ $r->vrsta }}
                                @if($r->povrsina_ha)<span class="font-normal text-gray-500"> · {{ $broj($r->povrsina_ha) }} ha</span>@endif
                                @if($r->napomena)<span class="block text-xs font-normal text-gray-500">{{ $r->napomena }}</span>@endif
                            </td>
                            <td data-label="Izvedba" class="px-4 py-2 text-gray-600">
                                @if($r->izvedba === 'usluga')
                                    Usluga{{ $r->izvodac ? ': ' . $r->izvodac : '' }}
                                    @if($r->usluga_cijena !== null)<span class="block text-xs text-gray-500">{{ $broj($r->usluga_cijena) }} {{ $r->usluga_po === 'ha' ? '€/ha' : '€' }}</span>@endif
                                @else
                                    {{ $r->stroj?->naziv ?? 'Vlastiti rad' }}{{ $r->sati ? ' · ' . $broj($r->sati) . ' h' : '' }}
                                    @php $dijelovi = array_filter([$r->gorivo_l ? $broj($r->gorivo_l) . ' L goriva' : null, $r->radni_sati ? $broj($r->radni_sati) . ' radnih sati' : null]); @endphp
                                    @if($dijelovi)<span class="block text-xs text-gray-500">{{ implode(' · ', $dijelovi) }}</span>@endif
                                @endif
                            </td>
                            <td data-label="Trošak" class="px-4 py-2 text-right whitespace-nowrap font-medium">{{ $r->trosak !== null ? $eur($r->trosak) : '—' }}</td>
                            <td class="mob-puna mob-akcije px-4 py-2 text-right whitespace-nowrap">
                                <button wire:click="kopiraj({{ $r->id }})" class="btn-xs-gray" title="Upiši isti rad s današnjim datumom">Kopiraj</button>
                                <button wire:click="uredi({{ $r->id }})" class="btn-xs-blue">Uredi</button>
                                <button wire:click="obrisi({{ $r->id }})" wire:confirm="Obrisati ovaj rad?" class="btn-xs-red" aria-label="Obriši">&#10005;</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
