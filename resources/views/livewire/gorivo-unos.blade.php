<div>
    @php
        $eur = fn ($v) => number_format((float) $v, 2, ',', '.') . ' €';
        $ha = fn ($v) => number_format((float) $v, 2, ',', '.') . ' ha';
        $hr = fn ($d) => \Illuminate\Support\Carbon::parse($d)->format('d.m.Y.');
    @endphp

    {{-- traka: gorivo odgođeno za kraj dana --}}
    @if($traka && ! $otvoren)
        <div class="mb-4 flex flex-wrap items-center gap-x-3 gap-y-1 rounded-lg border border-orange-200 bg-orange-50 px-4 py-2.5 text-sm text-orange-900">
            <span aria-hidden="true">⛽</span>
            <span>Gorivo za <strong>{{ $hr($traka) }}</strong> još nije uneseno.</span>
            <button type="button" wire:click="otvori('{{ $traka }}')" class="font-semibold underline">Unesi</button>
            <button type="button" wire:click="sakrijTraku" class="ms-auto text-xs text-orange-800/80 underline">sakrij</button>
        </div>
    @endif

    {{-- popis na Troškovima --}}
    @if($popis)
        <div id="gorivo" class="rounded-xl border border-gray-200 bg-white overflow-hidden scroll-mt-4">
            <div class="px-4 pt-4 pb-3 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h4 class="font-semibold text-gray-800">Gorivo po danima</h4>
                    <p class="text-xs text-gray-500">Upišite koliko ste natočili za dan rada. Trošak se dijeli na prskanja, gnojidbe i radove tog dana po hektarima.</p>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <select wire:model.live="godina" aria-label="Godina" class="form-input w-28">
                        @foreach($godine as $g)
                            <option value="{{ $g }}">{{ $g }}.</option>
                        @endforeach
                    </select>
                    <button type="button" wire:click="otvori" class="btn-primary">+ Gorivo za dan</button>
                </div>
            </div>
            @if(! $prati)
                <p class="mx-4 mb-3 rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-600">Podsjetnik za gorivo nakon prskanja i gnojidbe je isključen. Uključiti ga možete u <a href="{{ route('profile.edit') }}#gorivo" class="text-green-700 underline">Profilu</a>.</p>
            @endif
            @if($dani->isEmpty())
                <p class="text-sm text-gray-500 px-4 pb-6">Za {{ $godina }}. nema upisanog goriva.</p>
            @else
                <div class="px-4 pb-3 text-sm text-gray-700">
                    Ukupno {{ $godina }}.: <strong class="text-gray-900">{{ $eur($dani->sum('iznos')) }}</strong>
                    @if($dani->sum('litara') > 0)<span class="text-gray-500"> · {{ number_format((float) $dani->sum('litara'), 2, ',', '.') }} L</span>@endif
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm mob-kartice">
                        <thead>
                            <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                                <th class="px-4 py-2 text-left">Datum</th><th class="px-4 py-2 text-right">Litara</th><th class="px-4 py-2 text-right">€/L</th>
                                <th class="px-4 py-2 text-left">Raspodijeljeno na</th><th class="px-4 py-2 text-right">Iznos</th><th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dani as $d)
                                <tr wire:key="gorivo-{{ $d->id }}" class="border-t border-gray-100 hover:bg-gray-50">
                                    <td data-label="Datum" class="px-4 py-2 whitespace-nowrap">{{ $d->datum->format('d.m.Y.') }}</td>
                                    <td data-label="Litara" class="px-4 py-2 text-right">{{ $d->litara !== null ? number_format((float) $d->litara, 2, ',', '.') : '—' }}</td>
                                    <td data-label="€/L" class="px-4 py-2 text-right">{{ $d->cijena_l !== null ? number_format((float) $d->cijena_l, 3, ',', '.') : '—' }}</td>
                                    <td data-label="Raspodijeljeno na" class="px-4 py-2 text-gray-600">
                                        @if($d->broj_poslova)
                                            {{ $d->broj_poslova }} {{ $d->broj_poslova === 1 ? 'posao' : 'poslova' }} · {{ $ha($d->ha) }}
                                        @else
                                            <span class="text-amber-700">nema poslova tog dana, ulazi samo u ukupni trošak</span>
                                        @endif
                                    </td>
                                    <td data-label="Iznos" class="px-4 py-2 text-right whitespace-nowrap font-medium">{{ $eur($d->iznos) }}</td>
                                    <td class="mob-puna mob-akcije px-4 py-2 text-right whitespace-nowrap">
                                        <button type="button" wire:click="otvori('{{ $d->datum->format('Y-m-d') }}')" class="btn-xs-blue">Uredi</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    {{-- popup --}}
    @if($otvoren)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/40 p-0 sm:p-4" wire:key="gorivo-popup"
             x-data x-on:keydown.escape.window="$wire.kasnije()" role="dialog" aria-modal="true" aria-labelledby="gorivo-naslov">
            <div class="w-full sm:max-w-lg max-h-[92vh] overflow-y-auto rounded-t-2xl sm:rounded-2xl bg-white shadow-xl">
                <div class="flex items-start justify-between gap-3 border-b border-gray-100 px-5 py-4">
                    <div>
                        <h3 id="gorivo-naslov" class="text-lg font-semibold text-gray-900">⛽ Gorivo za {{ $datum ? $hr($datum) : 'dan' }}</h3>
                        <p class="text-xs text-gray-500">Upišite ukupno za cijeli dan, ne po parceli. Raspodjela po parcelama je automatska.</p>
                    </div>
                    <button type="button" wire:click="kasnije" class="text-2xl leading-none text-gray-400 hover:text-gray-700" aria-label="Zatvori">&times;</button>
                </div>

                <div class="space-y-4 px-5 py-4">
                    @if($danId)
                        <p class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-900">Za ovaj dan je gorivo već uneseno. Možete ga ispraviti.</p>
                    @endif

                    <label class="block text-xs text-gray-600">Datum
                        <input type="date" wire:model.live="datum" max="{{ now()->toDateString() }}" class="form-input w-full mt-1 block">
                        @error('datum')<span class="text-red-500">{{ $message }}</span>@enderror
                    </label>

                    <div>
                        <p class="text-xs text-gray-600 mb-1.5">Poslovi tog dana <span class="text-gray-400">· maknite kvačicu s posla koji nije trošio ovo gorivo</span></p>
                        @if($poslovi->isEmpty())
                            <p class="rounded-lg border border-dashed border-gray-300 px-3 py-2 text-sm text-gray-500">Za ovaj dan još nema prskanja, gnojidbe ni rada. Poslovi koje upišete kasnije sami ulaze u raspodjelu.</p>
                        @else
                            <ul class="divide-y divide-gray-100 rounded-lg border border-gray-200">
                                @foreach($poslovi as $p)
                                    <li wire:key="gp-{{ $p['kljuc'] }}">
                                        <label class="flex items-start gap-3 px-3 py-2 cursor-pointer">
                                            <input type="checkbox" wire:model.live="ukljuceni" value="{{ $p['kljuc'] }}" class="mt-1 rounded border-gray-300 text-green-700">
                                            <span class="flex-1 min-w-0 text-sm">
                                                <span class="font-medium text-gray-800">{{ $p['naziv'] }}</span> · {{ $p['kultura'] }}, {{ $p['parcela'] }}
                                                @if($p['detalj'])<span class="block truncate text-xs text-gray-500">{{ $p['detalj'] }}</span>@endif
                                            </span>
                                            <span class="text-right text-sm whitespace-nowrap">
                                                <span class="text-gray-600">{{ $ha($p['ha']) }}</span>
                                                @isset($raspodjela[$p['kljuc']])<span class="block text-xs font-medium text-gray-900">{{ $eur($raspodjela[$p['kljuc']]) }}</span>@endisset
                                            </span>
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <label class="block text-xs text-gray-600">Ukupno natočeno (L)
                            <input type="number" step="0.01" inputmode="decimal" wire:model.live.debounce.400ms="litara" placeholder="npr. 95" class="form-input w-full mt-1 block">
                        </label>
                        <label class="block text-xs text-gray-600">Cijena po litri (€)
                            <input type="number" step="0.001" inputmode="decimal" wire:model.live.debounce.400ms="cijena" placeholder="npr. 1,42" class="form-input w-full mt-1 block">
                        </label>
                    </div>
                    @error('litara')<p class="text-sm text-red-500">{{ $message }}</p>@enderror
                    @error('cijena')<p class="text-sm text-red-500">{{ $message }}</p>@enderror

                    @if(is_numeric($litara) && is_numeric($cijena))
                        <p class="text-sm text-gray-700">{{ number_format((float) $litara, 2, ',', '.') }} L × {{ number_format((float) $cijena, 3, ',', '.') }} €/L = <strong class="text-gray-900">{{ $eur($this->izracun) }}</strong></p>
                    @else
                        <label class="block text-xs text-gray-600">ili samo iznos (€)
                            <input type="number" step="0.01" inputmode="decimal" wire:model.live.debounce.400ms="iznos" placeholder="npr. 135" class="form-input w-full mt-1 block">
                            @error('iznos')<span class="text-red-500">{{ $message }}</span>@enderror
                        </label>
                    @endif

                    <label class="block text-xs text-gray-600">Napomena <span class="text-gray-400">neob.</span>
                        <input type="text" wire:model="napomena" maxlength="300" placeholder="npr. traktor + prskalica" class="form-input w-full mt-1 block">
                    </label>
                </div>

                <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 px-5 py-4">
                    <button type="button" wire:click="spremi" class="btn-primary">Spremi</button>
                    @if($danId)
                        <button type="button" wire:click="obrisi" wire:confirm="Obrisati gorivo za ovaj dan?" class="btn-xs-red">Obriši</button>
                        <button type="button" wire:click="$set('otvoren', false)" class="btn-xs-gray">Zatvori</button>
                    @else
                        <button type="button" wire:click="kasnije" class="btn-xs-gray">Unijet ću na kraju dana</button>
                    @endif
                    @if($prati && ! $savjetnik)
                        <button type="button" wire:click="nePratim" wire:confirm="Više ne prikazivati unos goriva? Uključiti ga možete u Profilu." class="ms-auto text-xs text-gray-500 underline">Ne pratim gorivo – ne prikazuj više</button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- toast --}}
    @if($poruka)
        <div wire:key="gorivo-toast-{{ $porukaBroj }}" x-data="{ v: true }" x-init="setTimeout(() => v = false, 5000)" x-show="v" x-transition.opacity
             class="fixed bottom-4 left-1/2 z-50 -translate-x-1/2 rounded-lg bg-gray-900 px-4 py-2.5 text-sm text-white shadow-lg" role="status">
            ⛽ {{ $poruka }}
        </div>
    @endif
</div>
