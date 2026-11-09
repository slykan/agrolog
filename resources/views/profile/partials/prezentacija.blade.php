@php
    $p = \App\Models\Prezentacija::where('user_id', $user->id)->first();
    $greske = $errors->getBag('prezentacija');
    $iz = fn ($polje, $zadano = null) => old($polje, $p?->{$polje} ?? $zadano);
    $odabrane = old('oznake', $p?->oznake ?? []);
    $uklj = (bool) old('vidljivo', $p?->vidljivo ?? false);
    $status = $p?->status();
    $mjesta = \App\Models\Prezentacija::priblizno($user->id);
@endphp
<section x-data="{ uklj: @js($uklj), prag: @js((bool) $iz('kucni_prag', false)) }">
    <header>
        <h2 class="text-lg font-medium text-gray-900">Prezentacija na AgroLogu</h2>
        <p class="mt-1 text-sm text-gray-600">
            Predstavite gospodarstvo na javnom popisu <a href="{{ route('gospodarstva') }}" class="text-green-700 underline" target="_blank">Gospodarstva</a>.
            Kupci vas mogu pronaći po mjestu i onome što nudite.
        </p>
    </header>

    @if($p && $p->vidljivo)
        @php
            $oznakaStatusa = [
                'javno' => ['bg-green-50 border-green-200 text-green-900', '✓ Objavljeno', 'Vaše gospodarstvo je na javnom popisu.'],
                'na_pregledu' => ['bg-amber-50 border-amber-200 text-amber-900', '⏳ Na pregledu', 'Pregledat ćemo prezentaciju i javiti vam mailom kad bude objavljena.'],
                'odbijeno' => ['bg-red-50 border-red-200 text-red-900', 'Nije objavljeno', 'Razlog: ' . $p->odbijeno_razlog . ' Doradite i spremite ponovno.'],
            ][$status] ?? null;
        @endphp
        @if($oznakaStatusa)
            <div class="mt-4 rounded-lg border px-3 py-2 text-sm {{ $oznakaStatusa[0] }}">
                <strong>{{ $oznakaStatusa[1] }}</strong> · {{ $oznakaStatusa[2] }}
                @if($status === 'javno')<a href="{{ route('gospodarstvo', $p->slug) }}" target="_blank" class="ml-1 font-medium underline">Pogledaj stranicu →</a>@endif
            </div>
        @endif
    @endif

    <form method="post" action="{{ route('profile.prezentacija') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
        @csrf
        <input type="hidden" name="vidljivo" value="0">
        <label for="vidljivo" class="flex items-start gap-3 rounded-lg border border-gray-200 p-3 cursor-pointer has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
            <input id="vidljivo" type="checkbox" name="vidljivo" value="1" x-model="uklj" class="mt-0.5 rounded border-gray-300 text-green-700 focus:ring-green-500">
            <span class="text-sm text-gray-700">
                <strong class="block text-gray-900">Želim da moje gospodarstvo bude na javnom popisu</strong>
                Podaci ispod bit će <strong>javno vidljivi svima</strong>. Kad maknete kvačicu i spremite, gospodarstvo odmah nestaje s popisa.
            </span>
        </label>

        <div x-show="uklj" x-cloak class="space-y-5">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-input-label for="pz_naziv" value="Naziv koji se prikazuje *" />
                    <x-text-input id="pz_naziv" name="naziv" type="text" class="mt-1 block w-full" maxlength="120" :value="$iz('naziv', $user->naziv_gospodarstva)" x-bind:required="uklj" placeholder="npr. OPG Horvat" />
                    <x-input-error class="mt-2" :messages="$greske->get('naziv')" />
                </div>
                <div>
                    <x-input-label for="pz_mjesto" value="Mjesto *" />
                    <x-text-input id="pz_mjesto" name="mjesto" type="text" class="mt-1 block w-full" maxlength="80" :value="$iz('mjesto')" x-bind:required="uklj" placeholder="npr. Hrastin" />
                    <x-input-error class="mt-2" :messages="$greske->get('mjesto')" />
                </div>
                <div>
                    <x-input-label for="pz_zupanija" value="Županija *" />
                    <select id="pz_zupanija" name="zupanija" x-bind:required="uklj" class="form-input mt-1 block w-full">
                        <option value="">Odaberite…</option>
                        @foreach(\App\Models\Prezentacija::ZUPANIJE as $z)
                            <option value="{{ $z }}" @selected($iz('zupanija') === $z)>{{ $z }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$greske->get('zupanija')" />
                </div>
            </div>

            <div>
                <x-input-label for="pz_opis" value="Čime se bavimo *" />
                <textarea id="pz_opis" name="opis" rows="5" maxlength="3000" x-bind:required="uklj" class="form-input mt-1 block w-full"
                          placeholder="npr. Obiteljsko gospodarstvo s 5 ha voćnjaka. Uzgajamo jabuke i kruške, radimo sok i ocat. Prodajemo na kućnom pragu i na tržnici u Osijeku subotom.">{{ $iz('opis') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Barem 50 znakova: što proizvodite, gdje i kako se može kupiti.</p>
                <x-input-error class="mt-2" :messages="$greske->get('opis')" />
            </div>

            <div>
                <x-input-label value="Što nudimo * (do 8)" />
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach(\App\Models\Prezentacija::OZNAKE as $o)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="oznake[]" value="{{ $o }}" class="peer sr-only" @checked(in_array($o, $odabrane, true))>
                            <span class="inline-block rounded-full border border-gray-300 bg-white px-3 py-1 text-sm text-gray-700 peer-checked:border-green-700 peer-checked:bg-green-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-green-500">{{ $o }}</span>
                        </label>
                    @endforeach
                </div>
                <x-input-error class="mt-2" :messages="$greske->get('oznake')" />
            </div>

            <div>
                <x-input-label for="pz_slika" value="Slika (neobavezno)" />
                @if($p?->slika)
                    <div class="mt-2 flex items-center gap-3">
                        <img src="{{ $p->slikaUrl() }}" alt="" class="h-20 w-32 rounded-lg object-cover border border-gray-200">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700"><input type="checkbox" name="ukloni_sliku" value="1" class="rounded border-gray-300 text-red-600"> Ukloni sliku</label>
                    </div>
                @endif
                <input id="pz_slika" name="slika" type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-green-50 file:px-3 file:py-2 file:text-green-800">
                <p class="mt-1 text-xs text-gray-500">JPG, PNG ili WebP do 8 MB. Smanjujemo je na 1600 px. Objavite samo svoju fotografiju.</p>
                <x-input-error class="mt-2" :messages="$greske->get('slika')" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="pz_telefon" value="Kontakt telefon *" />
                    <x-text-input id="pz_telefon" name="telefon" type="tel" class="mt-1 block w-full" maxlength="30" :value="$iz('telefon')" x-bind:required="uklj" placeholder="091 234 5678" />
                    <div class="mt-2 flex gap-4 text-sm text-gray-700">
                        <label class="inline-flex items-center gap-2"><input type="hidden" name="whatsapp" value="0"><input type="checkbox" name="whatsapp" value="1" class="rounded border-gray-300 text-green-700" @checked($iz('whatsapp', false))> WhatsApp</label>
                        <label class="inline-flex items-center gap-2"><input type="hidden" name="viber" value="0"><input type="checkbox" name="viber" value="1" class="rounded border-gray-300 text-green-700" @checked($iz('viber', false))> Viber</label>
                    </div>
                    <x-input-error class="mt-2" :messages="$greske->get('telefon')" />
                </div>
                <div>
                    <x-input-label for="pz_email" value="E-mail za kupce (neobavezno)" />
                    <x-text-input id="pz_email" name="email" type="email" class="mt-1 block w-full" maxlength="150" :value="$iz('email')" />
                    <x-input-error class="mt-2" :messages="$greske->get('email')" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="pz_adresa" value="Adresa (neobavezno)" />
                    <x-text-input id="pz_adresa" name="adresa" type="text" class="mt-1 block w-full" maxlength="200" :value="$iz('adresa')" placeholder="npr. Kiš Ferenca 15, Hrastin" />
                    <p class="mt-1 text-xs text-gray-500">Upišite je ako kupci dolaze k vama. Na karti se gospodarstvo ionako prikazuje samo približno.</p>
                    <x-input-error class="mt-2" :messages="$greske->get('adresa')" />
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 p-3 space-y-3">
                <label class="flex items-center gap-2 text-sm text-gray-800"><input type="hidden" name="kucni_prag" value="0"><input type="checkbox" name="kucni_prag" value="1" x-model="prag" class="rounded border-gray-300 text-green-700"> <strong>Prodajemo na kućnom pragu</strong></label>
                <div x-show="prag" x-cloak>
                    <x-input-label for="pz_rv" value="Kada se može doći (neobavezno)" />
                    <x-text-input id="pz_rv" name="radno_vrijeme" type="text" class="mt-1 block w-full" maxlength="150" :value="$iz('radno_vrijeme')" placeholder="npr. pon–sub 8–18 h, uz najavu" />
                    <x-input-error class="mt-2" :messages="$greske->get('radno_vrijeme')" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                @foreach(['web' => 'Web stranica', 'facebook' => 'Facebook', 'instagram' => 'Instagram'] as $k => $l)
                    <div>
                        <x-input-label :for="'pz_' . $k" :value="$l . ' (neob.)'" />
                        <x-text-input :id="'pz_' . $k" :name="$k" type="text" class="mt-1 block w-full" maxlength="200" :value="$iz($k)" placeholder="poveznica" />
                        <x-input-error class="mt-2" :messages="$greske->get($k)" />
                    </div>
                @endforeach
            </div>

            <p class="text-xs text-gray-500">
                @if($mjesta[0] !== null)
                    Na karti popisa gospodarstvo se prikazuje približno, oko sredine vaših parcela (na oko 1 km), nikad na točnoj adresi.
                @else
                    Kad dodate parcele, gospodarstvo će se prikazati i na karti, približno (na oko 1 km).
                @endif
                Prvu objavu pregledavamo prije nego što postane javna.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <x-primary-button>Spremi prezentaciju</x-primary-button>
            @if(session('status') === 'prezentacija-na-pregledu')
                <p class="text-sm text-amber-800">Spremljeno. Prezentacija je poslana na pregled.</p>
            @elseif(session('status') === 'prezentacija-spremljena')
                <p class="text-sm text-gray-600">Spremljeno.</p>
            @elseif(session('status') === 'prezentacija-obrisana')
                <p class="text-sm text-gray-600">Prezentacija je obrisana.</p>
            @endif
        </div>
    </form>

    @if($p)
        <form method="post" action="{{ route('profile.prezentacija.obrisi') }}" class="mt-4" onsubmit="return confirm('Obrisati prezentaciju i sliku? Ovo se ne može vratiti.')">
            @csrf
            @method('delete')
            <button class="text-sm text-red-700 underline">Obriši prezentaciju i sve njezine podatke</button>
        </form>
    @endif
</section>
