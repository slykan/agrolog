<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tržnica · moji proizvodi</h2>
            <a href="{{ $p && $p->jeJavna() ? route('trznica', ['prodavac' => $p->slug]) : route('trznica') }}" target="_blank" class="text-sm text-green-700 hover:underline">Otvori Tržnicu &rarr;</a>
        </div>
    </x-slot>

    @php
        $f = $uredi;
        $iz = fn ($polje, $zadano = '') => old($polje, $f?->{$polje} ?? $zadano);
    @endphp

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">
            @if(! $p || ! $p->vidljivo)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
                    <p class="font-semibold">Za Tržnicu prvo predstavite gospodarstvo.</p>
                    <p class="mt-1">Oglas uvijek pokazuje tko prodaje i kontakt, pa proizvode mogu dodati gospodarstva s uključenom prezentacijom.</p>
                    <a href="{{ route('profile.edit') }}#prezentacija" class="inline-block mt-3 btn-primary">Predstavi gospodarstvo</a>
                </div>
            @else
                @if(! $p->jeJavna())
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        Prezentacija još čeka odobrenje. Proizvode možete dodati već sada, a na Tržnici će se pojaviti kad je odobrimo.
                    </div>
                @endif

                {{-- unos / izmjena --}}
                <form method="post" enctype="multipart/form-data" action="{{ $f ? route('trznica.moji.azuriraj', $f) : route('trznica.moji.spremi') }}"
                      class="bg-white shadow-sm rounded-xl p-5 space-y-4" id="obrazac">
                    @csrf
                    <h3 class="font-semibold text-gray-900">{{ $f ? 'Uredi proizvod' : 'Novi proizvod' }}</h3>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="sm:col-span-2">
                            <x-input-label for="naziv" value="Naziv proizvoda *" />
                            <x-text-input id="naziv" name="naziv" class="mt-1 block w-full" maxlength="100" required :value="$iz('naziv')" placeholder="npr. Bagremov med" />
                            <x-input-error class="mt-2" :messages="$errors->get('naziv')" />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="vrsta" value="Vrsta *" />
                            <select id="vrsta" name="vrsta" required class="form-input mt-1 block w-full">
                                <option value="">Odaberite…</option>
                                @foreach(\App\Models\Prezentacija::OZNAKE as $o)
                                    <option value="{{ $o }}" @selected($iz('vrsta') === $o)>{{ $o }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('vrsta')" />
                        </div>
                        <div>
                            <x-input-label for="cijena" value="Cijena (€) *" />
                            <x-text-input id="cijena" name="cijena" inputmode="decimal" class="mt-1 block w-full" required :value="$f ? number_format((float) $f->cijena, 2, ',', '') : old('cijena')" placeholder="npr. 9,50" />
                            <x-input-error class="mt-2" :messages="$errors->get('cijena')" />
                        </div>
                        <div>
                            <x-input-label for="jedinica" value="Po *" />
                            <select id="jedinica" name="jedinica" required class="form-input mt-1 block w-full">
                                @foreach(\App\Models\Proizvod::JEDINICE as $k => $l)
                                    <option value="{{ $k }}" @selected($iz('jedinica', 'kg') === $k)>{{ $l }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('jedinica')" />
                        </div>
                        <div class="sm:col-span-2 flex items-end pb-1">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-800"><input type="hidden" name="dostupno" value="0"><input type="checkbox" name="dostupno" value="1" class="rounded border-gray-300 text-green-700" @checked((bool) old('dostupno', $f?->dostupno ?? true))> Trenutno dostupno (vidljivo na Tržnici)</label>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-4">
                            <x-input-label for="opis" value="Opis (neobavezno)" />
                            <textarea id="opis" name="opis" rows="3" maxlength="1500" class="form-input mt-1 block w-full" placeholder="npr. Med iz vlastitih košnica, staklenka 900 g. Može i dostava u Osijeku.">{{ $iz('opis') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('opis')" />
                        </div>
                        <div class="sm:col-span-2 lg:col-span-4">
                            <x-input-label for="slika" value="Slika (neobavezno)" />
                            @if($f?->slika)
                                <div class="mt-2 flex items-center gap-3">
                                    <img src="{{ $f->slikaUrl() }}" alt="" class="h-16 w-24 rounded-lg object-cover border border-gray-200">
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="ukloni_sliku" value="1" class="rounded border-gray-300 text-red-600"> Ukloni sliku</label>
                                </div>
                            @endif
                            <input id="slika" name="slika" type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-green-50 file:px-3 file:py-2 file:text-green-800">
                            <x-input-error class="mt-2" :messages="$errors->get('slika')" />
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-primary-button>{{ $f ? 'Spremi izmjene' : 'Dodaj proizvod' }}</x-primary-button>
                        @if($f)<a href="{{ route('trznica.moji') }}" class="text-sm text-gray-600 underline">Odustani</a>@endif
                    </div>
                </form>

                {{-- popis --}}
                @if($proizvodi->isEmpty())
                    <p class="text-center text-gray-500 py-6">Još nema proizvoda. Dodajte prvi, npr. med, jabuke ili sir.</p>
                @else
                    <div class="bg-white shadow-sm rounded-xl divide-y divide-gray-100">
                        @foreach($proizvodi as $pr)
                            <div class="p-4 flex items-center gap-4 {{ $pr->dostupno ? '' : 'opacity-60' }}">
                                @if($pr->slika)
                                    <img src="{{ $pr->slikaUrl() }}" alt="" class="h-14 w-20 rounded-lg object-cover border border-gray-200 shrink-0">
                                @else
                                    <div class="h-14 w-20 rounded-lg bg-green-50 shrink-0"></div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 truncate">{{ $pr->naziv }}</p>
                                    <p class="text-sm text-gray-600">{{ $pr->cijenaTekst() }} · {{ $pr->vrsta }} · {{ $pr->dostupno ? 'dostupno' : 'nije dostupno' }}</p>
                                </div>
                                <div class="flex flex-wrap items-center justify-end gap-2 shrink-0">
                                    <form method="post" action="{{ route('trznica.moji.dostupnost', $pr) }}">@csrf<button class="btn-xs-gray">{{ $pr->dostupno ? 'Sakrij' : 'Prikaži' }}</button></form>
                                    <a href="{{ route('trznica.moji', ['uredi' => $pr->id]) }}#obrazac" class="btn-xs-blue">Uredi</a>
                                    <form method="post" action="{{ route('trznica.moji.obrisi', $pr) }}" onsubmit="return confirm('Obrisati {{ addslashes($pr->naziv) }}?')">@csrf @method('delete')<button class="btn-xs-red" aria-label="Obriši">&#10005;</button></form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
