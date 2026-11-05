@php
    $pristupi = \App\Models\Pristup::with('savjetnik')->where('vlasnik_id', $user->id)->where('status', '!=', 'opozvano')->latest()->get();
    $izmjene = \App\Models\PristupIzmjena::where('vlasnik_id', $user->id)->latest('created_at')->limit(30)->get();
    $poruke = [
        'savjetnik-poslano' => 'Poziv je poslan e-mailom.',
        'savjetnik-prava' => 'Prava savjetnika su spremljena.',
        'savjetnik-ukinut' => 'Pristup je ukinut.',
    ];
@endphp
<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">Pristup savjetniku</h2>
        <p class="mt-1 text-sm text-gray-600">
            Pozovite svog savjetnika, agronoma ili zadrugu da vide i vode vašu evidenciju. Savjetnik dobije mail s poveznicom;
            pristup možete promijeniti ili ukinuti u bilo kojem trenutku, a svaku njegovu izmjenu vidite u povijesti.
        </p>
    </header>

    @if(isset($poruke[session('status')]))
        <p class="mt-4 rounded-lg bg-green-50 border border-green-200 px-3 py-2 text-sm text-green-800">{{ $poruke[session('status')] }}</p>
    @endif

    {{-- poziv --}}
    <form method="post" action="{{ route('savjetnici.posalji') }}" class="mt-6 grid gap-3 sm:grid-cols-[1fr_auto_auto] sm:items-end">
        @csrf
        <div>
            <x-input-label for="savjetnik_email" value="E-mail savjetnika" />
            <x-text-input id="savjetnik_email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" placeholder="npr. savjetnik@zadruga.hr" required />
        </div>
        <div>
            <x-input-label for="savjetnik_prava" value="Prava" />
            <select id="savjetnik_prava" name="prava" class="mt-1 block w-full border-gray-300 focus:border-green-500 focus:ring-green-500 rounded-md shadow-sm">
                @foreach(\App\Models\Pristup::PRAVA as $k => $v)
                    <option value="{{ $k }}" @selected(old('prava', 'unos') === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <x-primary-button>Pošalji poziv</x-primary-button>
    </form>
    <x-input-error :messages="$errors->savjetnik->get('email')" class="mt-2" />

    {{-- popis --}}
    @if($pristupi->isNotEmpty())
        <ul class="mt-6 divide-y divide-gray-100 rounded-lg border border-gray-200">
            @foreach($pristupi as $p)
                <li class="p-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="font-medium text-gray-900 truncate">{{ $p->savjetnik?->name ?? $p->email }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $p->email }} ·
                            @if($p->status === 'prihvaceno')
                                <span class="text-green-700 font-medium">prihvaćeno {{ $p->prihvaceno_at?->format('d.m.Y.') }}</span>
                                @if($p->zadnji_rad_at) · zadnji rad {{ $p->zadnji_rad_at->format('d.m.Y. H:i') }}@endif
                            @else
                                <span class="text-amber-700 font-medium">poziv poslan, čeka prihvaćanje</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <form method="post" action="{{ route('savjetnici.prava', $p) }}">
                            @csrf @method('patch')
                            <select name="prava" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-md py-1 focus:border-green-500 focus:ring-green-500">
                                @foreach(\App\Models\Pristup::PRAVA as $k => $v)
                                    <option value="{{ $k }}" @selected($p->prava === $k)>{{ $v }}</option>
                                @endforeach
                            </select>
                        </form>
                        @if($p->status === 'poslano')
                            <form method="post" action="{{ route('savjetnici.ponovno', $p) }}">@csrf
                                <button class="text-sm text-green-700 hover:underline">Pošalji ponovno</button>
                            </form>
                        @endif
                        <form method="post" action="{{ route('savjetnici.ukini', $p) }}" onsubmit="return confirm('Ukinuti pristup savjetniku {{ addslashes($p->savjetnik?->name ?? $p->email) }}?')">
                            @csrf @method('delete')
                            <button class="text-sm text-red-600 hover:underline">{{ $p->status === 'prihvaceno' ? 'Ukini pristup' : 'Povuci poziv' }}</button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    {{-- povijest izmjena --}}
    @if($izmjene->isNotEmpty())
        <details class="mt-6" @if(session('status') === null && $izmjene->first()->created_at->gt(now()->subDays(7))) open @endif>
            <summary class="cursor-pointer text-sm font-semibold text-gray-800">Povijest izmjena savjetnika ({{ $izmjene->count() }})</summary>
            <ul class="mt-3 divide-y divide-gray-100 text-sm">
                @foreach($izmjene as $iz)
                    <li class="py-2 flex flex-wrap gap-x-3 gap-y-0.5">
                        <span class="text-gray-500 w-36 shrink-0">{{ $iz->created_at->format('d.m.Y. H:i') }}</span>
                        <span class="font-medium text-gray-800">{{ $iz->savjetnik_ime }}</span>
                        <span class="text-gray-700">{{ $iz->opis }}</span>
                    </li>
                @endforeach
            </ul>
        </details>
    @endif
</section>
