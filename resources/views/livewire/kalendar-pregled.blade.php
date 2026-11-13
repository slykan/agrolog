<div class="space-y-4">
    @php
        $mjeseci = ['siječanj', 'veljača', 'ožujak', 'travanj', 'svibanj', 'lipanj', 'srpanj', 'kolovoz', 'rujan', 'listopad', 'studeni', 'prosinac'];
        $daniKratko = ['pon', 'uto', 'sri', 'čet', 'pet', 'sub', 'ned'];
        $daniDugo = ['ponedjeljak', 'utorak', 'srijeda', 'četvrtak', 'petak', 'subota', 'nedjelja'];
        $grupeSve = \App\Support\Kalendar::GRUPE;
        $hrDatum = fn ($d) => \Illuminate\Support\Carbon::parse($d)->format('d.m.Y.');
        $danUTjednu = fn ($d) => $daniDugo[\Illuminate\Support\Carbon::parse($d)->dayOfWeekIso - 1];
        $danasMj = substr($danas, 0, 7);
    @endphp

    {{-- Navigacija mjeseca, parcela, dodavanje --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
            <button type="button" wire:click="pomakni(-1)" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-700 hover:bg-gray-50" aria-label="Prethodni mjesec">‹</button>
            <h3 class="min-w-[10rem] text-center text-lg font-semibold text-gray-800 capitalize">{{ $mjeseci[$prvi->month - 1] }} {{ $prvi->year }}.</h3>
            <button type="button" wire:click="pomakni(1)" class="rounded-md border border-gray-300 bg-white px-3 py-2 text-gray-700 hover:bg-gray-50" aria-label="Sljedeći mjesec">›</button>
            @if($mjesec !== $danasMj || $dan !== $danas)
                <button type="button" wire:click="danas" class="ms-1 text-sm text-green-700 underline">danas</button>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <select wire:model.live="parcela" aria-label="Parcela" class="form-input w-56 max-w-full">
                <option value="">Sve parcele</option>
                @foreach($parcele as $p)
                    <option value="{{ $p->id }}">{{ \App\Models\Parcela::oznaka($p->arkod_broj, $p->rucno_ucrtana) }}</option>
                @endforeach
            </select>
            <button type="button" wire:click="noviPlan" class="btn-primary">+ Planiraj posao</button>
        </div>
    </div>

    {{-- Filter po vrsti --}}
    <div class="flex flex-wrap items-center gap-1.5">
        @foreach($grupeSve as $kljuc => [$naziv, $boja])
            @php $ukljuceno = in_array($kljuc, $grupe, true); @endphp
            <button type="button" wire:click="prebaciGrupu('{{ $kljuc }}')" wire:key="g-{{ $kljuc }}-{{ $ukljuceno ? 1 : 0 }}"
                    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium transition {{ $ukljuceno ? 'border-gray-300 bg-white text-gray-800' : 'border-gray-200 bg-gray-50 text-gray-400 line-through' }}"
                    aria-pressed="{{ $ukljuceno ? 'true' : 'false' }}">
                <span class="h-2.5 w-2.5 rounded-full {{ $kljuc === 'plan' ? 'border-2 border-dashed bg-white' : '' }}" style="{{ $kljuc === 'plan' ? 'border-color:' . $boja : 'background:' . $boja }}"></span>
                {{ $naziv }}
            </button>
        @endforeach
        @if(count($grupe) < count($grupeSve))
            <button type="button" wire:click="sveGrupe" class="ms-1 text-xs text-green-700 underline">prikaži sve</button>
        @endif
    </div>

    {{-- Planiranje --}}
    @if($showPlan)
        <div class="rounded-lg border border-green-200 bg-green-50 p-4">
            <p class="mb-3 text-sm font-semibold text-gray-800">{{ $planEditId ? 'Uredi planirani posao' : 'Planiraj posao' }}</p>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <label class="block text-xs text-gray-600">Datum
                    <input type="date" wire:model.live="plan.datum" class="form-input mt-1 block w-full">
                    @error('plan.datum')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>
                <label class="block text-xs text-gray-600">Vrsta posla
                    <select wire:model.live="plan.vrsta" class="form-input mt-1 block w-full">
                        @foreach(\App\Models\PlaniraniRad::VRSTE as $k => $v)
                            <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                    @error('plan.vrsta')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>
                <label class="block text-xs text-gray-600 sm:col-span-2">Kultura{{ in_array($plan['vrsta'], ['rad', 'ostalo'], true) ? ' (nije obavezno)' : '' }}
                    <select wire:model.live="plan.kultura_id" class="form-input mt-1 block w-full">
                        <option value="">Odaberite…</option>
                        @foreach($kulture as $k)
                            <option value="{{ $k->id }}">{{ $k->parcela ? \App\Models\Parcela::oznaka($k->parcela->arkod_broj, $k->parcela->rucno_ucrtana) . ' · ' : '' }}{{ $k->naziv }}{{ $k->zavrsena_at ? ' · završena' : '' }}</option>
                        @endforeach
                    </select>
                    @error('plan.kultura_id')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>
                <label class="block text-xs text-gray-600 sm:col-span-2">
                    {{ match ($plan['vrsta']) { 'prskanje' => 'Sredstvo', 'gnojidba' => 'Gnojivo', 'rad' => 'Vrsta rada (npr. rezidba, košnja)', 'berba' => 'Opis (nije obavezno)', default => 'Što treba napraviti' } }}
                    <input type="text" wire:model="plan.opis" maxlength="200" class="form-input mt-1 block w-full">
                    @error('plan.opis')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>
                <label class="block text-xs text-gray-600 sm:col-span-2">Napomena
                    <input type="text" wire:model="plan.napomena" maxlength="500" class="form-input mt-1 block w-full">
                    @error('plan.napomena')<span class="text-red-500">{{ $message }}</span>@enderror
                </label>
            </div>
            <p class="mt-3 text-xs text-gray-500">Plan nije zapis u evidenciji. Kad posao obavite, kliknite „Odrađeno” i upišite ga, a plan se zatvara sam.</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <button type="button" wire:click="spremiPlan" class="btn-primary">Spremi</button>
                <button type="button" wire:click="odustani" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Odustani</button>
                @if($planEditId)
                    <button type="button" wire:click="obrisiPlan({{ $planEditId }})" wire:confirm="Obrisati planirani posao?" class="ms-auto text-sm text-red-600 underline">Obriši plan</button>
                @endif
            </div>
        </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_20rem]">
        {{-- Mjesečna mreža (tablet i računalo) --}}
        <div class="hidden sm:block">
            <div class="grid grid-cols-7 border-b border-gray-200 pb-1 text-center text-xs font-semibold uppercase tracking-wide text-gray-500">
                @foreach($daniKratko as $d)<div>{{ $d }}</div>@endforeach
            </div>
            <div class="mt-1 grid grid-cols-7 gap-1">
                @foreach($tjedni as $tjedan)
                    @foreach($tjedan as $d)
                        @php
                            $lista = $dogadaji[$d] ?? collect();
                            $uMjesecu = str_starts_with($d, $mjesec);
                            $vrijeme = $prognoza[$d] ?? null;
                            $karenca = $podKarencom[$d] ?? [];
                        @endphp
                        <button type="button" wire:click="odaberi('{{ $d }}')" wire:key="c-{{ $d }}"
                                class="relative flex min-h-[6.5rem] flex-col rounded-md border p-1.5 text-left transition hover:border-green-400
                                       {{ $d === $dan ? 'border-green-600 ring-1 ring-green-600' : 'border-gray-200' }}
                                       {{ $uMjesecu ? 'bg-white' : 'bg-gray-50 text-gray-400' }}">
                            <span class="flex items-center justify-between gap-1">
                                <span class="text-sm font-semibold {{ $d === $danas ? 'flex h-6 w-6 items-center justify-center rounded-full bg-green-700 text-white' : '' }}">{{ (int) substr($d, 8, 2) }}</span>
                                @if($vrijeme)
                                    <span class="text-xs" title="{{ $vrijeme['opis'] }}, {{ $vrijeme['min'] }}–{{ $vrijeme['max'] }} °C, vjetar {{ $vrijeme['vjetar'] }} km/h, kiša {{ $vrijeme['vjerojatnost'] }} %{{ $vrijeme['prskanje'] ? ' · povoljno za prskanje' : ' · nije za prskanje: ' . $vrijeme['razlog'] }}">
                                        {{ $vrijeme['ikona'] }}<span class="ms-0.5 inline-block h-1.5 w-1.5 rounded-full align-middle {{ $vrijeme['prskanje'] ? 'bg-green-600' : 'bg-red-400' }}"></span>
                                    </span>
                                @endif
                            </span>
                            <span class="mt-1 flex flex-col gap-0.5">
                                @foreach($lista->take(3) as $e)
                                    <span class="truncate rounded px-1 py-0.5 text-[11px] leading-tight {{ $e['stanje'] === 'kasni' ? 'ring-1 ring-red-500' : '' }}"
                                          style="{{ $e['plan_id'] ? 'border:1px dashed ' . $e['boja'] . ';color:' . $e['boja'] . ';background:#fff' : 'background:' . $e['boja'] . '1f;color:' . $e['boja'] }}">
                                        {{ $e['naslov'] }}{{ $e['kultura'] ? ' · ' . $e['kultura'] : '' }}
                                    </span>
                                @endforeach
                                @if($lista->count() > 3)
                                    <span class="text-[11px] text-gray-500">+ još {{ $lista->count() - 3 }}</span>
                                @endif
                            </span>
                            @if($karenca)
                                <span class="absolute inset-x-1 bottom-1 h-1 rounded-full" style="background: {{ $grupeSve['karenca'][1] }}" title="Karenca: {{ implode('; ', $karenca) }}"></span>
                            @endif
                        </button>
                    @endforeach
                @endforeach
            </div>
            <p class="mt-2 text-xs text-gray-500">
                Narančasta crta na dnu dana = karenca, berba još nije dozvoljena. Isprekidani okvir = planirano, crveni rub = kasni.
                @if($prognoza)
                    Točkica uz vrijeme: <span class="text-green-700">zelena</span> = povoljno za prskanje (vjetar &lt; 15 km/h, kiša &lt; 40 %, 5–30 °C, bez mraza). Prognoza: <a href="https://open-meteo.com/" target="_blank" rel="noopener" class="underline">Open-Meteo</a>.
                @elseif(! $imaKoordinate)
                    Prognoza se prikazuje kad je barem jedna parcela na karti.
                @endif
            </p>
        </div>

        {{-- Agenda (mobitel): dani s događajima --}}
        <div class="space-y-2 sm:hidden">
            @php $imaDana = false; @endphp
            @foreach($tjedni as $tjedan)
                @foreach($tjedan as $d)
                    @continue(! str_starts_with($d, $mjesec))
                    @php $lista = $dogadaji[$d] ?? collect(); $vrijeme = $prognoza[$d] ?? null; $karenca = $podKarencom[$d] ?? []; @endphp
                    @continue($lista->isEmpty() && ! $vrijeme && $d !== $danas)
                    @php $imaDana = true; @endphp
                    <button type="button" wire:click="odaberi('{{ $d }}')" wire:key="m-{{ $d }}"
                            class="block w-full rounded-lg border p-3 text-left {{ $d === $dan ? 'border-green-600 bg-green-50' : 'border-gray-200 bg-white' }}">
                        <span class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-800">{{ $d === $danas ? 'Danas · ' : '' }}{{ $danUTjednu($d) }} {{ $hrDatum($d) }}</span>
                            @if($vrijeme)
                                <span class="text-xs text-gray-600">{{ $vrijeme['ikona'] }} {{ $vrijeme['min'] }}–{{ $vrijeme['max'] }} °C
                                    <span class="ms-1 rounded px-1 {{ $vrijeme['prskanje'] ? 'bg-green-100 text-green-800' : 'bg-red-50 text-red-700' }}">{{ $vrijeme['prskanje'] ? 'za prskanje' : $vrijeme['razlog'] }}</span>
                                </span>
                            @endif
                        </span>
                        @foreach($lista as $e)
                            <span class="mt-1.5 flex items-start gap-2 text-sm">
                                <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $e['plan_id'] ? 'border-2 border-dashed bg-white' : '' }}" style="{{ $e['plan_id'] ? 'border-color:' . $e['boja'] : 'background:' . $e['boja'] }}"></span>
                                <span class="min-w-0">
                                    <span class="text-gray-800">{{ $e['naslov'] }}</span>
                                    @if($e['stanje'] === 'kasni')<span class="ms-1 text-xs font-medium text-red-600">kasni</span>@endif
                                    @if($e['kultura'] || $e['parcela'])<span class="block text-xs text-gray-500">{{ collect([$e['kultura'], $e['parcela']])->filter()->implode(' · ') }}</span>@endif
                                </span>
                            </span>
                        @endforeach
                        @if($karenca)
                            <span class="mt-1.5 block text-xs text-amber-700">Karenca: {{ implode('; ', $karenca) }}</span>
                        @endif
                    </button>
                @endforeach
            @endforeach
            @unless($imaDana)
                <p class="rounded-lg border border-dashed border-gray-300 p-4 text-center text-sm text-gray-500">U ovom mjesecu nema upisanih ni planiranih poslova.</p>
            @endunless
        </div>

        {{-- Odabrani dan --}}
        <div class="lg:sticky lg:top-4 lg:self-start">
            @if($dan !== '')
                @php $vrijeme = $prognoza[$dan] ?? null; $karenca = $podKarencom[$dan] ?? []; @endphp
                <div class="rounded-lg border border-gray-200 bg-white p-4" wire:key="dan-{{ $dan }}">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-gray-500">{{ $dan === $danas ? 'danas, ' : '' }}{{ $danUTjednu($dan) }}</p>
                            <p class="text-lg font-semibold text-gray-800">{{ $hrDatum($dan) }}</p>
                        </div>
                        @if($vrijeme)
                            <div class="text-right text-xs text-gray-600">
                                <div class="text-2xl leading-none">{{ $vrijeme['ikona'] }}</div>
                                {{ $vrijeme['min'] }}–{{ $vrijeme['max'] }} °C · 💨 {{ $vrijeme['vjetar'] }} km/h · 🌧 {{ $vrijeme['vjerojatnost'] }} %
                            </div>
                        @endif
                    </div>
                    @if($vrijeme)
                        <p class="mt-2 rounded-md px-2 py-1 text-xs {{ $vrijeme['prskanje'] ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-700' }}">
                            {{ $vrijeme['prskanje'] ? 'Povoljno za prskanje.' : 'Nije povoljno za prskanje: ' . $vrijeme['razlog'] . '.' }}
                        </p>
                    @endif
                    @if($karenca)
                        <div class="mt-2 rounded-md bg-amber-50 px-2 py-1 text-xs text-amber-800">
                            <p class="font-medium">Karenca, berba još nije dozvoljena:</p>
                            @foreach($karenca as $k)<p>{{ $k }}</p>@endforeach
                        </div>
                    @endif

                    <ul class="mt-3 space-y-2">
                        @forelse($odabrani as $i => $e)
                            <li class="rounded-md border border-gray-100 p-2" wire:key="e-{{ $dan }}-{{ $i }}-{{ $e['plan_id'] }}" style="border-left: 4px {{ $e['plan_id'] ? 'dashed' : 'solid' }} {{ $e['boja'] }}">
                                <p class="text-sm font-medium text-gray-800">
                                    {{ $e['naslov'] }}
                                    @if($e['stanje'] === 'kasni')<span class="ms-1 rounded bg-red-50 px-1 text-xs font-medium text-red-600">kasni</span>@endif
                                    @if($e['stanje'] === 'plan')<span class="ms-1 rounded bg-gray-100 px-1 text-xs text-gray-600">planirano</span>@endif
                                </p>
                                @if($e['kultura'] || $e['parcela'])
                                    <p class="text-xs text-gray-600">{{ collect([$e['kultura'], $e['parcela']])->filter()->implode(' · ') }}</p>
                                @endif
                                @if($e['detalj'])<p class="text-xs text-gray-500">{{ $e['detalj'] }}</p>@endif
                                <div class="mt-1 flex flex-wrap gap-3 text-xs">
                                    @if($e['plan_id'])
                                        <button type="button" wire:click="obavljeno({{ $e['plan_id'] }})" class="font-medium text-green-700 underline">{{ $e['link'] ? 'Odrađeno, upiši' : 'Odrađeno' }}</button>
                                        <button type="button" wire:click="urediPlan({{ $e['plan_id'] }})" class="text-gray-600 underline">Uredi</button>
                                    @elseif($e['link'])
                                        <a href="{{ $e['link'] }}" class="text-green-700 underline">{{ $e['grupa'] === 'preporuka' ? 'Otvori preporuku' : 'Otvori' }}</a>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="text-sm text-gray-500">Za ovaj dan nema ništa upisano{{ count($grupe) < count($grupeSve) ? ' (uz odabrani filter)' : '' }}.</li>
                        @endforelse
                    </ul>

                    <div class="mt-4 border-t border-gray-100 pt-3">
                        @if($dan <= $danas)
                            <p class="mb-1.5 text-xs font-semibold uppercase tracking-wide text-gray-500">Upiši obavljeno</p>
                            <div class="flex flex-wrap gap-1.5 text-sm">
                                <a href="{{ route('prskanje', ['datum' => $dan]) }}" class="rounded-md border border-gray-300 px-2 py-1 text-gray-700 hover:bg-gray-50">Prskanje</a>
                                <a href="{{ route('gnojidba', ['datum' => $dan]) }}" class="rounded-md border border-gray-300 px-2 py-1 text-gray-700 hover:bg-gray-50">Gnojidba</a>
                                <a href="{{ route('berba', ['datum' => $dan]) }}" class="rounded-md border border-gray-300 px-2 py-1 text-gray-700 hover:bg-gray-50">Berba</a>
                                <a href="{{ route('radovi', ['datum' => $dan]) }}" class="rounded-md border border-gray-300 px-2 py-1 text-gray-700 hover:bg-gray-50">Rad</a>
                                @php $gorivoDan = \App\Models\GorivoDan::where('user_id', auth()->id())->whereDate('datum', $dan)->first(); @endphp
                                @if($gorivoDan || auth()->user()->prati_gorivo)
                                    <button type="button" x-on:click="Livewire.dispatch('gorivo-otvori', { datum: '{{ $dan }}' })" class="rounded-md border px-2 py-1 {{ $gorivoDan ? 'border-orange-300 bg-orange-50 text-orange-900' : 'border-gray-300 text-gray-700 hover:bg-gray-50' }}">⛽ Gorivo{{ $gorivoDan ? ': ' . number_format((float) $gorivoDan->iznos, 2, ',', '.') . ' €' : '' }}</button>
                                @endif
                            </div>
                        @endif
                        <button type="button" wire:click="noviPlan('{{ $dan }}')" class="mt-2 text-sm font-medium text-green-700 underline">+ Planiraj za {{ $hrDatum($dan) }}</button>
                    </div>
                </div>
            @else
                <p class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500">Kliknite na dan za popis poslova, upis obavljenog ili planiranje.</p>
            @endif
        </div>
    </div>
</div>
