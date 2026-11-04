<div>
@if(! auth()->user()->koristi_skladiste)
    {{-- Skladište je neobavezno: dok nije uključeno, ništa se ne prati --}}
    <div class="max-w-2xl">
        <h3 class="text-lg font-bold text-gray-800 mb-2">Skladište sredstava i gnojiva</h3>
        <p class="text-sm text-gray-600 mb-4">Upišete što ste kupili, a AgroLog sam oduzima ono što ste poprskali i pognojili. Uvijek znate koliko vam je ostalo.</p>
        <ul class="text-sm text-gray-700 space-y-2 mb-6">
            <li class="flex gap-2"><span class="text-green-600 font-bold">✓</span> <span><strong>Ulaz:</strong> nabava s količinom i, po želji, cijenom i dobavljačem.</span></li>
            <li class="flex gap-2"><span class="text-green-600 font-bold">✓</span> <span><strong>Izlaz sam od sebe:</strong> doza iz prskanja ili gnojidbe × površina. Kad ispravite ili obrišete zapis, ispravi se i stanje.</span></li>
            <li class="flex gap-2"><span class="text-green-600 font-bold">✓</span> <span><strong>Upozorenje</strong> kad je nečega malo ili je stanje u minusu. Unos u evidenciju se nikad ne blokira.</span></li>
            <li class="flex gap-2"><span class="text-green-600 font-bold">✓</span> <span>Prati se samo ono što unesete na skladište. Ostala sredstva i gnojiva rade kao i do sada.</span></li>
        </ul>
        <button wire:click="ukljuci" class="btn-primary">Uključi skladište</button>
        <p class="text-xs text-gray-400 mt-3">Skladište možete isključiti u profilu. Podaci ostaju spremljeni.</p>
    </div>
