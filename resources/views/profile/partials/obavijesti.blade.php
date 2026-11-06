<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Obavijesti</h2>
        <p class="mt-1 text-sm text-gray-600">Rokovi mehanizacije, karence, skladište pri kraju i novosti u AgroLogu.</p>
    </header>

    <form method="post" action="{{ route('profile.obavijesti') }}" class="mt-4 space-y-3">
        @csrf
        @method('patch')
        <label class="flex items-start gap-3">
            <input type="checkbox" name="push" value="1" @checked($user->push_obavijesti) onchange="this.form.submit()" class="mt-1 rounded text-green-600">
            <span><span class="font-medium text-gray-800">Push obavijesti</span>
                <span class="block text-sm text-gray-600">Na mobitelu ili računalu, kao iz aplikacije.</span></span>
        </label>
        <label class="flex items-start gap-3">
            <input type="checkbox" name="mail" value="1" @checked(! $user->obavijesti_odjava_at) onchange="this.form.submit()" class="mt-1 rounded text-green-600">
            <span><span class="font-medium text-gray-800">Mail obavijesti</span>
                <span class="block text-sm text-gray-600">Podsjetnici na rokove i novosti. Poruke o računu, npr. promjena lozinke ili poziv savjetnika, stižu uvijek.</span></span>
        </label>
        <noscript><button class="btn-xs-green">Spremi</button></noscript>
        @if(session('status') === 'obavijesti-spremljene')
            <p class="text-sm text-green-700">Spremljeno.</p>
        @endif
    </form>

    @if($user->push_obavijesti)
        {{-- push traži i dopuštenje preglednika, posebno za svaki uređaj --}}
        <div class="mt-4 rounded-lg bg-gray-50 p-3 text-sm" x-data="{
                stanje: '', poruka: '',
                init() { this.stanje = window.agroPush?.stanje() ?? 'nepodrzano' },
                async ukljuci() {
                    const ok = await window.agroPush.ukljuci().catch(() => false);
                    this.stanje = window.agroPush.stanje();
                    this.poruka = ok ? 'Uključeno na ovom uređaju.' : (this.stanje === 'blokirano' ? '' : 'Uključivanje nije uspjelo. Pokušajte ponovno.');
                },
                async proba() {
                    this.poruka = '';
                    const n = await window.agroPush.proba().catch(() => 0);
                    this.poruka = n > 0 ? 'Probna obavijest je poslana.' : 'Slanje nije uspjelo. Pokušajte ponovno uključiti obavijesti.';
                },
             }">
            <p class="font-medium text-gray-800">Na ovom uređaju</p>
            <p x-show="stanje === 'ukljuceno'" x-cloak class="text-green-800">Uključeno.</p>
            <p x-show="stanje === 'nije-ukljuceno'" x-cloak class="text-gray-600">Još nije uključeno. Preglednik će tražiti vaše dopuštenje.</p>
            <p x-show="stanje === 'blokirano'" x-cloak class="text-amber-800">Blokirano u postavkama preglednika. Dopustite obavijesti za agro-log.app u postavkama stranice.</p>
            <p x-show="stanje === 'ios-instaliraj'" x-cloak class="text-gray-600">Na iPhoneu najprije dodajte AgroLog na početni zaslon (Dijeli → Dodaj na početni zaslon) i otvorite ga odande. Potreban je iOS 16.4 ili noviji.</p>
            <p x-show="stanje === 'nepodrzano'" x-cloak class="text-gray-600">Ovaj preglednik ne podržava push obavijesti.</p>
            <div class="mt-2 flex flex-wrap gap-2">
                <button type="button" x-show="stanje === 'nije-ukljuceno'" x-cloak @click="ukljuci()" class="btn-xs-green">Uključi na ovom uređaju</button>
                <button type="button" x-show="stanje === 'ukljuceno'" x-cloak @click="proba()" class="btn-xs-gray">Pošalji probnu obavijest</button>
            </div>
            <p x-show="poruka" x-cloak x-text="poruka" class="mt-2 text-gray-700"></p>
        </div>
    @endif
</section>
