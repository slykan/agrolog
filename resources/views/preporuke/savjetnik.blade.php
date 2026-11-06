<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Preporuke klijentima</h2>
    </x-slot>
    @php
        $staro = old('kultura_ids', []);
        $boja = ['poslano' => 'bg-amber-100 text-amber-800', 'prihvaceno' => 'bg-sky-100 text-sky-800', 'odbijeno' => 'bg-red-100 text-red-700', 'pitanje' => 'bg-violet-100 text-violet-800',
            'napravljeno' => 'bg-green-100 text-green-800', 'nije_napravljeno' => 'bg-gray-200 text-gray-700', 'povuceno' => 'bg-gray-100 text-gray-500'];
    @endphp
    <div class="py-3 sm:py-6">
        <div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8 space-y-5">
            @if(session('status'))
                <p class="rounded-lg bg-green-50 border border-green-200 px-4 py-2 text-sm text-green-800">{{ session('status') }}</p>
            @endif
            <a href="{{ route('klijenti') }}" class="inline-block text-sm text-green-700 hover:underline">← Natrag na klijente</a>

            @if($klijenti->isEmpty())
                <div class="bg-white shadow rounded-lg p-6 text-sm text-gray-600">Još nemate klijenata. Preporuke možete slati gospodarstvima koja su vam dala pristup.</div>
            @else
                <form method="post" action="{{ route('klijenti.preporuke.posalji') }}" class="bg-white shadow rounded-lg border-t-4 border-green-600 p-4 sm:p-6 space-y-4"
                      x-data="{
                        vrsta: @js(old('vrsta', 'prskanje')),
                        kultura: '',
                        ciljevi: @js($ciljevi),
                        odabrane: @js(array_map('intval', $staro)),
                        get kulture() { return [...new Set(this.ciljevi.map(c => c.kultura))].sort((a, b) => a.localeCompare(b, 'hr')) },
                        get popis() { return this.ciljevi.filter(c => c.kultura === this.kultura) },
                        odaberiKulturu() { this.odabrane = this.popis.map(c => c.id) },
                        get brojGospodarstava() { return new Set(this.ciljevi.filter(c => this.odabrane.includes(c.id)).map(c => c.vlasnik_id)).size },
                      }">
                    @csrf
                    <h3 class="text-lg font-semibold text-gray-900">Nova preporuka</h3>
                    @if($errors->any())
                        <ul class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-sm text-red-700 list-disc pl-6">
                            @foreach($errors->all() as $greska)<li>{{ $greska }}</li>@endforeach
                        </ul>
                    @endif

                    <fieldset>
                        <legend class="text-sm font-medium text-gray-700 mb-1">Što šaljete?</legend>
                        <div class="flex flex-wrap gap-2">
                            @foreach(\App\Models\Preporuka::VRSTE as $k => $v)
                                <label class="flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm cursor-pointer has-[:checked]:border-green-600 has-[:checked]:bg-green-50">
                                    <input type="radio" name="vrsta" value="{{ $k }}" x-model="vrsta" class="text-green-700 focus:ring-green-500"> {{ $v }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    {{-- prskanje i gnojidba: samo parcele s odabranom kulturom --}}
                    <div x-show="vrsta !== 'poruka'" class="space-y-3">
                        <div>
                            <label for="p_kultura" class="block text-sm font-medium text-gray-700 mb-1">Kultura</label>
                            <select id="p_kultura" x-model="kultura" @change="odaberiKulturu()" class="form-input w-full sm:w-72">
                                <option value="">Odaberite kulturu</option>
                                <template x-for="k in kulture" :key="k"><option :value="k" x-text="k"></option></template>
                            </select>
                            <p x-show="! ciljevi.length" class="mt-1 text-xs text-amber-700">Vaši klijenti još nemaju aktivnih kultura za ovu godinu.</p>
                        </div>
                        <div x-show="kultura" x-cloak>
                            <p class="text-sm font-medium text-gray-700 mb-1">Parcele s tom kulturom <span class="font-normal text-gray-500" x-text="'(odabrano ' + odabrane.length + ', gospodarstava ' + brojGospodarstava + ')'"></span></p>
                            <p class="mb-2 text-xs text-gray-500">Gospodarstva bez ove kulture nisu na popisu i neće dobiti preporuku.</p>
                            <div class="max-h-64 overflow-y-auto rounded-lg border border-gray-200 divide-y divide-gray-100">
                                <template x-for="c in popis" :key="c.id">
                                    <label class="flex items-start gap-2 px-3 py-2 text-sm cursor-pointer hover:bg-gray-50">
                                        <input type="checkbox" name="kultura_ids[]" :value="c.id" x-model.number="odabrane" class="mt-0.5 rounded text-green-700 focus:ring-green-500">
                                        <span><b x-text="c.gospodarstvo"></b> <span class="text-gray-500" x-text="c.parcela + ' · ' + c.ha.toFixed(2).replace('.', ',') + ' ha'"></span></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-3 items-start">
                            <div class="w-full sm:w-72">
                                <label for="p_sredstvo" class="block text-sm font-medium text-gray-700 mb-1" x-text="vrsta === 'gnojidba' ? 'Gnojivo' : 'Sredstvo'"></label>
                                <input id="p_sredstvo" type="text" name="sredstvo" value="{{ old('sredstvo') }}" :list="vrsta === 'prskanje' ? 'p-sredstva' : null" autocomplete="off" :placeholder="vrsta === 'gnojidba' ? 'npr. KAN' : 'npr. Roundup'" class="form-input w-full">
                            </div>
                            <div>
                                <label for="p_doza" class="block text-sm font-medium text-gray-700 mb-1">Doza <span class="font-normal text-gray-400">neob.</span></label>
                                <div class="flex gap-2">
                                    <input id="p_doza" type="number" name="doza" value="{{ old('doza') }}" step="0.001" min="0" class="form-input w-24">
                                    <select name="jedinica" aria-label="Jedinica" class="form-input w-24">
                                        @foreach(\App\Models\Preporuka::JEDINICE as $j)<option value="{{ $j }}" @selected(old('jedinica', 'l/ha') === $j)>{{ $j }}</option>@endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- opća poruka: gospodarstva bez provjere kultura --}}
                    <div x-show="vrsta === 'poruka'" x-cloak x-data="{ svi: true }">
                        <p class="text-sm font-medium text-gray-700 mb-1">Kome šaljete?</p>
                        <div class="max-h-64 overflow-y-auto rounded-lg border border-gray-200 divide-y divide-gray-100">
                            @foreach($klijenti as $id => $naziv)
                                <label class="flex items-center gap-2 px-3 py-2 text-sm cursor-pointer hover:bg-gray-50">
                                    <input type="checkbox" name="vlasnik_ids[]" value="{{ $id }}" @checked(in_array($id, old('vlasnik_ids', array_keys($klijenti->all())))) :disabled="vrsta !== 'poruka'" class="rounded text-green-700 focus:ring-green-500"> {{ $naziv }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3 items-start">
                        <div>
                            <label for="p_rok" class="block text-sm font-medium text-gray-700 mb-1">Rok <span class="font-normal text-gray-400">neob.</span></label>
                            <input id="p_rok" type="date" name="rok" value="{{ old('rok') }}" min="{{ date('Y-m-d') }}" class="form-input w-44">
                        </div>
                    </div>
                    <div>
                        <label for="p_napomena" class="block text-sm font-medium text-gray-700 mb-1"><span x-text="vrsta === 'poruka' ? 'Poruka' : 'Napomena'"></span></label>
                        <textarea id="p_napomena" name="napomena" rows="3" maxlength="1000" class="form-input w-full">{{ old('napomena') }}</textarea>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <x-primary-button>Pregledaj prije slanja</x-primary-button>
                        <span class="text-xs text-gray-500">Prije slanja vidite kome ide i što kaže registar FIS-a.</span>
                    </div>
                    <datalist id="p-sredstva">@foreach($sredstva as $s)<option value="{{ $s }}">@endforeach</datalist>
                </form>
            @endif

            {{-- poslano --}}
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-100"><h3 class="font-semibold text-gray-800">Poslane preporuke</h3></div>
                @forelse($paketi as $redovi)
                    @php $p0 = $redovi->first(); @endphp
                    <details class="border-b border-gray-100 last:border-0" @if($loop->index < 3 || $redovi->contains('status', 'pitanje')) open @endif>
                        <summary class="cursor-pointer px-4 sm:px-6 py-3 text-sm">
                            <b class="text-gray-900">{{ \App\Models\Preporuka::VRSTE[$p0->vrsta] }}</b>
                            <span class="text-gray-700">{{ $p0->sredstvo ?: \Illuminate\Support\Str::limit((string) $p0->napomena, 60) }}</span>
                            <span class="text-gray-500">· {{ $p0->created_at->timezone('Europe/Zagreb')->format('d.m.Y.') }} · {{ $redovi->pluck('vlasnik_id')->unique()->count() }} gosp.</span>
                        </summary>
                        <ul class="divide-y divide-gray-100 bg-gray-50/60">
                            @foreach($redovi as $r)
                                <li class="px-4 sm:px-6 py-3 text-sm">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="min-w-0"><b>{{ $r->vlasnik?->naziv_gospodarstva ?: $r->vlasnik?->name }}</b> <span class="text-gray-500">{{ collect([$r->kultura_naziv, $r->parcela_oznaka])->filter()->implode(' · ') }}</span></span>
                                        <span class="flex items-center gap-2">
                                            <span class="rounded-full px-2 py-0.5 text-xs {{ $boja[$r->status] ?? '' }}">{{ \App\Models\Preporuka::STATUSI[$r->status] ?? $r->status }}</span>
                                            @if(in_array($r->status, \App\Models\Preporuka::OTVORENO, true))
                                                <form method="post" action="{{ route('klijenti.preporuke.povuci', $r) }}" onsubmit="return confirm('Povući preporuku?')">@csrf
                                                    <button class="text-xs text-red-600 hover:underline">Povuci</button>
                                                </form>
                                            @endif
                                        </span>
                                    </div>
                                    @if($r->pitanje)
                                        <p class="mt-1 text-gray-700">Pitanje: „{{ $r->pitanje }}”</p>
                                    @endif
                                    @if($r->status === 'pitanje')
                                        <form method="post" action="{{ route('klijenti.preporuke.odgovori', $r) }}" class="mt-2 flex flex-col gap-2 sm:flex-row">
                                            @csrf
                                            <input type="text" name="odgovor" required maxlength="1000" aria-label="Vaš odgovor" placeholder="Vaš odgovor" class="form-input flex-1">
                                            <button class="btn-xs-green">Odgovori</button>
                                        </form>
                                    @elseif($r->odgovor_savjetnika)
                                        <p class="mt-1 text-gray-500">Vaš odgovor: „{{ $r->odgovor_savjetnika }}”</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </details>
                @empty
                    <p class="px-6 py-8 text-center text-sm text-gray-500">Još niste poslali preporuku.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