@else
    @php
        $artikli = $this->artikli;
        $prikaz = $filterVrsta ? $artikli->where('vrsta', $filterVrsta) : $artikli;
        $uMinusu = $artikli->where('status', 'minus')->count();
        $priKraju = $artikli->where('status', 'malo')->count();
        $vrijednost = $artikli->sum(fn ($a) => $a['vrijednost'] ?? 0);
        $b = fn ($v, $d = 2) => \App\Support\Skladiste::broj($v, $d);
    @endphp

    {{-- sažetak i gumbi --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
        <div class="flex flex-wrap gap-2 text-sm">
            <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700">Praćeno: <strong>{{ $artikli->count() }}</strong></span>
            @if($uMinusu)<span class="px-3 py-1 rounded-full bg-red-100 text-red-700">U minusu: <strong>{{ $uMinusu }}</strong></span>@endif
            @if($priKraju)<span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800">Pri kraju: <strong>{{ $priKraju }}</strong></span>@endif
            @if($vrijednost > 0)<span class="px-3 py-1 rounded-full bg-green-50 text-green-800">Vrijednost zalihe: <strong>{{ $b($vrijednost) }} €</strong></span>@endif
        </div>
        <div class="grid grid-cols-2 gap-2 sm:flex">
            <button wire:click="novaNabava('szb')" class="btn-primary">+ Nabava sredstva</button>
            <button wire:click="novaNabava('gnojivo')" class="btn-primary">+ Nabava gnojiva</button>
        </div>
    </div>

    {{-- forma nabave --}}
    @if($showForm)
        <div class="mb-6 rounded-lg border {{ $editingId ? 'border-blue-200 bg-blue-50' : 'border-yellow-200 bg-yellow-50' }} p-4">
            <h4 class="font-semibold text-gray-800 mb-3">{{ $editingId ? 'Uredi nabavu' : 'Nova nabava' }}</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <label class="block text-sm">
                    <span class="text-gray-600">Vrsta</span>
                    <select wire:model.live="form.vrsta" class="form-input w-full mt-1">
                        @foreach(\App\Support\Skladiste::VRSTE as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach
                    </select>
                </label>
                <label class="block text-sm sm:col-span-1 lg:col-span-2">
                    <span class="text-gray-600">{{ $form['vrsta'] === 'gnojivo' ? 'Gnojivo' : 'Trgovački naziv sredstva' }}</span>
                    <input type="text" wire:model.live.blur="form.naziv" list="skladiste-{{ $form['vrsta'] }}" autocomplete="off"
                           placeholder="{{ $form['vrsta'] === 'gnojivo' ? 'npr. KAN 27% N' : 'npr. Roundup' }}" class="form-input w-full mt-1">
                    @error('form.naziv')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm">
                    <span class="text-gray-600">Datum nabave</span>
                    <input type="date" wire:model="form.datum" class="form-input w-full mt-1">
                    @error('form.datum')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                </label>
                <div class="block text-sm">
                    <span class="text-gray-600">Količina</span>
                    <div class="flex gap-1 mt-1">
                        <input type="number" step="0.001" wire:model="form.kolicina" placeholder="{{ $form['vrsta'] === 'gnojivo' ? '1000' : '5' }}" class="form-input flex-1 min-w-0">
                        <select wire:model="form.jedinica" class="form-input w-20">
                            <option value="L">L</option>
                            <option value="kg">kg</option>
                            @if($form['vrsta'] === 'gnojivo')<option value="t">t</option>@endif
                        </select>
                    </div>
                    @error('form.kolicina')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                </div>
                <label class="block text-sm">
                    <span class="text-gray-600">Cijena ukupno (€) <span class="text-gray-400">neobavezno</span></span>
                    <input type="number" step="0.01" wire:model="form.cijena" placeholder="npr. 85.00" class="form-input w-full mt-1">
                    @error('form.cijena')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm">
                    <span class="text-gray-600">Dobavljač <span class="text-gray-400">neobavezno</span></span>
                    <input type="text" wire:model="form.dobavljac" class="form-input w-full mt-1">
                </label>
                <label class="block text-sm">
                    <span class="text-gray-600">Napomena <span class="text-gray-400">neobavezno</span></span>
                    <input type="text" wire:model="form.napomena" placeholder="npr. račun br., serija" class="form-input w-full mt-1">
                </label>
            </div>
            <p class="text-xs text-gray-500 mt-3">
                Jedinica mora odgovarati dozi u evidenciji: ako u prskanju upisujete 2,5 po hektaru za sredstvo u litrama, ovdje upišite litre (za praškasta sredstva kg).
                Za vreće i kanistre upišite ukupnu količinu, npr. 4 kanistra po 5 L = 20 L.
            </p>
            <div class="flex gap-2 mt-3">
                <button wire:click="spremi" class="btn-xs-green">Spremi</button>
                <button wire:click="odustani" class="btn-xs-gray">Odustani</button>
            </div>
        </div>
    @endif

    <datalist id="skladiste-szb">@foreach($this->prijedlozi['szb'] as $n)<option value="{{ $n }}">@endforeach</datalist>
    <datalist id="skladiste-gnojivo">@foreach($this->prijedlozi['gnojivo'] as $n)<option value="{{ $n }}">@endforeach</datalist>

    @if($artikli->isEmpty())
        <div class="text-center py-12 text-gray-500">
            <p class="font-medium text-gray-700 mb-1">Na skladištu još nema ničega.</p>
            <p class="text-sm">Unesite prvu nabavu. Od tog datuma AgroLog oduzima potrošnju iz prskanja i gnojidbe.</p>
        </div>
    @else
        <div class="flex gap-1 mb-3 text-sm no-print">
            @foreach(['' => 'Sve', 'szb' => 'Sredstva', 'gnojivo' => 'Gnojiva'] as $k => $v)
                <button wire:click="$set('filterVrsta', '{{ $k }}')"
                        class="px-3 py-1 rounded-full border {{ $filterVrsta === $k ? 'bg-green-600 border-green-600 text-white' : 'bg-white border-gray-300 text-gray-600 hover:bg-gray-50' }}">{{ $v }}</button>
            @endforeach
        </div>

        <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm agrolog-table mob-kartice">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <th class="px-3 py-2 border border-gray-200">Naziv</th>
                    <th class="px-3 py-2 border border-gray-200 text-right">Nabavljeno</th>
                    <th class="px-3 py-2 border border-gray-200 text-right">Potrošeno</th>
                    <th class="px-3 py-2 border border-gray-200 text-right">Stanje</th>
                    <th class="px-3 py-2 border border-gray-200 text-right">Cijena / jed.</th>
                    <th class="px-3 py-2 border border-gray-200 text-right">Vrijednost</th>
                    <th class="px-3 py-2 border border-gray-200 no-print"></th>
                </tr>
            </thead>
            <tbody>
            @foreach($prikaz as $a)
                @php $j = $a['jedinica']; $kid = md5($a['kljuc']); @endphp
                <tr wire:key="art-{{ $kid }}" class="{{ $a['status'] === 'minus' ? 'bg-red-50' : ($a['status'] === 'malo' ? 'bg-amber-50' : '') }}">
                    <td class="mob-puna px-3 py-2 border border-gray-200">
                        <button wire:click="otvori('{{ $kid }}')" class="font-medium text-gray-800 hover:text-green-700 text-left">{{ $a['naziv'] }}</button>
                        <div class="text-xs text-gray-400">{{ $a['vrsta'] === 'szb' ? 'Sredstvo' : 'Gnojivo' }} · prati se od {{ $a['od']->format('d.m.Y.') }}</div>
                    </td>
                    <td data-label="Nabavljeno" class="px-3 py-2 border border-gray-200 text-right whitespace-nowrap">{{ $b($a['nabavljeno'] + $a['korekcije'], 3) }} {{ $j }}</td>
                    <td data-label="Potrošeno" class="px-3 py-2 border border-gray-200 text-right whitespace-nowrap">{{ $b($a['potroseno'], 3) }} {{ $j }}</td>
                    <td data-label="Stanje" class="px-3 py-2 border border-gray-200 text-right whitespace-nowrap font-semibold {{ $a['status'] === 'minus' ? 'text-red-700' : ($a['status'] === 'malo' ? 'text-amber-700' : 'text-green-700') }}">
                        {{ $b($a['stanje'], 3) }} {{ $j }}
                        @if($a['status'] === 'minus')<div class="text-[11px] font-normal">više potrošeno nego nabavljeno</div>
                        @elseif($a['status'] === 'malo')<div class="text-[11px] font-normal">pri kraju</div>@endif
                    </td>
                    <td data-label="Cijena / jed." class="px-3 py-2 border border-gray-200 text-right whitespace-nowrap">{{ $a['cijena_po_jedinici'] !== null ? $b($a['cijena_po_jedinici']) . ' €/' . $j : '—' }}</td>
                    <td data-label="Vrijednost" class="px-3 py-2 border border-gray-200 text-right whitespace-nowrap">{{ $a['vrijednost'] !== null ? $b($a['vrijednost']) . ' €' : '—' }}</td>
                    <td class="mob-puna mob-akcije px-3 py-2 border border-gray-200 no-print whitespace-nowrap text-right">
                        <button wire:click="novaNabava('{{ $a['vrsta'] }}', '{{ $kid }}')" class="btn-xs-green">+ Nabava</button>
                        <button wire:click="otvori('{{ $kid }}')" class="btn-xs-gray">{{ $otvoren === $kid ? 'Zatvori' : 'Detalji' }}</button>
                    </td>
                </tr>
                @if($otvoren === $kid)
                <tr wire:key="det-{{ $kid }}">
                    <td colspan="7" class="mob-puna px-3 py-3 border border-gray-200 bg-gray-50">
                        <div class="grid gap-4 lg:grid-cols-2">
                            <div>
                                <h5 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Ulazi</h5>
                                <ul class="divide-y divide-gray-200 bg-white rounded border border-gray-200">
                                    @foreach(array_reverse($a['ulazi']) as $u)
                                        <li class="px-3 py-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                                            <span class="text-gray-500 w-24">{{ $u->datum->format('d.m.Y.') }}</span>
                                            <span class="font-medium {{ $u->kolicina < 0 ? 'text-red-700' : '' }}">{{ $u->kolicina > 0 ? '+' : '' }}{{ $b($u->kolicina, 3) }} {{ $u->jedinica }}</span>
                                            @if($u->korekcija)<span class="text-xs px-2 rounded-full bg-gray-200 text-gray-600">inventura</span>@endif
                                            @if($u->cijena !== null)<span class="text-gray-600">{{ $b($u->cijena) }} €</span>@endif
                                            @if($u->dobavljac)<span class="text-gray-500">{{ $u->dobavljac }}</span>@endif
                                            @if($u->napomena && ! $u->korekcija)<span class="text-gray-400 text-xs">{{ $u->napomena }}</span>@endif
                                            <span class="ml-auto flex gap-1">
                                                @unless($u->korekcija)<button wire:click="uredi({{ $u->id }})" class="btn-xs-blue">Uredi</button>@endunless
                                                <button wire:click="obrisi({{ $u->id }})" wire:confirm="Obrisati ovaj ulaz sa skladišta?" class="btn-xs-red">✕</button>
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="mt-3 bg-white rounded border border-gray-200 p-3">
                                    <p class="text-xs text-gray-600 mb-2"><strong>Inventura:</strong> ako se izračunato stanje ne slaže s onim što stvarno imate, upišite stvarnu količinu. AgroLog doda ispravak za razliku.</p>
                                    <div class="flex gap-2 items-center">
                                        <input type="text" inputmode="decimal" wire:model="inventura.{{ $kid }}" placeholder="stvarno stanje" class="form-input w-32">
                                        <span class="text-gray-500">{{ $j }}</span>
                                        <button wire:click="spremiInventuru('{{ $kid }}')" class="btn-xs-gray">Spremi stanje</button>
                                    </div>
                                    @error('inventura.' . $kid)<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            <div>
                                <h5 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Potrošnja iz evidencije {{ $a['vrsta'] === 'szb' ? 'prskanja' : 'gnojidbe' }}</h5>
                                @if(empty($a['potrosnja']))
                                    <p class="text-sm text-gray-500 bg-white rounded border border-gray-200 px-3 py-2">Od {{ $a['od']->format('d.m.Y.') }} još nema zapisa s ovim {{ $a['vrsta'] === 'szb' ? 'sredstvom' : 'gnojivom' }}.</p>
                                @else
                                    <ul class="divide-y divide-gray-200 bg-white rounded border border-gray-200">
                                        @foreach(array_reverse($a['potrosnja']) as $p)
                                            <li class="px-3 py-2 flex flex-wrap gap-x-3 gap-y-1">
                                                <span class="text-gray-500 w-24">{{ $p['datum']->format('d.m.Y.') }}</span>
                                                <span class="text-gray-700">{{ $p['arkod'] }} · {{ $p['kultura'] }}</span>
                                                <span class="text-gray-400 text-xs self-center">{{ $b($p['doza'], 3) }} {{ $j }}/ha × {{ $b($p['povrsina']) }} ha</span>
                                                <span class="ml-auto font-medium text-gray-800">−{{ $b($p['kolicina'], 3) }} {{ $j }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @endif
            @endforeach
            </tbody>
        </table>
        </div>
    @endif

    <p class="text-xs text-gray-400 mt-4">
        Potrošnja = doza po hektaru × tretirana površina, iz zapisa od datuma prve nabave tog artikla.
        Naziv u prskanju ili gnojidbi mora biti isti kao na skladištu (velika i mala slova nisu bitna).
    </p>
@endif
</div>
