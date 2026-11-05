<div>
    @php
        $strojevi = $this->strojevi;
        $aktivni = $strojevi->flatMap(fn ($s) => $s->rokovi->whereNull('obnovljen_at'));
        $istekli = $aktivni->filter(fn ($r) => $r->status === 'istekao')->count();
        $uskoro = $aktivni->filter(fn ($r) => $r->status === 'uskoro')->count();
        $V = \App\Support\Mehanizacija::VRSTE;
        $R = \App\Support\Mehanizacija::ROKOVI;
        $ikona = ['traktor' => '🚜', 'kombajn' => '🌾', 'prskalica' => '💧', 'rasipac' => '🧂', 'prikljucak' => '⚙️', 'prikolica' => '🛒', 'vozilo' => '🚙', 'ostalo' => '🔧'];
        $novac = fn ($v) => number_format((float) $v, 2, ',', '.') . ' €';
    @endphp

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
        <div class="flex flex-wrap gap-2 text-sm">
            <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700">Strojeva i vozila: <strong>{{ $strojevi->count() }}</strong></span>
            @if($istekli)<span class="px-3 py-1 rounded-full bg-red-100 text-red-700">Isteklo: <strong>{{ $istekli }}</strong></span>@endif
            @if($uskoro)<span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800">Uskoro ističe: <strong>{{ $uskoro }}</strong></span>@endif
        </div>
        <button wire:click="noviStroj" class="btn-primary">+ Dodaj stroj ili vozilo</button>
    </div>

    {{-- FORME --}}
    @if($forma === 'stroj')
        <div class="mb-6 rounded-lg border {{ $urediId ? 'border-blue-200 bg-blue-50' : 'border-yellow-200 bg-yellow-50' }} p-4">
            <h4 class="font-semibold text-gray-800 mb-3">{{ $urediId ? 'Uredi stroj' : 'Novi stroj ili vozilo' }}</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <label class="block text-sm"><span class="text-gray-600">Vrsta</span>
                    <select wire:model.live="f.vrsta" class="form-input w-full mt-1">@foreach($V as $k => $v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
                </label>
                <label class="block text-sm lg:col-span-2"><span class="text-gray-600">Naziv (marka i model)</span>
                    <input type="text" wire:model="f.naziv" placeholder="npr. John Deere 6120M" class="form-input w-full mt-1">
                    @error('f.naziv')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm"><span class="text-gray-600">Registracija <span class="text-gray-400">neobavezno</span></span>
                    <input type="text" wire:model="f.registracija" placeholder="npr. OS 123-AB" class="form-input w-full mt-1 uppercase">
                </label>
                <label class="block text-sm"><span class="text-gray-600">Godina proizvodnje <span class="text-gray-400">neobavezno</span></span>
                    <input type="number" wire:model="f.godina" placeholder="2018" class="form-input w-full mt-1">
                    @error('f.godina')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm"><span class="text-gray-600">Serijski broj / šasija <span class="text-gray-400">neobavezno</span></span>
                    <input type="text" wire:model="f.serijski_broj" class="form-input w-full mt-1">
                </label>
                <label class="block text-sm sm:col-span-2 lg:col-span-3"><span class="text-gray-600">Napomena <span class="text-gray-400">neobavezno</span></span>
                    <input type="text" wire:model="f.napomena" class="form-input w-full mt-1">
                </label>
            </div>
            @unless($urediId)
                <div class="mt-4 pt-3 border-t border-yellow-200">
                    <p class="text-sm font-medium text-gray-700 mb-2">Rokovi <span class="font-normal text-gray-400">(neobavezno, mogu se dodati i kasnije)</span></p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        @foreach(\App\Support\Mehanizacija::prijedloziRokova($f['vrsta'] ?? 'traktor') as $vr)
                            <label class="block text-sm" wire:key="brzi-{{ $vr }}"><span class="text-gray-600">{{ $R[$vr][0] }} vrijedi do</span>
                                <input type="date" wire:model="f.rokovi.{{ $vr }}" class="form-input w-full mt-1">
                            </label>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Podsjetnik stiže mailom 30 dana prije isteka. To se može promijeniti uz svaki rok.</p>
                </div>
            @endunless
            <div class="flex gap-2 mt-4">
                <button wire:click="spremiStroj" class="btn-xs-green">Spremi</button>
                <button wire:click="odustani" class="btn-xs-gray">Odustani</button>
            </div>
        </div>
    @elseif($forma === 'rok')
        @php $zaStroj = $strojevi->firstWhere('id', (int) ($f['stroj_id'] ?? 0)); @endphp
        <div class="mb-6 rounded-lg border {{ $urediId ? 'border-blue-200 bg-blue-50' : 'border-yellow-200 bg-yellow-50' }} p-4">
            <h4 class="font-semibold text-gray-800 mb-1">{{ $obnavljaId ? 'Obnovljeno: sljedeći rok' : ($urediId ? 'Uredi rok' : 'Novi rok') }}</h4>
            <p class="text-sm text-gray-500 mb-3">{{ $zaStroj?->naziv }}{{ $zaStroj?->registracija ? ' · ' . $zaStroj->registracija : '' }}
                @if($obnavljaId) · predložen je datum za {{ ($R[$f['vrsta']][1] ?? 12) / 12 }} {{ ($R[$f['vrsta']][1] ?? 12) > 12 ? 'godine' : 'godinu' }} unaprijed, po potrebi ga ispravite @endif</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <label class="block text-sm"><span class="text-gray-600">Što</span>
                    <select wire:model.live="f.vrsta" class="form-input w-full mt-1">@foreach($R as $k => [$naziv]) <option value="{{ $k }}">{{ $naziv }}</option>@endforeach</select>
                </label>
                @if(($f['vrsta'] ?? '') === 'ostalo')
                    <label class="block text-sm"><span class="text-gray-600">Naziv roka</span>
                        <input type="text" wire:model="f.naziv" placeholder="npr. baždarenje" class="form-input w-full mt-1">
                        @error('f.naziv')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                    </label>
                @endif
                <label class="block text-sm"><span class="text-gray-600">Vrijedi do / rok</span>
                    <input type="date" wire:model="f.datum_isteka" class="form-input w-full mt-1">
                    @error('f.datum_isteka')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm"><span class="text-gray-600">Podsjetnik mailom</span>
                    <select wire:model="f.podsjetnik_dana" class="form-input w-full mt-1">
                        @foreach([7 => '7 dana prije', 14 => '14 dana prije', 30 => '30 dana prije', 60 => '60 dana prije', 90 => '90 dana prije', 0 => 'bez podsjetnika'] as $d => $t)<option value="{{ $d }}">{{ $t }}</option>@endforeach
                    </select>
                </label>
                <label class="block text-sm"><span class="text-gray-600">Iznos (€) <span class="text-gray-400">neobavezno</span></span>
                    <input type="number" step="0.01" wire:model="f.iznos" placeholder="npr. 180.00" class="form-input w-full mt-1">
                </label>
                <label class="block text-sm sm:col-span-2"><span class="text-gray-600">Napomena <span class="text-gray-400">neobavezno</span></span>
                    <input type="text" wire:model="f.napomena" placeholder="npr. broj police, osiguravatelj" class="form-input w-full mt-1">
                </label>
            </div>
            <div class="flex gap-2 mt-4">
                <button wire:click="spremiRok" class="btn-xs-green">Spremi</button>
                <button wire:click="odustani" class="btn-xs-gray">Odustani</button>
            </div>
        </div>
    @elseif($forma === 'servis')
        @php $zaStroj = $strojevi->firstWhere('id', (int) ($f['stroj_id'] ?? 0)); @endphp
        <div class="mb-6 rounded-lg border {{ $urediId ? 'border-blue-200 bg-blue-50' : 'border-yellow-200 bg-yellow-50' }} p-4">
            <h4 class="font-semibold text-gray-800 mb-1">{{ $urediId ? 'Uredi servis' : 'Upiši servis' }}</h4>
            <p class="text-sm text-gray-500 mb-3">{{ $zaStroj?->naziv }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <label class="block text-sm"><span class="text-gray-600">Datum</span>
                    <input type="date" wire:model="f.datum" class="form-input w-full mt-1">
                    @error('f.datum')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm sm:col-span-1 lg:col-span-3"><span class="text-gray-600">Što je rađeno</span>
                    <input type="text" wire:model="f.opis" placeholder="npr. zamjena ulja i filtera, remen" class="form-input w-full mt-1">
                    @error('f.opis')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm"><span class="text-gray-600">Radni sati (motosati) <span class="text-gray-400">neobavezno</span></span>
                    <input type="number" wire:model="f.radni_sati" placeholder="npr. 4250" class="form-input w-full mt-1">
                </label>
                <label class="block text-sm"><span class="text-gray-600">Trošak (€) <span class="text-gray-400">neobavezno</span></span>
                    <input type="number" step="0.01" wire:model="f.trosak" class="form-input w-full mt-1">
                </label>
                <label class="block text-sm sm:col-span-2"><span class="text-gray-600">Serviser <span class="text-gray-400">neobavezno</span></span>
                    <input type="text" wire:model="f.serviser" class="form-input w-full mt-1">
                </label>
            </div>
            @if(! $urediId && ! empty($f['ima_rok']))
                <label class="flex items-center gap-2 mt-3 text-sm text-gray-700">
                    <input type="checkbox" wire:model="f.pomakni_rok" class="rounded border-gray-300 text-green-600"> Pomakni rok sljedećeg servisa na godinu dana od ovog datuma
                </label>
            @endif
            <div class="flex gap-2 mt-4">
                <button wire:click="spremiServis" class="btn-xs-green">Spremi</button>
                <button wire:click="odustani" class="btn-xs-gray">Odustani</button>
            </div>
        </div>
    @endif

    {{-- STROJEVI --}}
    @if($strojevi->isEmpty())
        <div class="max-w-2xl py-4">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Mehanizacija: traktori, priključci i vozila</h3>
            <p class="text-sm text-gray-600 mb-4">Na jednom mjestu su registracije, tehnički pregledi, osiguranja, servisi i pregled prskalice. AgroLog vas na vrijeme podsjeti mailom.</p>
            <ul class="text-sm text-gray-700 space-y-2 mb-6">
                <li class="flex gap-2"><span class="text-green-600 font-bold">✓</span> <span><strong>Rokovi</strong> za svaki stroj: registracija, tehnički, osiguranje (AO i kasko), servis, pregled prskalice ili nešto svoje.</span></li>
                <li class="flex gap-2"><span class="text-green-600 font-bold">✓</span> <span><strong>Podsjetnik mailom</strong>, zadano 30 dana prije isteka, i još jedan ako rok istekne.</span></li>
                <li class="flex gap-2"><span class="text-green-600 font-bold">✓</span> <span><strong>Obnovljeno</strong> jednim klikom: AgroLog predloži sljedeći datum (registracija +1 godina, pregled prskalice +3 godine).</span></li>
                <li class="flex gap-2"><span class="text-green-600 font-bold">✓</span> <span><strong>Servisna knjiga</strong>: datum, što je rađeno, radni sati i trošak.</span></li>
            </ul>
            <p class="text-sm text-gray-500">Neobavezno je: dok ne dodate prvi stroj, ništa se ne prikazuje ni ne šalje. Počnite gumbom <strong>+ Dodaj stroj ili vozilo</strong>.</p>
        </div>
    @else
        <div class="grid gap-4 lg:grid-cols-2">
        @foreach($strojevi as $s)
            @php
                $aktivniRokovi = $s->rokovi->whereNull('obnovljen_at');
                $nedostaju = collect(\App\Support\Mehanizacija::prijedloziRokova($s->vrsta))->reject(fn ($v) => $aktivniRokovi->contains('vrsta', $v));
                $povijest = $s->rokovi->whereNotNull('obnovljen_at')->sortByDesc('datum_isteka');
            @endphp
            <div wire:key="stroj-{{ $s->id }}" class="border border-gray-200 rounded-xl overflow-hidden">
                <div class="flex items-start justify-between gap-3 bg-gray-50 px-4 py-3">
                    <div class="min-w-0">
                        <div class="font-semibold text-gray-800"><span class="mr-1">{{ $ikona[$s->vrsta] ?? '🔧' }}</span>{{ $s->naziv }}</div>
                        <div class="text-xs text-gray-500">{{ $V[$s->vrsta] ?? $s->vrsta }}@if($s->registracija) · <span class="font-medium text-gray-700">{{ $s->registracija }}</span>@endif @if($s->godina) · {{ $s->godina }}.@endif</div>
                    </div>
                    <div class="flex gap-1 shrink-0">
                        <button wire:click="urediStroj({{ $s->id }})" class="btn-xs-blue">Uredi</button>
                        <button wire:click="obrisiStroj({{ $s->id }})" wire:confirm="Obrisati {{ $s->naziv }} sa svim rokovima i servisima?" class="btn-xs-red">✕</button>
                    </div>
                </div>

                <ul class="divide-y divide-gray-100">
                    @forelse($aktivniRokovi as $r)
                        @php $boja = ['istekao' => 'text-red-700 bg-red-50', 'uskoro' => 'text-amber-800 bg-amber-50', 'ok' => 'text-green-700 bg-green-50'][$r->status]; @endphp
                        <li wire:key="rok-{{ $r->id }}" class="px-4 py-2.5 flex flex-wrap items-center gap-x-3 gap-y-1.5">
                            <div class="flex-1 min-w-[9rem]">
                                <div class="text-sm font-medium text-gray-800">{{ $r->opis }}</div>
                                <div class="text-xs text-gray-500">do {{ $r->datum_isteka->format('d.m.Y.') }}@if($r->iznos) · {{ $novac($r->iznos) }}@endif @if(! $r->podsjetnik_dana) · bez podsjetnika @endif</div>
                            </div>
                            <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $boja }}">
                                {{ $r->dana < 0 ? 'isteklo prije ' . abs($r->dana) . ' d.' : ($r->dana === 0 ? 'ističe danas' : ($r->status === 'uskoro' ? 'za ' . $r->dana . ' d.' : 'vrijedi')) }}
                            </span>
                            <span class="flex gap-1">
                                <button wire:click="obnovi({{ $r->id }})" class="btn-xs-green" title="Obavljeno: upiši sljedeći rok">Obnovljeno</button>
                                <button wire:click="urediRok({{ $r->id }})" class="btn-xs-gray">Uredi</button>
                                <button wire:click="obrisiRok({{ $r->id }})" wire:confirm="Obrisati ovaj rok?" class="btn-xs-red">✕</button>
                            </span>
                        </li>
                    @empty
                        <li class="px-4 py-3 text-sm text-gray-500">Nema upisanih rokova.</li>
                    @endforelse
                </ul>

                <div class="px-4 py-3 border-t border-gray-100 flex flex-wrap gap-2 items-center">
                    @foreach($nedostaju as $vr)
                        <button wire:click="noviRok({{ $s->id }}, '{{ $vr }}')" class="text-xs px-2.5 py-1 rounded-full border border-dashed border-gray-300 text-gray-600 hover:border-green-500 hover:text-green-700">+ {{ $R[$vr][0] }}</button>
                    @endforeach
                    <button wire:click="noviRok({{ $s->id }}, 'ostalo')" class="text-xs px-2.5 py-1 rounded-full border border-dashed border-gray-300 text-gray-600 hover:border-green-500 hover:text-green-700">+ Drugi rok</button>
                    <span class="flex-1"></span>
                    <button wire:click="noviServis({{ $s->id }})" class="btn-xs-blue">+ Servis</button>
                    <button wire:click="otvori({{ $s->id }})" class="btn-xs-gray">Servisna knjiga ({{ $s->servisi->count() }})</button>
                </div>

                @if($otvoren === $s->id)
                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 text-sm space-y-3">
                        <div>
                            <h5 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Servisi</h5>
                            @forelse($s->servisi as $sv)
                                <div wire:key="sv-{{ $sv->id }}" class="flex flex-wrap items-center gap-x-3 gap-y-1 py-1.5 border-b border-gray-200 last:border-0">
                                    <span class="text-gray-500 w-24">{{ $sv->datum->format('d.m.Y.') }}</span>
                                    <span class="text-gray-800 flex-1 min-w-[8rem]">{{ $sv->opis }}@if($sv->serviser) <span class="text-gray-400">· {{ $sv->serviser }}</span>@endif</span>
                                    @if($sv->radni_sati)<span class="text-gray-500">{{ number_format($sv->radni_sati, 0, ',', '.') }} h</span>@endif
                                    @if($sv->trosak !== null)<span class="text-gray-700">{{ $novac($sv->trosak) }}</span>@endif
                                    <span class="flex gap-1"><button wire:click="urediServis({{ $sv->id }})" class="btn-xs-gray">Uredi</button><button wire:click="obrisiServis({{ $sv->id }})" wire:confirm="Obrisati ovaj servis?" class="btn-xs-red">✕</button></span>
                                </div>
                            @empty
                                <p class="text-gray-500">Još nema upisanih servisa.</p>
                            @endforelse
                            @if($s->servisi->whereNotNull('trosak')->isNotEmpty())
                                <p class="text-xs text-gray-500 mt-1">Ukupno servisi: {{ $novac($s->servisi->sum('trosak')) }}</p>
                            @endif
                        </div>
                        @if($povijest->isNotEmpty())
                            <div>
                                <h5 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Obnovljeni rokovi</h5>
                                @foreach($povijest as $r)
                                    <div class="flex gap-3 py-1 text-gray-600"><span class="w-24 text-gray-500">{{ $r->datum_isteka->format('d.m.Y.') }}</span><span class="flex-1">{{ $r->opis }}</span>@if($r->iznos)<span>{{ $novac($r->iznos) }}</span>@endif</div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
        </div>
    @endif
</div>
