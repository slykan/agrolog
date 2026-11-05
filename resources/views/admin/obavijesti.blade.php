<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin &mdash; obavijesti korisnicima</h2>
            <a href="{{ route('admin.korisnici') }}" class="text-sm text-green-700 hover:underline">Korisnici &rarr;</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('poruka'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('poruka') }}</div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white shadow-sm rounded-xl p-4"><div class="text-xs text-gray-500">Primatelja</div><div class="text-2xl font-bold">{{ $primatelja }}</div><div class="text-xs text-gray-400">svi korisnici osim odjavljenih</div></div>
                <div class="bg-white shadow-sm rounded-xl p-4"><div class="text-xs text-gray-500">Odjavljenih</div><div class="text-2xl font-bold">{{ $odjavljenih }}</div><div class="text-xs text-gray-400">ne primaju obavijesti</div></div>
                <div class="bg-white shadow-sm rounded-xl p-4"><div class="text-xs text-gray-500">U redu za slanje</div><div class="text-2xl font-bold">{{ $naCekanju }}</div><div class="text-xs text-gray-400">šalje se 8 mailova u minuti</div></div>
            </div>

            {{-- Nova obavijest: lijevo forma, desno pregled kako će mail izgledati --}}
            <form method="POST" action="{{ route('admin.obavijesti.posalji') }}"
                  x-data="{ predmet: @js(old('predmet', '')), naslov: @js(old('naslov', '')), tekst: @js(old('tekst', '')), gumbTekst: @js(old('gumb_tekst', '')), gumbUrl: @js(old('gumb_url', '')) }"
                  class="grid gap-6 lg:grid-cols-2">
                @csrf
                <div class="bg-white shadow rounded-lg p-6 space-y-4">
                    <h3 class="font-semibold text-gray-800">Nova obavijest</h3>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Predmet maila</label>
                        <input name="predmet" x-model="predmet" required maxlength="150" class="form-input w-full" placeholder="npr. Novo u AgroLogu: parcele na karti">
                        @error('predmet')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Naslov u mailu</label>
                        <input name="naslov" x-model="naslov" required maxlength="150" class="form-input w-full" placeholder="npr. Vaše parcele sada su na karti">
                        @error('naslov')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Tekst <span class="text-gray-400">(prazan red = novi odlomak)</span></label>
                        <textarea name="tekst" x-model="tekst" required rows="10" class="form-input w-full" placeholder="Poštovani,&#10;&#10;..."></textarea>
                        @error('tekst')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Gumb — tekst <span class="text-gray-400">(neobavezno)</span></label>
                            <input name="gumb_tekst" x-model="gumbTekst" maxlength="60" class="form-input w-full" placeholder="npr. Otvori AgroLog">
                            @error('gumb_tekst')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Gumb — poveznica</label>
                            <input name="gumb_url" x-model="gumbUrl" type="url" class="form-input w-full" placeholder="{{ url('/novosti') }}">
                            @error('gumb_url')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 pt-2 border-t">
                        <button name="akcija" value="proba" class="btn-secondary">Pošalji probu meni ({{ auth()->user()->email }})</button>
                        <button name="akcija" value="svima" class="btn-primary"
                                onclick="return confirm('Poslati ovu obavijest svim korisnicima ({{ $primatelja }})? To se ne može poništiti.')">
                            Pošalji svima ({{ $primatelja }})
                        </button>
                    </div>
                    <p class="text-xs text-gray-400">Savjet: prvo pošaljite probu i pogledajte mail u svom sandučiću. Svaki mail ima link za odjavu.</p>
                </div>

                {{-- Pregled --}}
                <div class="rounded-lg bg-[#f3f7f1] p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Pregled maila</p>
                    <div class="rounded-xl bg-white overflow-hidden shadow-sm">
                        <div class="bg-gradient-to-br from-[#06120a] via-[#0f2a17] to-[#14532d] px-6 py-5">
                            <div class="flex items-center justify-between"><x-application-logo bijeli class="h-7" /><span class="text-[10px] font-bold uppercase tracking-widest bg-[#3ee36f] text-[#06120a] rounded-full px-2 py-0.5">Obavijest</span></div>
                            <h4 class="mt-4 text-xl font-extrabold text-white" x-text="naslov || 'Naslov obavijesti'"></h4>
                        </div>
                        <div class="h-1 bg-[#3ee36f]"></div>
                        <div class="px-6 py-5 text-[15px] leading-relaxed text-gray-800">
                            <p class="text-xs text-gray-400 mb-3">Predmet: <span x-text="predmet || '—'"></span></p>
                            <p class="mb-3">Pozdrav, <b>{{ auth()->user()->naziv_gospodarstva ?: auth()->user()->name }}</b>!</p>
                            <template x-for="(o, i) in (tekst || 'Tekst obavijesti…').split(/\n\s*\n/).filter(x => x.trim())" :key="i">
                                <p class="mb-3 whitespace-pre-line" x-text="o.trim()"></p>
                            </template>
                            <template x-if="gumbTekst && gumbUrl">
                                <span class="inline-block mt-2 rounded-xl bg-green-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-green-600/30" x-text="gumbTekst + ' →'"></span>
                            </template>
                        </div>
                        <div class="px-6 py-3 bg-gray-50 text-xs text-gray-400">… Ne želite primati obavijesti? Odjavite se jednim klikom.</div>
                    </div>
                </div>
            </form>

            {{-- Povijest --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-3">Poslane obavijesti</h3>
                @forelse($obavijesti as $o)
                    <div class="py-2 border-b border-gray-100 last:border-0 text-sm flex flex-wrap justify-between gap-2">
                        <span><b>{{ $o->naslov }}</b> <span class="text-gray-400">· {{ $o->predmet }}</span></span>
                        <span class="text-gray-500 whitespace-nowrap">{{ $o->poslano_at?->timezone('Europe/Zagreb')->format('d.m.Y. H:i') }} · {{ $o->primatelja }} primatelja</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Još nije poslana nijedna obavijest.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
