<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Preporuke savjetnika</h2>
    </x-slot>
    @php
        $boja = ['poslano' => 'bg-amber-100 text-amber-800', 'prihvaceno' => 'bg-sky-100 text-sky-800', 'odbijeno' => 'bg-red-100 text-red-700', 'pitanje' => 'bg-violet-100 text-violet-800',
            'napravljeno' => 'bg-green-100 text-green-800', 'nije_napravljeno' => 'bg-gray-200 text-gray-700', 'povuceno' => 'bg-gray-100 text-gray-500'];
    @endphp
    <div class="py-3 sm:py-6">
        <div class="max-w-3xl mx-auto px-3 sm:px-6 lg:px-8 space-y-4">
            @if(session('status'))
                <p class="rounded-lg bg-green-50 border border-green-200 px-4 py-2 text-sm text-green-800">{{ session('status') }}</p>
            @endif
            @if($errors->any())
                <p class="rounded-lg bg-red-50 border border-red-200 px-4 py-2 text-sm text-red-700">{{ $errors->first() }}</p>
            @endif

            @forelse($preporuke as $p)
                <article class="bg-white shadow rounded-lg p-4 sm:p-5" x-data="{ pitam: false }">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500">{{ $p->savjetnik_ime }} · {{ $p->created_at->timezone('Europe/Zagreb')->format('d.m.Y.') }}</p>
                            <h3 class="font-semibold text-gray-900">{{ \App\Models\Preporuka::VRSTE[$p->vrsta] }}@if($p->sredstvo): {{ $p->sredstvo }}@endif</h3>
                        </div>
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $boja[$p->status] ?? '' }}">{{ \App\Models\Preporuka::STATUSI[$p->status] ?? $p->status }}</span>
                    </div>
                    <dl class="mt-2 grid gap-x-6 gap-y-1 text-sm sm:grid-cols-2">
                        @if($p->kultura_naziv)<div><dt class="inline text-gray-500">Kultura:</dt> <dd class="inline font-medium">{{ $p->kultura_naziv }}</dd></div>@endif
                        @if($p->parcela_oznaka)<div><dt class="inline text-gray-500">Parcela:</dt> <dd class="inline font-medium">{{ $p->parcela_oznaka }}</dd></div>@endif
                        @if($p->dozaTekst())<div><dt class="inline text-gray-500">Doza:</dt> <dd class="inline font-medium">{{ $p->dozaTekst() }}</dd></div>@endif
                        @if($p->rok)<div><dt class="inline text-gray-500">Rok:</dt> <dd class="inline font-medium">{{ $p->rok->format('d.m.Y.') }}</dd></div>@endif
                    </dl>
                    @if($p->napomena)<p class="mt-2 text-sm text-gray-800 whitespace-pre-line">{{ $p->napomena }}</p>@endif

                    @foreach($p->upozorenja() as $u)
                        <p class="mt-2 rounded border border-amber-300 bg-amber-50 px-2 py-1 text-xs text-amber-900">{{ $u }}</p>
                    @endforeach
                    @if($p->pitanje)<p class="mt-2 text-sm text-gray-600">Vaše pitanje: „{{ $p->pitanje }}”</p>@endif
                    @if($p->odgovor_savjetnika)<p class="mt-1 text-sm text-gray-800">Odgovor savjetnika: „{{ $p->odgovor_savjetnika }}”</p>@endif

                    @if(in_array($p->status, \App\Models\Preporuka::OTVORENO, true))
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            @if($p->status !== 'prihvaceno')
                                <form method="post" action="{{ route('preporuke.odgovor', $p) }}">@csrf
                                    <input type="hidden" name="radnja" value="prihvati">
                                    <button class="btn-xs-green">{{ $p->vrsta === 'poruka' ? 'Primljeno' : 'Prihvaćam' }}</button>
                                </form>
                            @endif
                            @if($p->status === 'prihvaceno' && $p->imaUnos())
                                <a href="{{ route($p->vrsta, ['preporuka' => $p->id]) }}" class="btn-xs-green">Napravljeno, unesi u evidenciju</a>
                                <form method="post" action="{{ route('preporuke.odgovor', $p) }}">@csrf
                                    <input type="hidden" name="radnja" value="napravljeno">
                                    <button class="btn-xs-gray" title="Zapis je već u evidenciji">Već je upisano</button>
                                </form>
                                <form method="post" action="{{ route('preporuke.odgovor', $p) }}">@csrf
                                    <input type="hidden" name="radnja" value="nije">
                                    <button class="btn-xs-gray">Nije napravljeno</button>
                                </form>
                            @endif
                            @if($p->vrsta !== 'poruka' || $p->status !== 'prihvaceno')
                                @if($p->status !== 'pitanje')
                                    <button type="button" @click="pitam = ! pitam" class="btn-xs-blue">Imam pitanje</button>
                                @endif
                            @endif
                            @if($p->vrsta !== 'poruka')
                                <form method="post" action="{{ route('preporuke.odgovor', $p) }}" onsubmit="return confirm('Ne prihvaćate ovu preporuku?')">@csrf
                                    <input type="hidden" name="radnja" value="odbij">
                                    <button class="btn-xs-red">Ne prihvaćam</button>
                                </form>
                            @endif
                        </div>
                        <form x-show="pitam" x-cloak method="post" action="{{ route('preporuke.odgovor', $p) }}" class="mt-2 flex flex-col gap-2 sm:flex-row">
                            @csrf
                            <input type="hidden" name="radnja" value="pitanje">
                            <input type="text" name="tekst" maxlength="1000" aria-label="Vaše pitanje" placeholder="Vaše pitanje savjetniku" class="form-input flex-1">
                            <button class="btn-xs-green">Pošalji</button>
                        </form>
                    @endif
                </article>
            @empty
                <div class="bg-white shadow rounded-lg p-8 text-center text-sm text-gray-500">Još nemate preporuka. Stižu kad vam ih pošalje savjetnik kojemu ste dali pristup.</div>
            @endforelse
        </div>
    </div>
</x-app-layout>
