{{-- Kontakt obrazac (KontaktController) --}}
@php
    $polje = 'mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-green-600 focus:ring-green-600 text-base';
    $oznaka = 'block text-sm font-medium text-gray-800';
    $neob = '<span class="font-normal text-gray-500">neobavezno</span>';
@endphp
<section id="poruka" class="scroll-mt-24 bg-white border border-gray-100 shadow-sm rounded-2xl p-6 sm:p-8">
    <h2 class="text-2xl font-bold text-gray-900">{{ $naslovObrasca ?? 'Pošaljite nam poruku' }}</h2>

    @if(session('kontakt_poslano'))
        <div role="status" class="mt-5 rounded-xl border border-green-200 bg-green-50 p-5 text-green-900">
            <p class="font-semibold">Hvala, poruka je poslana.</p>
            <p class="mt-1 text-sm">Javit ćemo vam se čim prije na upisanu e-mail adresu.</p>
        </div>
    @else
        @if(session('kontakt_greska'))
            <div role="alert" class="mt-5 rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">{{ session('kontakt_greska') }}</div>
        @endif

        <form method="post" action="{{ route('contact.posalji') }}" class="mt-5 space-y-5" novalidate>
            @csrf
            <input type="hidden" name="obrazac_od" value="{{ $obrazacOd }}">
            @if(! empty($izvor))<input type="hidden" name="izvor" value="{{ $izvor }}">@endif
            {{-- robotima: ljudi ovo polje ne vide --}}
            <div aria-hidden="true" style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden">
                <label>Web stranica <input type="text" name="web_stranica" tabindex="-1" autocomplete="off"></label>
            </div>

            <div x-data="{ tema: @js($odabranaTema) }">
                <label for="k-tema" class="{{ $oznaka }}">Tema</label>
                <select id="k-tema" name="tema" required x-model="tema" class="{{ $polje }}">
                    <option value="">Odaberite temu…</option>
                    @foreach($teme as $k => $naziv)
                        <option value="{{ $k }}" @selected($odabranaTema === $k)>{{ $naziv }}</option>
                    @endforeach
                </select>
                @error('tema')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                @if(empty($izvor))
                    <p x-show="tema === 'oglasavanje'" x-cloak class="mt-2 text-sm text-gray-700">Mjesta za oglas i tehnički uvjeti su na stranici <a href="{{ route('oglasavanje') }}" class="font-medium text-green-700 underline">Oglašavanje</a>.</p>
                @endif
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="k-ime" class="{{ $oznaka }}">Ime i prezime</label>
                    <input id="k-ime" type="text" name="ime" value="{{ old('ime', $korisnik?->name) }}" required maxlength="120" autocomplete="name" class="{{ $polje }}">
                    @error('ime')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="k-email" class="{{ $oznaka }}">E-mail adresa</label>
                    <input id="k-email" type="email" name="email" value="{{ old('email', $korisnik?->email) }}" required maxlength="190" autocomplete="email" class="{{ $polje }}">
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="k-tvrtka" class="{{ $oznaka }}">Naziv tvrtke {!! $neob !!}</label>
                    <input id="k-tvrtka" type="text" name="tvrtka" value="{{ old('tvrtka', $korisnik?->naziv_gospodarstva) }}" maxlength="150" autocomplete="organization" class="{{ $polje }}">
                    @error('tvrtka')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="k-oib" class="{{ $oznaka }}">OIB {!! $neob !!}</label>
                    <input id="k-oib" type="text" name="oib" value="{{ old('oib') }}" inputmode="numeric" maxlength="11" autocomplete="off" class="{{ $polje }}">
                    @error('oib')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="k-poruka" class="{{ $oznaka }}">Poruka</label>
                <textarea id="k-poruka" name="poruka" rows="6" required maxlength="5000" class="{{ $polje }}">{{ old('poruka') }}</textarea>
                @error('poruka')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="kopija" value="1" @checked(old('kopija')) class="rounded text-green-600 focus:ring-green-600">
                Pošalji kopiju poruke i meni
            </label>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <button class="btn-primary justify-center">Pošalji poruku</button>
                <p class="text-xs text-gray-600 sm:max-w-sm sm:text-right">Podatke koristimo samo za odgovor na vašu poruku. Više u <a href="{{ route('privacy') }}" class="underline hover:text-gray-900">Pravilima privatnosti</a>.</p>
            </div>
        </form>
    @endif
</section>
