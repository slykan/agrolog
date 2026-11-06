<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin &mdash; ponude oglašivačima</h2>
            <a href="{{ route('admin.oglasi') }}" class="text-sm text-green-700 hover:underline">Oglasi &rarr;</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">
            @if(session('poruka'))
                <div role="status" class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('poruka') }}</div>
            @endif
            @if(session('poruka_greska'))
                <div role="alert" class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ session('poruka_greska') }}</div>
            @endif

            <form method="post" action="{{ route('admin.ponude.posalji') }}" class="bg-white shadow-sm rounded-xl p-5 space-y-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm text-gray-700">Naziv tvrtke
                        <input type="text" name="tvrtka" value="{{ old('tvrtka') }}" required maxlength="150" class="form-input w-full mt-1 block">
                        @error('tvrtka')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                    <label class="block text-sm text-gray-700">E-mail adresa
                        <input type="email" name="email" value="{{ old('email') }}" required maxlength="190" class="form-input w-full mt-1 block">
                        @error('email')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                    </label>
                </div>
                <label class="block text-sm text-gray-700">Što prodaju
                    <input type="text" name="proizvodi" value="{{ old('proizvodi') }}" required maxlength="150" placeholder="npr. gnojiva i sredstva za zaštitu bilja" class="form-input w-full mt-1 block">
                    @error('proizvodi')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm text-gray-700">Naslov maila
                    <input type="text" name="predmet" value="{{ old('predmet', $predmet) }}" required maxlength="150" class="form-input w-full mt-1 block">
                    @error('predmet')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                </label>
                <label class="block text-sm text-gray-700">Tekst
                    <textarea name="tekst" rows="15" required maxlength="5000" class="form-input w-full mt-1 block">{{ old('tekst', $tekst) }}</textarea>
                    <span class="text-xs text-gray-600">Oznake {tvrtka} i {proizvodi} popune se same. Gumb „Pogledajte mjesta i uvjete” i napomena o odbijanju dodaju se u mail automatski.</span>
                    @error('tekst')<span class="block text-xs text-red-600">{{ $message }}</span>@enderror
                </label>

                @if(session('vec_poslano'))
                    <label class="flex items-start gap-2 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                        <input type="checkbox" name="ipak" value="1" class="mt-0.5 rounded">
                        <span>Ovoj adresi je već poslano {{ session('vec_poslano') }}. Označite ako je ipak želite poslati ponovno.</span>
                    </label>
                @endif

                <div class="flex flex-wrap gap-2">
                    <button name="akcija" value="proba" class="btn-secondary">Pošalji probu meni</button>
                    <button name="akcija" value="posalji" class="btn-primary">Pošalji tvrtki</button>
                </div>
            </form>

            {{-- zapisnik --}}
            <section class="bg-white shadow-sm rounded-xl p-4">
                <h3 class="font-semibold text-gray-800">Poslano ({{ $poslane->count() }})</h3>
                @if($poslane->isEmpty())
                    <p class="mt-2 text-sm text-gray-600">Još ništa nije poslano.</p>
                @else
                    <ul class="mt-2 divide-y divide-gray-100 text-sm">
                        @foreach($poslane as $p)
                            <li class="flex flex-col gap-1 py-2 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <span class="whitespace-nowrap text-gray-600">{{ $p->poslano_at->timezone('Europe/Zagreb')->format('d.m.Y. H:i') }}</span>
                                    <span class="font-medium text-gray-900"> · {{ $p->tvrtka }}</span>
                                    <span class="break-all text-gray-600"> · {{ $p->email }}</span>
                                    @if($p->ne_zeli_at)<span class="ml-1 rounded bg-gray-200 px-1.5 py-0.5 text-xs font-medium text-gray-700">ne želi poruke</span>@endif
                                </div>
                                <form method="post" action="{{ route('admin.ponude.ne-zeli', $p) }}" class="shrink-0">
                                    @csrf
                                    <button class="text-xs text-gray-600 underline hover:text-gray-900">{{ $p->ne_zeli_at ? 'Poništi' : 'Ne želi poruke' }}</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
