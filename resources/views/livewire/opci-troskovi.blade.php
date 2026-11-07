<div id="opci-troskovi" class="rounded-xl border border-gray-200 bg-white overflow-hidden scroll-mt-4">
    @php
        $eur = fn ($v) => number_format((float) $v, 2, ',', '.') . ' €';
        $kmT = fn ($v) => rtrim(rtrim(number_format((float) $v, 1, ',', '.'), '0'), ',') . ' km';
    @endphp

    <div class="px-4 pt-4 pb-3 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h4 class="font-semibold text-gray-800">Opći troškovi</h4>
            <p class="text-xs text-gray-500">Vožnja, zakup, osiguranje i drugo što nije rad na parceli. Bez kulture ulazi samo u ukupni trošak, a s kulturom i u njezin €/ha.</p>
        </div>
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            <select wire:model.live="godina" aria-label="Godina" class="form-input w-28">
                @foreach($godine as $g)
                    <option value="{{ $g }}">{{ $g }}.</option>
                @endforeach
            </select>
            <button wire:click="dodaj('Vožnja')" class="btn-xs-gray">+ Vožnja</button>
            <button wire:click="dodaj" class="btn-primary">+ Dodaj trošak</button>
        </div>
    </div>

    {{-- spremljene rute: jedan klik za upis vožnje --}}
    @if($rute->isNotEmpty())
        <div class="px-4 pb-3 flex flex-wrap items-center gap-2 text-sm">
            <span class="text-xs text-gray-500">Brzi upis vožnje:</span>
            @foreach($rute as $r)
                <span wire:key="ruta-{{ $r->id }}" class="inline-flex items-center rounded-full border border-green-300 bg-green-50 text-green-900">
                    <button wire:click="izRute({{ $r->id }})" class="pl-3 pr-1 py-0.5 hover:underline">{{ $r->naziv }} · {{ $kmT($r->km) }}{{ $r->povratno ? ' ⇄' : '' }}</button>
                    <button wire:click="obrisiRutu({{ $r->id }})" wire:confirm="Obrisati rutu {{ $r->naziv }}? Upisane vožnje ostaju." class="px-2 py-0.5 text-green-700 hover:text-red-600" aria-label="Obriši rutu {{ $r->naziv }}">&times;</button>
                </span>
            @endforeach
        </div>
    @endif

    {{-- Unos --}}
    @if($showForm)
        <div class="mx-4 mb-4 rounded-lg border border-green-200 bg-green-50 p-4">
            <p class="text-sm font-semibold text-gray-800 mb-3">{{ $editId ? 'Uredi trošak' : 'Novi trošak' }}</p>
            <div class="flex flex-wrap gap-1.5 mb-3">
                @foreach($vrste as $v)
                    <button type="button" x-on:click="const i = document.getElementById('opca-vrsta'); i.value = @js($v); i.dispatchEvent(new Event('input', { bubbles: true }))" wire:key="opca-{{ md5($v) }}"
                            class="rounded-full border px-2.5 py-0.5 text-xs transition {{ mb_strtolower($form['vrsta'] ?? '') === mb_strtolower($v) ? 'border-green-700 bg-green-700 text-white' : 'border-gray-300 bg-white text-gray-700 hover:border-green-600' }}">{{ $v }}</button>
                @endforeach
            </div>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <label class="block text-xs text-gray-600">Vrsta
                    <input type="text" id="opca-vrsta" wire:model.live.debounce.400ms="form.vrsta" list="opce-vrste" maxlength="100" placeholder="ili upišite svoju" class="form-input w-full mt-1 block">
                    <datalist id="opce-vrste">@foreach($vrste as $v)<option value="{{ $v }}">@endforeach</datalist>
                    @error('form.vrsta')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>
                <label class="block text-xs text-gray-600">Datum
                    <input type="date" wire:model="form.datum" class="form-input w-full mt-1 block">
                    @error('form.datum')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>
                <label class="block text-xs text-gray-600 sm:col-span-2">{{ $voznja ? 'Kamo / svrha' : 'Opis' }} <span class="text-gray-400">{{ $voznja && $zapamtiRutu ? '' : 'neob.' }}</span>
                    <input type="text" wire:model="form.opis" maxlength="300" placeholder="{{ $voznja ? 'npr. Voćnjak Hrastin' : 'npr. zakup parcele od susjeda' }}" class="form-input w-full mt-1 block">
                    @error('form.opis')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>

                @if($voznja)
                    <label class="block text-xs text-gray-600">Kilometri u jednom smjeru
                        <input type="number" step="0.1" wire:model.live.debounce.400ms="form.km" placeholder="npr. 25" class="form-input w-full mt-1 block">
                        @error('form.km')<span class="text-red-500">{{ $message }}</span>@enderror
                    </label>
                    <label class="block text-xs text-gray-600">Cijena po km (€)
                        <input type="number" step="0.01" wire:model.live.debounce.400ms="form.cijena_km" class="form-input w-full mt-1 block">
                        @error('form.cijena_km')<span class="text-red-500">{{ $message }}</span>@enderror
                    </label>
                    <div class="flex flex-col justify-end gap-2 text-sm text-gray-700 sm:col-span-2 pb-1">
                        <label class="inline-flex items-center gap-2"><input type="checkbox" wire:model.live="form.povratno" class="rounded border-gray-300 text-green-700"> Tamo i natrag (km × 2)</label>
                        @unless($editId)
                            <label class="inline-flex items-center gap-2"><input type="checkbox" wire:model.live="zapamtiRutu" class="rounded border-gray-300 text-green-700"> Zapamti kao rutu za brzi upis</label>
                        @endunless
                    </div>
                @else
                    <label class="block text-xs text-gray-600">Iznos (€)
                        <input type="number" step="0.01" wire:model="form.iznos" class="form-input w-full mt-1 block">
                        @error('form.iznos')<span class="text-red-500">{{ $message }}</span>@enderror
                    </label>
                @endif

                <label class="block text-xs text-gray-600 sm:col-span-2">Kultura <span class="text-gray-400">neob., tada ulazi i u njezin €/ha</span>
                    <select wire:model="form.kultura_id" class="form-input w-full mt-1 block">
                        <option value="">— cijelo gospodarstvo —</option>
                        @foreach($kulture as $k)
                            <option value="{{ $k->id }}">{{ $k->arkod_broj }} · {{ $k->naziv }}</option>
                        @endforeach
                    </select>
                    @error('form.kultura_id')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>
                @if($klijenti->isNotEmpty())
                    <label class="block text-xs text-gray-600 sm:col-span-2">Klijent <span class="text-gray-400">neob., za obilazak klijenta</span>
                        <select wire:model="form.klijent_id" class="form-input w-full mt-1 block">
                            <option value="">—</option>
                            @foreach($klijenti as $k)
                                <option value="{{ $k->id }}">{{ $k->name }}</option>
                            @endforeach
                        </select>
                        @error('form.klijent_id')<span class="text-red-500">{{ $message }}</span>@enderror
                    </label>
                @endif
            </div>

            @if($voznja)
                <p class="mt-3 text-sm text-gray-700">
                    @if($this->iznosVoznje !== null)
                        {{ $kmT((float) $form['km'] * (! empty($form['povratno']) ? 2 : 1)) }} × {{ number_format((float) $form['cijena_km'], 2, ',', '.') }} €/km = <strong class="text-gray-900">{{ $eur($this->iznosVoznje) }}</strong>
                    @else
                        Upišite kilometre i iznos se izračuna sam.
                    @endif
                </p>
                <p class="mt-1 text-xs text-gray-500">Cijena po km je samo prijedlog. Upišite onu koju koristite, npr. stvarni trošak goriva i auta, a pamti se za sljedeću vožnju.</p>
            @endif

            <div class="mt-4 flex gap-2">
                <button wire:click="spremi" class="btn-xs-green">Spremi</button>
                <button wire:click="odustani" class="btn-xs-gray">Odustani</button>
            </div>
        </div>
    @endif

    {{-- zbroj --}}
    @if($troskovi->isNotEmpty())
        <div class="px-4 pb-3 flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-700">
            <span>Ukupno {{ $godina }}.: <strong class="text-gray-900">{{ $eur($ukupno) }}</strong></span>
            @if($km > 0)<span>Prijeđeno: <strong class="text-gray-900">{{ $kmT($km) }}</strong></span>@endif
            @if($nevezano < $ukupno)<span class="text-gray-500">vezano uz kulture: {{ $eur($ukupno - $nevezano) }}</span>@endif
        </div>
    @endif

    {{-- savjetnik: obilasci po klijentu --}}
    @if($poKlijentu->isNotEmpty())
        <div class="mx-4 mb-3 rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm">
            <p class="font-semibold text-blue-900 mb-1">Po klijentu ({{ $godina }}.)</p>
            <ul class="space-y-0.5 text-blue-900">
                @foreach($poKlijentu as $k)
                    <li>{{ $k['ime'] }}: {{ $k['broj'] }} {{ $k['broj'] === 1 ? 'stavka' : 'stavki' }}@if($k['km'] > 0), {{ $kmT($k['km']) }}@endif · <strong>{{ $eur($k['iznos']) }}</strong></li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($troskovi->isEmpty())
        <p class="text-sm text-gray-500 px-4 pb-6">Za {{ $godina }}. nema općih troškova. Vožnju do udaljene parcele upišite gumbom <strong>+ Vožnja</strong>.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm mob-kartice">
                <thead>
                    <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <th class="px-4 py-2 text-left">Datum</th><th class="px-4 py-2 text-left">Vrsta</th><th class="px-4 py-2 text-left">Kultura</th>
                        <th class="px-4 py-2 text-right">Iznos</th><th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($troskovi as $t)
                        <tr wire:key="opci-{{ $t->id }}" class="border-t border-gray-100 hover:bg-gray-50">
                            <td data-label="Datum" class="px-4 py-2 whitespace-nowrap">{{ $t->datum->format('d.m.Y.') }}</td>
                            <td class="mob-puna px-4 py-2 font-medium text-gray-800">
                                {{ $t->vrsta }}@if($t->opis)<span class="font-normal text-gray-600"> · {{ $t->opis }}</span>@endif
                                @if($t->km !== null)<span class="block text-xs font-normal text-gray-500">{{ $kmT($t->ukupno_km) }}{{ $t->povratno ? ' (tamo i natrag)' : '' }} × {{ number_format((float) $t->cijena_km, 2, ',', '.') }} €/km</span>@endif
                                @if($t->klijent_id)<span class="block text-xs font-normal text-blue-800">Klijent: {{ $t->klijent?->name ?? 'bivši klijent' }}</span>@endif
                            </td>
                            <td data-label="Kultura" class="px-4 py-2 text-gray-600">{{ $t->kultura ? $t->kultura->parcela?->arkod_broj . ' · ' . $t->kultura->naziv : 'cijelo gospodarstvo' }}</td>
                            <td data-label="Iznos" class="px-4 py-2 text-right whitespace-nowrap font-medium">{{ $eur($t->iznos) }}</td>
                            <td class="mob-puna mob-akcije px-4 py-2 text-right whitespace-nowrap">
                                <button wire:click="uredi({{ $t->id }})" class="btn-xs-blue">Uredi</button>
                                <button wire:click="obrisi({{ $t->id }})" wire:confirm="Obrisati ovaj trošak?" class="btn-xs-red" aria-label="Obriši">&#10005;</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
