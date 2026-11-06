<x-guest-layout>
    @if(! $poziv || $poziv->status === 'povuceno')
        <h1 class="text-lg font-semibold text-gray-900 mb-2">Poziv više ne vrijedi</h1>
        <p class="text-sm text-gray-600">Savjetnik je povukao poziv ili je poveznica neispravna.</p>
    @elseif($poziv->status === 'odbijeno')
        <h1 class="text-lg font-semibold text-gray-900 mb-2">Poziv je odbijen</h1>
        <p class="text-sm text-gray-600">Savjetnik nema pristup vašim podacima i preko AgroLoga vam više ne može poslati poziv.</p>
    @elseif($poziv->status === 'prihvaceno')
        <h1 class="text-lg font-semibold text-gray-900 mb-2">Poziv je već prihvaćen</h1>
        <p class="text-sm text-gray-600">Pristup možete promijeniti ili ukinuti u Profilu, pod Pristup savjetniku.</p>
    @else
        @php $s = $poziv->savjetnik; @endphp
        <p class="text-xs font-semibold uppercase tracking-widest text-green-700 mb-1">Poziv na suradnju</p>
        <h1 class="text-xl font-bold text-gray-900 mb-1">{{ \Illuminate\Support\Str::limit($s->name, 60) }} traži pristup vašoj evidenciji</h1>
        <p class="text-sm text-gray-500 mb-4 break-words">{{ $s->naziv_gospodarstva ? \Illuminate\Support\Str::limit($s->naziv_gospodarstva, 60) . ' · ' : '' }}{{ $s->email }}</p>

        <div class="rounded-lg bg-green-50 border border-green-200 p-3 text-sm text-gray-700 mb-5">
            Ova osoba se predstavlja kao agronom ili savjetnik. Ne vidi ništa od vaših podataka dok ne prihvatite. Prihvatite samo ako je poznajete i želite da vam pomaže.
        </div>

        @if($uKlijentu)
            <p class="text-sm text-amber-800 mb-4">Trenutno radite u gospodarstvu klijenta. Vratite se na svoje gospodarstvo pa ponovno otvorite ovu poveznicu.</p>
            <form method="post" action="{{ route('klijenti.zatvori') }}">@csrf
                <x-primary-button class="w-full justify-center py-3">Natrag na svoje</x-primary-button>
            </form>
        @elseif($ja && $ja->id === $poziv->savjetnik_id)
            <p class="text-sm text-red-700">Ovo je poziv koji ste vi poslali. Poveznicu treba otvoriti poljoprivrednik.</p>
        @elseif($ja)
            <p class="text-sm text-gray-600 mb-4">Pristup dobiva vaše gospodarstvo <strong>{{ $ja->naziv_gospodarstva ?: $ja->name }}</strong> ({{ $ja->email }}). Ako to nije pravi račun, odjavite se pa prijavite drugim.</p>
            <form method="post" action="{{ route('poziv.gospodarstvo.prihvati', $poziv->token) }}" class="space-y-3">
                @csrf
                <fieldset>
                    <legend class="text-sm font-semibold text-gray-800 mb-2">Što savjetnik smije raditi?</legend>
                    <div class="space-y-2">
                        <label class="flex gap-3 rounded-lg border border-gray-200 p-3 cursor-pointer has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                            <input type="radio" name="prava" value="pregled" class="mt-0.5 text-green-700 focus:ring-green-500" @checked(old('prava') === 'pregled')>
                            <span class="text-sm text-gray-700"><strong class="block text-gray-900">Samo pregled</strong>Vidi evidenciju i ispisuje PDF, ali ne može ništa mijenjati.</span>
                        </label>
                        <label class="flex gap-3 rounded-lg border border-gray-200 p-3 cursor-pointer has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                            <input type="radio" name="prava" value="unos" class="mt-0.5 text-green-700 focus:ring-green-500" @checked(old('prava') === 'unos')>
                            <span class="text-sm text-gray-700"><strong class="block text-gray-900">Pregled i unos</strong>Može voditi evidenciju za vas. Svaku izmjenu vidite u povijesti izmjena.</span>
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('prava')" class="mt-2" />
                </fieldset>
                <x-primary-button class="w-full justify-center py-3">Prihvati poziv</x-primary-button>
            </form>
        @else
            <p class="text-sm text-gray-600 mb-4">Za prihvaćanje se prijavite ili registrirajte. Nakon toga se vraćate ovdje i birate što savjetnik smije raditi.</p>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('login') }}" class="inline-flex justify-center items-center px-4 py-3 bg-green-900 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-800">Prijava</a>
                <a href="{{ route('register') }}" class="inline-flex justify-center items-center px-4 py-3 border border-green-900 rounded-md font-semibold text-xs text-green-900 uppercase tracking-widest hover:bg-green-50">Registracija</a>
            </div>
        @endif

        @unless($uKlijentu || ($ja && $ja->id === $poziv->savjetnik_id))
            <form method="post" action="{{ route('poziv.gospodarstvo.odbij', $poziv->token) }}" class="mt-4 text-center" onsubmit="return confirm('Odbiti poziv? Ta osoba vam preko AgroLoga više neće moći poslati poziv.')">
                @csrf
                <button class="text-sm text-gray-500 underline hover:text-gray-800">Ne želim, odbij poziv</button>
            </form>
        @endunless
    @endif
</x-guest-layout>
