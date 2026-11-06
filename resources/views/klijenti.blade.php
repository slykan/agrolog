<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Klijenti</h2>
    </x-slot>
    <div class="py-3 sm:py-6">
        <div class="max-w-6xl mx-auto px-3 sm:px-6 lg:px-8 space-y-5">
            @if(session('status'))
                <p class="rounded-lg bg-green-50 border border-green-200 px-4 py-2 text-sm text-green-800">{{ session('status') }}</p>
            @endif

            @foreach($pozivi as $poziv)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-amber-900"><strong>{{ $poziv->vlasnik->naziv_gospodarstva ?: $poziv->vlasnik->name }}</strong> vas poziva kao savjetnika ({{ \App\Models\Pristup::PRAVA[$poziv->prava] }}).</p>
                    <form method="post" action="{{ route('poziv.prihvati', $poziv->token) }}">@csrf
                        <x-primary-button>Prihvati</x-primary-button>
                    </form>
                </div>
            @endforeach

            @if($ja->je_savjetnik)
                <div class="bg-white shadow rounded-lg border-t-4 border-green-600 p-4 sm:p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Pozovite svoje poljoprivrednike</h3>
                    @if($klijenti->isEmpty())
                        <ol class="mt-3 grid gap-3 sm:grid-cols-3 text-sm text-gray-600">
                            <li class="rounded-lg bg-green-50 p-3"><strong class="block text-gray-900">1. Upišite e-mail</strong>Poljoprivrednik dobije poziv mailom. Nije mu potreban račun unaprijed.</li>
                            <li class="rounded-lg bg-green-50 p-3"><strong class="block text-gray-900">2. On odlučuje</strong>Prihvaća poziv i bira: samo pregled ili pregled i unos.</li>
                            <li class="rounded-lg bg-green-50 p-3"><strong class="block text-gray-900">3. Radite zajedno</strong>Gospodarstvo se pojavi na vašem popisu i dobijete potvrdu mailom.</li>
                        </ol>
                    @endif
                    <form method="post" action="{{ route('klijenti.pozivi.posalji') }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                        @csrf
                        <div class="flex-1">
                            <x-input-label for="poziv_email" value="E-mail poljoprivrednika" />
                            <x-text-input id="poziv_email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" placeholder="npr. opg.horvat@gmail.com" required />
                        </div>
                        <x-primary-button class="justify-center py-3">Pošalji poziv</x-primary-button>
                    </form>
                    <x-input-error :messages="$errors->pozivGospodarstvu->get('email')" class="mt-2" />
                    <p class="mt-3 text-xs text-gray-500">Pozivajte samo poljoprivrednike s kojima surađujete. Dok ne prihvate, ne vidite ništa od njihovih podataka. <a href="{{ route('docs') }}#savjetnik-poziv" class="underline hover:text-gray-800">Kako to radi?</a></p>

                    @if($poslaniPozivi->isNotEmpty())
                        <h4 class="mt-5 text-sm font-semibold text-gray-800">Poslani pozivi</h4>
                        <ul class="mt-2 divide-y divide-gray-100 rounded-lg border border-gray-200">
                            @foreach($poslaniPozivi as $p)
                                <li class="p-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 truncate">{{ $p->email }}</p>
                                        <p class="text-xs text-gray-500">
                                            {{ $p->poslano_at?->timezone('Europe/Zagreb')->format('d.m.Y.') }} ·
                                            @if($p->status === 'odbijeno')
                                                <span class="text-red-700 font-medium">odbijeno</span>
                                            @else
                                                <span class="text-amber-700 font-medium">čeka odgovor</span>
                                            @endif
                                        </p>
                                    </div>
                                    @if($p->status === 'poslano')
                                        <div class="flex flex-wrap items-center gap-3">
                                            @if($p->smijePonovno())
                                                <form method="post" action="{{ route('klijenti.pozivi.ponovno', $p) }}">@csrf
                                                    <button class="text-sm text-green-700 hover:underline">Pošalji ponovno</button>
                                                </form>
                                            @endif
                                            <form method="post" action="{{ route('klijenti.pozivi.povuci', $p) }}" onsubmit="return confirm('Povući poziv?')">
                                                @csrf @method('delete')
                                                <button class="text-sm text-red-600 hover:underline">Povuci poziv</button>
                                            </form>
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Gospodarstva kojima ste savjetnik</h3>
                    <p class="text-xs text-gray-500">Poredano po tome tko traži najviše pažnje: istekli rokovi, skladište u minusu, zapisi za dopuniti (evidencija 2027.).</p>
                </div>
                @if($klijenti->isEmpty())
                    <div class="px-6 py-10 text-center text-gray-500">
                        <p class="font-medium text-gray-700 mb-1">Još nemate klijenata.</p>
                        @if($ja->je_savjetnik)
                            <p class="text-sm">Gospodarstva se ovdje pojavljuju kad prihvate vaš poziv. I gospodarstvo može pozvati vas, iz svog profila.</p>
                        @else
                            <p class="text-sm">Gospodarstvo vas poziva iz svog profila (Profil → Pristup savjetniku), a vi prihvaćate poveznicom iz maila.</p>
                            <p class="text-sm mt-2">Agronom ste ili savjetnik? Označite to u <a href="{{ route('profile.edit') }}" class="text-green-700 underline hover:text-green-800">Profilu</a> pa možete sami pozivati poljoprivrednike.</p>
                        @endif
                    </div>
                @else
                    <div class="overflow-x-auto">
                    <table class="w-full text-sm mob-kartice">
                        <thead>
                            <tr class="text-xs text-gray-500 uppercase bg-gray-50 text-left">
                                <th class="px-4 py-2">Gospodarstvo</th>
                                <th class="px-4 py-2 text-right">Površina</th>
                                <th class="px-4 py-2">Zadnji unos</th>
                                <th class="px-4 py-2">Traži pažnju</th>
                                <th class="px-4 py-2">Prava</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($klijenti as $k)
                                <tr>
                                    <td class="mob-puna px-4 py-3">
                                        <p class="font-semibold text-gray-900">{{ $k['gospodarstvo'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $k['vlasnik']->name }} · {{ $k['vlasnik']->email }}</p>
                                    </td>
                                    <td data-label="Površina" class="px-4 py-3 text-right whitespace-nowrap">{{ number_format($k['ha'], 2, ',', '.') }} ha <span class="text-xs text-gray-400">· {{ $k['parcela'] }} parc.</span></td>
                                    <td data-label="Zadnji unos" class="px-4 py-3 whitespace-nowrap {{ $k['zadnji_unos'] && $k['zadnji_unos']->lt(now()->subDays(30)) ? 'text-amber-700' : 'text-gray-700' }}">
                                        {{ $k['zadnji_unos'] ? $k['zadnji_unos']->format('d.m.Y.') : '—' }}
                                    </td>
                                    <td data-label="Traži pažnju" class="mob-puna px-4 py-3">
                                        <div class="flex flex-wrap gap-1.5 text-xs">
                                            @if($k['dopuniti'])<span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">{{ $k['dopuniti'] }} za dopuniti (2027.)</span>@endif
                                            @foreach($k['rokovi'] as $r)<span class="px-2 py-0.5 rounded-full {{ $r['razina'] === 'crveno' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800' }}" title="{{ $r['tekst'] }}">{{ $r['naslov'] }}</span>@endforeach
                                            @foreach($k['zaliha'] as $r)<span class="px-2 py-0.5 rounded-full {{ $r['razina'] === 'crveno' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800' }}" title="{{ $r['tekst'] }}">{{ $r['naslov'] }}</span>@endforeach
                                            @foreach($k['karence'] as $r)<span class="px-2 py-0.5 rounded-full bg-sky-100 text-sky-800" title="{{ $r['tekst'] }}">{{ $r['naslov'] }}</span>@endforeach
                                            @if(! $k['ukupno'])<span class="text-green-700">✓ sve u redu</span>@endif
                                        </div>
                                    </td>
                                    <td data-label="Prava" class="px-4 py-3 whitespace-nowrap text-gray-600">{{ \App\Models\Pristup::PRAVA[$k['pristup']->prava] }}</td>
                                    <td class="mob-puna mob-akcije px-4 py-3 text-right">
                                        <form method="post" action="{{ route('klijenti.otvori', $k['pristup']) }}">@csrf
                                            <button class="btn-primary whitespace-nowrap">Otvori →</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                @endif
            </div>
            <p class="text-xs text-gray-500">"Otvori" prebaci cijelu aplikaciju na gospodarstvo klijenta (parcele, prskanje, gnojidba, skladište, troškovi, izvještaji). Gore je traka s gumbom za povratak na svoje gospodarstvo.</p>
        </div>
    </div>
</x-app-layout>
