<div>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h2 class="text-xl font-bold text-gray-800">Moje parcele i kulture</h2>
        <button wire:click="novaParcela" class="btn-primary">+ Nova parcela</button>
    </div>

    {{-- Forma za parcelu --}}
    @if($showParcelaForm)
    <div class="bg-yellow-50 border border-yellow-200 rounded p-4 mb-4">
        <h3 class="font-semibold text-gray-700 mb-3">{{ $rucnoUnos ? 'Nova parcela bez ARKOD-a' : 'Nova parcela' }}</h3>
        @if($rucnoUnos)
        @include('livewire.partials.ucrtaj-parcelu')
        @else
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-600 mb-1">ARKOD broj</label>
                <input type="text" wire:model.live.blur="parcelaForm.arkod_broj" placeholder="npr. 1708275" class="form-input w-40" inputmode="numeric">
                @error('parcelaForm.arkod_broj')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                <p class="mt-1 text-[11px] text-gray-500">Ne znate broj? <a href="https://preglednik.arkod.hr/ARKOD-Web/" target="_blank" rel="noopener" class="text-green-700 underline">ARKOD preglednik</a> · <a href="{{ route('docs') }}#sto-je-arkod" target="_blank" class="text-green-700 underline">Što je ARKOD?</a></p>
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">Površina (ha)</label>
                <input type="number" wire:key="povrsina-{{ $arkodNadjen['id'] ?? 'x' }}-{{ $parcelaForm['povrsina_ha'] }}" wire:model="parcelaForm.povrsina_ha" step="0.01" placeholder="1.60" class="form-input w-28">
                @error('parcelaForm.povrsina_ha')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-2">
                <button wire:click="saveParcela" class="btn-xs-green">{{ $arkodNadjen ? 'Potvrdi i spremi' : 'Spremi' }}</button>
                <button wire:click="cancelParcelaEdit" class="btn-xs-gray">Odustani</button>
            </div>
            <span wire:loading wire:target="parcelaForm.arkod_broj" class="text-xs text-gray-500">Tražim parcelu u ARKOD-u…</span>
        </div>
        @include('livewire.partials.arkod-pregled')
        <button type="button" wire:click="ucrtajNaKarti" class="btn-xs-blue mt-3">Nemam ARKOD, ucrtaj na karti</button>
        @endif
    </div>
    @endif

    {{-- Datalist za autocomplete kulture --}}
    <datalist id="kulture-prijedlozi">
        @foreach($this->postojeceKulture as $naziv)
            <option value="{{ $naziv }}">
        @endforeach
    </datalist>

    @if($poruka)
    <div class="mb-4 rounded border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800 flex justify-between gap-3">
        <span>{{ $poruka }}</span>
        <button wire:click="$set('poruka', '')" class="text-amber-600 hover:text-amber-900">&#10005;</button>
    </div>
    @endif

    {{-- Forma za novu kulturu / zamjenu --}}
    @if($showKulturaForm)
    @php
        $stara = $zamjenaZaId ? \App\Models\Kultura::with('parcela')->find($zamjenaZaId) : null;
    @endphp
    <div class="bg-blue-50 border border-blue-200 rounded p-4 mb-4">
        @if($stara)
            <h3 class="font-semibold text-gray-700 mb-1">Zamijeni kulturu na parceli {{ $stara->parcela?->arkod_broj }}</h3>
            <p class="text-sm text-gray-600 mb-3">
                <strong class="uppercase">{{ $stara->naziv }} ({{ $stara->godina }})</strong> bit će označena kao završena i
                ostaje u evidenciji sa svim zapisima gnojidbe i prskanja. Nova kultura dobiva svoju, praznu evidenciju.
            </p>
        @else
            <h3 class="font-semibold text-gray-700 mb-3">Dodaj kulturu na parcelu</h3>
        @endif
        <div class="flex flex-wrap gap-3 items-end">
            @if($stara)
            <div>
                <label class="block text-xs text-gray-600 mb-1">{{ $stara->naziv }} završena (žetva/berba)</label>
                <input type="date" wire:model="zavrsenaDatum" class="form-input w-40">
                @error('zavrsenaDatum')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            @endif
            <div>
                <label class="block text-xs text-gray-600 mb-1">Kultura</label>
                <input type="text" wire:model.live.blur="kulturaForm.naziv" placeholder="npr. Pšenica"
                       list="kulture-prijedlozi" autocomplete="off" class="form-input w-40">
                @error('kulturaForm.naziv')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1" title="Naziv usjeva s Ministarstvova popisa — iz njega se uzima EPPO kod za evidenciju SZB">Usjev za evidenciju (EPPO)</label>
                <input type="text" wire:key="eppo-{{ $kulturaForm['eppo_naziv'] ?? '' }}" wire:model="kulturaForm.eppo_naziv" placeholder="prepozna se iz naziva"
                       list="eppo-popis" autocomplete="off" class="form-input w-56">
                @error('kulturaForm.eppo_naziv')<p class="text-red-500 text-xs mt-1 max-w-xs">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">Posađena površina (ha)</label>
                <input type="number" wire:key="ha-{{ $kulturaForm['posadjena_povrsina_ha'] ?? '' }}" wire:model="kulturaForm.posadjena_povrsina_ha" step="0.01" placeholder="1.60" class="form-input w-28">
                @if($this->slobodno !== null)<p class="text-[11px] text-gray-500 mt-1">slobodno {{ number_format($this->slobodno, 2, ',', '.') }} ha</p>@endif
                @error('kulturaForm.posadjena_povrsina_ha')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">Godina</label>
                <input type="number" wire:model.live.blur="kulturaForm.godina" min="2000" max="2100" class="form-input w-24">
                @error('kulturaForm.godina')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            @include('livewire.partials.kultura-nasad')
            <div class="flex gap-2">
                @if($stara)
                    <button wire:click="saveKultura" class="btn-xs-green">Spremi novu kulturu</button>
                @else
                    <button wire:click="saveKultura(true)" class="btn-xs-green">Spremi i nova</button>
                    <button wire:click="saveKultura" class="btn-xs-blue">Završi</button>
                @endif
                <button wire:click="closeKulturaForm" class="btn-xs-gray">Odustani</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Lista parcela --}}
    <div class="space-y-3">
        @forelse($this->parcele as $parcela)
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            {{-- Parcela header --}}
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between bg-green-50 px-4 py-2">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                    <span class="flex items-center gap-1.5">
                        <button type="button" wire:click="prikaziKartu({{ $parcela->id }})" wire:loading.attr="disabled"
                                class="w-7 h-7 rounded-md flex items-center justify-center text-green-700 hover:bg-green-100 transition" title="Prikaži parcelu na karti">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        </button>
                        <span class="font-bold text-green-800 text-lg">{{ $parcela->arkod_broj }}</span>
                        @if($parcela->rucno_ucrtana)<span class="text-[11px] rounded bg-gray-100 text-gray-600 px-1.5 py-0.5" title="Nije službena ARKOD parcela">ručno ucrtana</span>@endif
                    </span>
                    <span class="text-sm text-gray-600">{{ number_format($parcela->povrsina_ha, 2) }} ha</span>
                    @if($parcela->arkod_povrsina_ha !== null && (float) $parcela->povrsina_ha > (float) $parcela->arkod_povrsina_ha + 0.005)
                        <span class="text-xs text-amber-700" title="Upisana površina veća je od površine u ARKOD-u">ARKOD: {{ number_format($parcela->arkod_povrsina_ha, 2) }} ha</span>
                    @endif
                    @if($parcela->arkod_vrsta_uporabe)
                        <span class="hidden sm:inline text-xs text-gray-400">{{ $parcela->arkod_vrsta_uporabe }}{{ $parcela->arkod_naziv ? ' · ' . $parcela->arkod_naziv : '' }}</span>
                    @endif
                    <span class="text-xs text-gray-400">{{ $parcela->kulture->whereNull('zavrsena_at')->count() }} aktivnih kultura</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    @php
                        $slobodnoP = max(0, (float) $parcela->povrsina_ha - (float) $parcela->kulture->whereNull('zavrsena_at')->filter(fn ($k) => (int) $k->godina === (int) date('Y') || ($k->trajni && (int) $k->godina <= (int) date('Y')))->sum('posadjena_povrsina_ha'));
                    @endphp
                    @if($slobodnoP < 0.01)
                        <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-500 cursor-help" title="Kulture zauzimaju cijelu parcelu. Za novu kulturu koristi „Zamijeni kulturu” ili „Završi” uz postojeću, a kod nasada „Iskrči”.">Parcela popunjena</span>
                    @else
                        <button wire:click="openKulturaForm({{ $parcela->id }})" class="btn-xs-blue" title="Slobodno {{ number_format($slobodnoP, 2, ',', '.') }} ha">+ Kultura <span class="opacity-75">({{ number_format($slobodnoP, 2, ',', '.') }} ha)</span></button>
                    @endif
                    <button wire:click="editParcela({{ $parcela->id }})" class="btn-xs-gray">Uredi</button>
                    <button wire:click="deleteParcela({{ $parcela->id }})" wire:confirm="Obrisati parcelu i sve kulture?" class="btn-xs-red">&#10005;</button>
                </div>
            </div>

            {{-- Inline edit forma --}}
            @if($editParcelaId === $parcela->id)
            <div class="bg-yellow-50 border-t border-yellow-200 px-4 py-3">
                @if($rucnoUnos)
                @include('livewire.partials.ucrtaj-parcelu')
                @else
                <div class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">ARKOD broj</label>
                        <input type="text" wire:model.live.blur="parcelaForm.arkod_broj" placeholder="npr. 1708275" class="form-input w-40" inputmode="numeric">
                        @error('parcelaForm.arkod_broj')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Površina (ha)</label>
                        <input type="number" wire:key="povrsina-{{ $arkodNadjen['id'] ?? 'x' }}-{{ $parcelaForm['povrsina_ha'] }}" wire:model="parcelaForm.povrsina_ha" step="0.01" placeholder="1.60" class="form-input w-28">
                        @error('parcelaForm.povrsina_ha')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex gap-2">
                        <button wire:click="saveParcela" class="btn-xs-green">{{ $arkodNadjen ? 'Potvrdi i spremi' : 'Spremi' }}</button>
                        <button wire:click="cancelParcelaEdit" class="btn-xs-gray">Odustani</button>
                    </div>
                </div>
                @include('livewire.partials.arkod-pregled')
                <button type="button" wire:click="ucrtajNaKarti" class="btn-xs-blue mt-3">Nemam ARKOD, ucrtaj na karti</button>
                @endif
            </div>
            @endif

            {{-- Kulture na parceli --}}
            @if($parcela->kulture->isNotEmpty())
            <table class="w-full text-sm mob-kartice mob-kulture">
                <thead>
                    <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <th class="px-4 py-1 text-left">Kultura</th>
                        <th class="px-4 py-1 text-right">Posađena površina (ha)</th>
                        <th class="px-4 py-1 text-center">Godina</th>
                        <th class="px-4 py-1 text-center">Gnojidbi</th>
                        <th class="px-4 py-1 text-center">Tretmana</th>
                        <th class="px-4 py-1 text-center">Akcije</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parcela->kulture as $kultura)
                    @if($editKulturaId === $kultura->id)
                    <tr class="border-t border-blue-200 bg-blue-50">
                        <td colspan="6" class="mob-puna px-4 py-3">
                            <div class="flex flex-wrap gap-3 items-end">
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Kultura</label>
                                    <input type="text" wire:model.live.blur="kulturaForm.naziv" placeholder="npr. Pšenica"
                                           list="kulture-prijedlozi" autocomplete="off" class="form-input w-40">
                                    @error('kulturaForm.naziv')<p class="text-red-500 text-xs mt-1 max-w-xs">{{ $message }}</p>@enderror
                                    @if(($kultura->gnojidbe_count + $kultura->prskanja_count + $kultura->berbe_count) > 0)
                                        <p class="text-xs text-gray-500 mt-1 max-w-xs">Naziv se ne može mijenjati jer kultura ima zapise. Za novu kulturu koristi „Zamijeni kulturu".</p>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1" title="Naziv usjeva s Ministarstvova popisa — iz njega se uzima EPPO kod za evidenciju SZB">Usjev za evidenciju (EPPO)</label>
                                    <input type="text" wire:key="eppo-{{ $kulturaForm['eppo_naziv'] ?? '' }}" wire:model="kulturaForm.eppo_naziv" placeholder="prepozna se iz naziva"
                                           list="eppo-popis" autocomplete="off" class="form-input w-56">
                                    @error('kulturaForm.eppo_naziv')<p class="text-red-500 text-xs mt-1 max-w-xs">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Posađena površina (ha)</label>
                                    <input type="number" wire:key="ha-{{ $kulturaForm['posadjena_povrsina_ha'] ?? '' }}" wire:model="kulturaForm.posadjena_povrsina_ha" step="0.01" placeholder="1.60" class="form-input w-28">
                @if($this->slobodno !== null)<p class="text-[11px] text-gray-500 mt-1">slobodno {{ number_format($this->slobodno, 2, ',', '.') }} ha</p>@endif
                                    @error('kulturaForm.posadjena_povrsina_ha')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Godina</label>
                                    <input type="number" wire:model.live.blur="kulturaForm.godina" min="2000" max="2100" class="form-input w-24">
                                    @error('kulturaForm.godina')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                                </div>
                                @include('livewire.partials.kultura-nasad')
                                <div class="flex gap-2">
                                    <button wire:click="saveKultura" class="btn-xs-blue">Završi</button>
                                    <button wire:click="cancelKulturaEdit" class="btn-xs-gray">Odustani</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @else
                    @php
                        $imaZapise = ($kultura->gnojidbe_count + $kultura->prskanja_count + $kultura->berbe_count) > 0;
                    @endphp
                    <tr class="border-t border-gray-100 hover:bg-gray-50 {{ $kultura->zavrsena_at ? 'text-gray-400 bg-gray-50/60' : '' }}">
                        <td class="mob-puna px-4 py-2 font-medium uppercase">
                            {{ $kultura->naziv }}
                            @if($kultura->eppo_kod)
                                <span class="ml-1 text-[10px] font-normal normal-case text-gray-400" title="{{ $kultura->eppo_naziv }}">EPPO {{ $kultura->eppo_kod }}</span>
                            @elseif(! $kultura->zavrsena_at)
                                <button wire:click="editKultura({{ $kultura->id }})" class="ml-1 text-[10px] font-normal normal-case text-amber-600 hover:underline" title="Za evidenciju SZB od 2027. odaberite usjev s Ministarstvova popisa">bez EPPO koda</button>
                            @endif
                            @if($kultura->trajni)
                                <span class="ml-2 normal-case text-xs font-normal bg-green-50 text-green-800 rounded px-2 py-0.5">trajni nasad{{ $kultura->sorta ? ' · ' . $kultura->sorta : '' }}{{ $kultura->godina_sadnje ? ' · sađeno ' . $kultura->godina_sadnje . '.' : '' }}{{ $kultura->broj_stabala ? ' · ' . number_format($kultura->broj_stabala, 0, ',', '.') . ' stabala' : '' }}</span>
                            @elseif(! $kultura->zavrsena_at && \App\Models\Kultura::jeTrajniNaziv($kultura->naziv))
                                <button wire:click="oznaciTrajni({{ $kultura->id }})" class="ml-1 text-[11px] font-normal normal-case text-green-700 underline" title="Voćnjak, vinograd, maslinik: ostaje na parceli svake godine">označi kao trajni nasad</button>
                            @endif
                            @if($kultura->berbe_count)
                                <span class="ml-2 normal-case text-xs font-normal bg-green-50 text-green-800 rounded px-2 py-0.5">berbi: {{ $kultura->berbe_count }}</span>
                            @endif
                            @if($kultura->zavrsena_at)
                                <span class="ml-2 normal-case text-xs font-normal bg-gray-200 text-gray-600 rounded px-2 py-0.5">{{ $kultura->trajni ? 'iskrčen' : 'završena' }} {{ $kultura->zavrsena_at->format('d.m.Y.') }}</span>
                            @endif
                            @if($kultura->prinos_t_ha !== null)
                                <span class="ml-2 normal-case text-xs font-normal bg-green-50 text-green-800 rounded px-2 py-0.5" title="Prinos{{ $kultura->zetva_datum ? ', žetva ' . $kultura->zetva_datum->format('d.m.Y.') : '' }}">{{ number_format($kultura->prinos_t_ha, 2, ',', '.') }} t/ha{{ $kultura->cijena_t !== null ? ' · ' . number_format($kultura->cijena_t, 0, ',', '.') . ' €/t' : '' }}</span>
                            @endif
                        </td>
                        <td data-label="Hektara" class="px-4 py-2 text-right">{{ number_format($kultura->posadjena_povrsina_ha, 2) }}</td>
                        <td data-label="Godina" class="px-4 py-2 text-center">{{ $kultura->godina }}</td>
                        <td data-label="Gnojidbi" class="px-4 py-2 text-center text-gray-500">{{ $kultura->gnojidbe_count }}</td>
                        <td data-label="Tretmana" class="px-4 py-2 text-center text-gray-500">{{ $kultura->prskanja_count }}</td>
                        <td class="mob-puna mob-akcije px-4 py-2 text-center whitespace-nowrap">
                            @if($kultura->zavrsena_at)
                                <button wire:click="vratiKulturu({{ $kultura->id }})" class="btn-xs-gray" title="Ponovno aktiviraj (ako je označena greškom)">Vrati</button>
                            @else
                                @unless($kultura->trajni)
                                    <button wire:click="zamijeniKulturu({{ $kultura->id }})" class="btn-xs-green" title="Nova kultura na parceli, stara ostaje u evidenciji">Zamijeni kulturu</button>
                                @endunless
                                <button wire:click="editKultura({{ $kultura->id }})" class="btn-xs-blue">Uredi</button>
                                <button wire:click="zavrsiKulturu({{ $kultura->id }})" wire:confirm="{{ $kultura->trajni ? 'Označiti nasad ' . $kultura->naziv . ' kao iskrčen? Površina se oslobađa, a zapisi ostaju u evidenciji.' : 'Označiti ' . $kultura->naziv . ' kao završenu (požnjevenu)? Zapisi ostaju u evidenciji.' }}" class="btn-xs-gray">{{ $kultura->trajni ? 'Iskrči' : 'Završi' }}</button>
                            @endif
                            <button wire:click="otvoriPrinos({{ $kultura->id }})" class="btn-xs-gray" title="{{ $kultura->trajni ? 'Potpore i ostali troškovi po godinama, za dobit po hektaru' : 'Prinos, cijena i ostali troškovi — za dobit po hektaru (Troškovi)' }}">{{ $kultura->trajni ? 'Troškovi i potpore' : 'Prinos' }}</button>
                            @unless($imaZapise)
                                <button wire:click="deleteKultura({{ $kultura->id }})" wire:confirm="Obrisati kulturu?" class="btn-xs-red">&#10005;</button>
                            @endunless
                        </td>
                    </tr>
                    @if($prinosId === $kultura->id)
                    <tr class="bg-green-50">
                        <td colspan="6" class="mob-puna px-4 py-3">
                            @if($kultura->trajni)
                            <p class="text-sm font-semibold text-gray-700 mb-2">Troškovi i potpore: {{ $kultura->naziv }} ({{ number_format($kultura->posadjena_povrsina_ha, 2, ',', '.') }} ha)</p>
                            <div class="flex flex-wrap gap-3 items-end">
                                <label class="block text-xs text-gray-600">Godina
                                    <input type="number" wire:model.live.blur="prinosForm.godina" min="1900" max="2100" class="form-input w-24 mt-1 block">
                                    @error('prinosForm.godina')<span class="text-red-500">{{ $message }}</span>@enderror</label>
                                <label class="block text-xs text-gray-600">Potpore (€/ha) <span class="text-gray-400">neob.</span>
                                    <input type="number" step="0.01" wire:key="np-{{ $prinosForm['godina'] ?? '' }}" wire:model="prinosForm.potpora_ha" class="form-input w-28 mt-1 block"></label>
                                <label class="block text-xs text-gray-600">Ostali troškovi (€) <span class="text-gray-400">sadnice, rezidba, usluge…</span>
                                    <input type="number" step="0.01" wire:key="no-{{ $prinosForm['godina'] ?? '' }}" wire:model="prinosForm.trosak_ostalo" class="form-input w-32 mt-1 block"></label>
                                <div class="flex gap-2">
                                    <button wire:click="spremiPrinos" class="btn-xs-green">Spremi</button>
                                    <button wire:click="odustaniPrinos" class="btn-xs-gray">Odustani</button>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-600">Prinos i prodaja upisuju se kroz <a href="{{ route('berba') }}" class="text-green-700 underline">Berbu</a>, a dobit po hektaru vidi se na stranici <a href="{{ route('troskovi') }}" class="text-green-700 underline">Troškovi</a>.</p>
                            @else
                            <p class="text-sm font-semibold text-gray-700 mb-2">Prinos i prihod: {{ $kultura->naziv }} {{ $kultura->godina }}. ({{ number_format($kultura->posadjena_povrsina_ha, 2, ',', '.') }} ha)</p>
                            <div class="flex flex-wrap gap-3 items-end">
                                @unless($kultura->berbe_count)
                                <label class="block text-xs text-gray-600">Datum žetve / berbe
                                    <input type="date" wire:model="prinosForm.zetva_datum" class="form-input w-40 mt-1 block"></label>
                                <label class="block text-xs text-gray-600">Prinos (t/ha)
                                    <input type="number" step="0.01" wire:model.live.blur="prinosForm.prinos_t_ha" placeholder="npr. 7.2" class="form-input w-28 mt-1 block">
                                    @error('prinosForm.prinos_t_ha')<span class="text-red-500">{{ $message }}</span>@enderror</label>
                                <label class="block text-xs text-gray-600">Cijena (€/t)
                                    <input type="number" step="0.01" wire:model.live.blur="prinosForm.cijena_t" placeholder="npr. 210" class="form-input w-28 mt-1 block"></label>
                                @endunless
                                <label class="block text-xs text-gray-600">Potpore (€/ha) <span class="text-gray-400">neob.</span>
                                    <input type="number" step="0.01" wire:model.live.blur="prinosForm.potpora_ha" placeholder="npr. 150" class="form-input w-28 mt-1 block"></label>
                                <label class="block text-xs text-gray-600">Sjeme (€ ukupno) <span class="text-gray-400">neob.</span>
                                    <input type="number" step="0.01" wire:model="prinosForm.trosak_sjeme" class="form-input w-32 mt-1 block"></label>
                                <label class="block text-xs text-gray-600">Ostali troškovi (€) <span class="text-gray-400">usluge, zakup…</span>
                                    <input type="number" step="0.01" wire:model="prinosForm.trosak_ostalo" class="form-input w-32 mt-1 block"></label>
                                <div class="flex gap-2">
                                    <button wire:click="spremiPrinos" class="btn-xs-green">Spremi</button>
                                    <button wire:click="odustaniPrinos" class="btn-xs-gray">Odustani</button>
                                </div>
                            </div>
                            @php
                                $ha = (float) $kultura->posadjena_povrsina_ha;
                                $pr = is_numeric($prinosForm['prinos_t_ha'] ?? null) ? (float) $prinosForm['prinos_t_ha'] : null;
                                $cj = is_numeric($prinosForm['cijena_t'] ?? null) ? (float) $prinosForm['cijena_t'] : null;
                                $pt = is_numeric($prinosForm['potpora_ha'] ?? null) ? (float) $prinosForm['potpora_ha'] : 0;
                            @endphp
                            @if($kultura->berbe_count)
                                <p class="mt-2 text-xs text-gray-600">Prinos i prodaja računaju se iz <a href="{{ route('berba') }}" class="text-green-700 underline">upisanih berbi</a>.</p>
                            @elseif($pr !== null)
                                <p class="mt-2 text-xs text-gray-600">
                                    Ukupno {{ number_format($pr * $ha, 2, ',', '.') }} t
                                    @if($cj !== null) · prodaja {{ number_format($pr * $ha * $cj, 2, ',', '.') }} € @endif
                                    @if($pt) · potpore {{ number_format($pt * $ha, 2, ',', '.') }} € @endif
                                    — dobit po hektaru vidi se na stranici <a href="{{ route('troskovi') }}" class="text-green-700 underline">Troškovi</a>.
                                </p>
                            @endif
                            @endif
                        </td>
                    </tr>
                    @endif
                    @endif
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="px-4 py-3 text-sm text-gray-400 italic">Nema kultura na ovoj parceli. Dodajte klikom na + Kultura.</p>
            @endif
        </div>
        @empty
        <div class="text-center py-12 text-gray-400">
            <p class="text-lg">Nema dodanih parcela.</p>
            <p class="text-sm mt-1">Kliknite <strong>+ Nova parcela</strong> za početak.</p>
        </div>
        @endforelse
    </div>
    {{-- Popup s kartom parcele (ikona karte uz ARKOD broj) --}}
    <div wire:ignore x-data="{ otvoren: false, d: {} }"
         @arkod-karta.window="d = $event.detail; otvoren = true; $nextTick(() => { if (d.poligon) agroKarta.nacrtaj($refs.karta, d.poligon) })"
         @keydown.escape.window="otvoren = false"
         x-show="otvoren" x-cloak
         class="fixed inset-0 z-[1000] flex items-center justify-center bg-black/50 p-4">
        <div @click.outside="otvoren = false" class="w-full max-w-3xl overflow-hidden rounded-xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b px-5 py-3">
                <h3 class="font-semibold text-gray-800"><span x-text="d.rucno ? '' : 'ARKOD '"></span><span x-text="d.arkod"></span> <span x-show="d.rucno" class="ml-1 text-xs font-normal text-gray-500">ručno ucrtana</span></h3>
                <button type="button" @click="otvoren = false" class="w-8 h-8 rounded-full text-gray-500 hover:bg-gray-100" aria-label="Zatvori">&#10005;</button>
            </div>
            <div x-show="d.poligon" x-ref="karta" class="h-[60vh] bg-gray-100"></div>
            <div x-show="!d.poligon" class="px-6 py-12 text-center text-sm text-gray-500">
                Ovaj ARKOD broj nije pronađen u javnom ARKOD-u Agencije za plaćanja (ili servis trenutno ne odgovara). Provjerite broj u „Uredi”.
            </div>
            <div class="flex flex-wrap gap-x-6 gap-y-1 px-5 py-3 text-sm text-gray-600">
                <span>Upisano: <b x-text="(d.povrsina ?? 0).toFixed(2).replace('.', ',') + ' ha'"></b></span>
                <template x-if="d.arkodPovrsina !== null && d.arkodPovrsina !== undefined">
                    <span>Prema ARKOD-u: <b x-text="d.arkodPovrsina.toFixed(2).replace('.', ',') + ' ha'"></b></span>
                </template>
                <span x-show="d.vrsta" x-text="d.vrsta"></span>
                <span x-show="d.naziv" x-text="d.naziv" class="text-gray-400"></span>
            </div>
        </div>
    </div>
</div>
