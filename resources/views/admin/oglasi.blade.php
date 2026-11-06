<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin &mdash; oglasi</h2>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.korisnici') }}" class="text-sm text-green-700 hover:underline">Korisnici &rarr;</a>
                <a href="{{ route('admin.oglasi', ['nova' => 1]) }}" class="btn-primary">+ Novi oglas</a>
            </div>
        </div>
    </x-slot>

    @php
        $broj = fn ($v) => number_format($v, 0, ',', '.');
        $udio = fn ($k, $p) => $p > 0 ? number_format($k / $p * 100, 2, ',', '.') . ' %' : '—';
        $boje = ['prikazuje se' => 'bg-green-100 text-green-800', 'zakazan' => 'bg-blue-100 text-blue-800', 'istekao' => 'bg-amber-100 text-amber-800', 'ugašen' => 'bg-gray-200 text-gray-700'];
        $f = $uredi ?? null;
        $otvoreno = $nova || $f || $errors->any();
    @endphp

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-3 sm:px-6 lg:px-8 space-y-6">
            @if(session('poruka'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('poruka') }}</div>
            @endif

            {{-- Unos --}}
            @if($otvoreno)
                <form method="post" enctype="multipart/form-data" action="{{ $f ? route('admin.oglasi.azuriraj', $f) : route('admin.oglasi.spremi') }}" class="bg-white shadow-sm rounded-xl p-5 space-y-4">
                    @csrf
                    <h3 class="font-semibold text-gray-800">{{ $f ? 'Uredi oglas' : 'Novi oglas' }}</h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block text-sm text-gray-700">Naziv (npr. oglašivač)
                            <input type="text" name="naziv" value="{{ old('naziv', $f?->naziv) }}" required class="form-input w-full mt-1 block">
                            @error('naziv')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="block text-sm text-gray-700">Poveznica
                            <input type="text" name="url" value="{{ old('url', $f?->url ?? 'https://') }}" required placeholder="https://firma.hr" class="form-input w-full mt-1 block">
                            @error('url')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="block text-sm text-gray-700">Slika {{ $f ? '(ostavite prazno da ostane ista)' : '' }}
                            <input type="file" name="slika" accept="image/jpeg,image/png,image/webp,image/gif" @unless($f) required @endunless class="mt-1 block w-full text-sm">
                            <span class="text-xs text-gray-500">Širok banner, preporuka 2172 × 242 px, do 4 MB. JPG, PNG, WebP ili GIF.</span>
                            @error('slika')<span class="block text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="block text-sm text-gray-700">Slika za mobitel <span class="text-gray-500">neobavezno, preporučeno</span>
                            <input type="file" name="slika_mobitel" accept="image/jpeg,image/png,image/webp,image/gif" class="mt-1 block w-full text-sm">
                            <span class="text-xs text-gray-500">Preporuka 1080 × 400 px. Bez nje se širok banner na mobitelu jako smanji.</span>
                            @error('slika_mobitel')<span class="block text-xs text-red-600">{{ $message }}</span>@enderror
                            @if($f?->slika_mobitel)
                                <span class="mt-1 flex items-center gap-2 text-xs text-gray-600"><input type="checkbox" name="makni_mobitel" value="1" class="rounded"> makni postojeću sliku za mobitel</span>
                            @endif
                        </label>
                        <label class="block text-sm text-gray-700">Prikazuj od <span class="text-gray-500">prazno = odmah</span>
                            <input type="date" name="od" value="{{ old('od', $f?->od?->format('Y-m-d')) }}" class="form-input w-full mt-1 block">
                            @error('od')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <label class="block text-sm text-gray-700">Prikazuj do <span class="text-gray-500">uključujući taj dan, prazno = bez kraja</span>
                            <input type="date" name="do" value="{{ old('do', $f?->do?->format('Y-m-d')) }}" class="form-input w-full mt-1 block">
                            @error('do')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </label>
                    </div>
                    <fieldset>
                        <legend class="text-sm font-medium text-gray-800">Gdje se prikazuje</legend>
                        <div class="mt-2 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-700">
                            @foreach(['na_naslovnoj' => 'Naslovna', 'na_dashboardu' => 'Dashboard', 'na_clancima' => 'Vodiči i novosti'] as $k => $naziv)
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="{{ $k }}" value="1" @checked(old($k, $errors->any() ? false : ($f ? $f->$k : true))) class="rounded text-green-600"> {{ $naziv }}
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <div class="flex flex-col gap-2 text-sm text-gray-700">
                        <label class="flex items-center gap-2"><input type="checkbox" name="aktivan" value="1" @checked(old('aktivan', $errors->any() ? false : ($f ? $f->aktivan : true))) class="rounded text-green-600"> Aktivan</label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="rezervni" value="1" @checked(old('rezervni', $f?->rezervni)) class="rounded text-green-600"> Rezervni: prikazuje se samo kad na tom mjestu nema drugih oglasa</label>
                    </div>
                    <div class="flex gap-2">
                        <button class="btn-primary">Spremi</button>
                        <a href="{{ route('admin.oglasi') }}" class="btn-secondary">Odustani</a>
                    </div>
                </form>
            @endif

            {{-- Oglasi --}}
            <div class="space-y-3">
                @foreach($oglasi as $o)
                    <div class="bg-white shadow-sm rounded-xl p-4">
                        <img src="{{ asset($o->slika) }}" alt="" loading="lazy" class="w-full max-h-28 rounded object-cover object-left">
                        <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900">{{ $o->naziv }}
                                    <span class="ml-1 rounded px-2 py-0.5 text-xs font-medium {{ $boje[$o->stanje] }}">{{ $o->stanje }}</span>
                                    @if($o->rezervni)<span class="ml-1 rounded bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">rezervni</span>@endif
                                </p>
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ $o->od ? 'od ' . $o->od->format('d.m.Y.') : 'od odmah' }} · {{ $o->do ? 'do ' . $o->do->format('d.m.Y.') : 'bez kraja' }} ·
                                    {{ collect(['Naslovna' => $o->na_naslovnoj, 'Dashboard' => $o->na_dashboardu, 'Vodiči i novosti' => $o->na_clancima])->filter()->keys()->implode(', ') ?: 'nigdje' }}
                                </p>
                                <p class="text-sm text-gray-600 break-all">{{ $o->url }}</p>
                                @unless($o->slika_mobitel)<p class="text-xs text-amber-800">Nema sliku za mobitel.</p>@endunless
                                @php $z = $zbroj[$o->id] ?? ['prikazi' => 0, 'klikovi' => 0]; @endphp
                                <p class="mt-1 text-sm text-gray-800">Prikazi {{ $broj($z['prikazi']) }} · Klikovi {{ $broj($z['klikovi']) }} · Udio {{ $udio($z['klikovi'], $z['prikazi']) }}</p>
                            </div>
                            <div class="flex shrink-0 gap-2">
                                <a href="{{ route('admin.oglasi', ['uredi' => $o->id]) }}" class="btn-xs-blue">Uredi</a>
                                <form method="post" action="{{ route('admin.oglasi.obrisi', $o) }}" onsubmit="return confirm('Obrisati oglas i njegovu statistiku?')">
                                    @csrf @method('delete')
                                    <button class="btn-xs-red" aria-label="Obriši {{ $o->naziv }}">Obriši</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Statistika --}}
            <div class="bg-white shadow-sm rounded-xl p-4 overflow-x-auto">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="font-semibold text-gray-800">Statistika po mjestu</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['30' => 'Zadnjih 30 dana', 'godina' => 'Ova godina', 'sve' => 'Sve'] as $k => $naziv)
                            <a href="{{ route('admin.oglasi', ['period' => $k]) }}"
                               class="rounded-lg px-3 py-1.5 text-sm font-medium {{ $period === (string) $k ? 'bg-green-700 text-white' : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50' }}">{{ $naziv }}</a>
                        @endforeach
                    </div>
                </div>
                @if($poMjestu->isEmpty())
                    <p class="py-4 text-sm text-gray-600">Za ovo razdoblje još nema podataka.</p>
                @else
                    <table class="mt-3 w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr><th class="px-3 py-2 text-left">Oglas</th><th class="px-3 py-2 text-left">Mjesto</th><th class="px-3 py-2 text-right">Prikazi</th><th class="px-3 py-2 text-right">Klikovi</th><th class="px-3 py-2 text-right">Udio</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($poMjestu as $r)
                                <tr>
                                    <td class="px-3 py-2 text-gray-600">{{ $r['oglas'] }}</td>
                                    <td class="px-3 py-2 font-medium text-gray-800">{{ $r['mjesto'] }}</td>
                                    <td class="px-3 py-2 text-right">{{ $broj($r['prikazi']) }}</td>
                                    <td class="px-3 py-2 text-right">{{ $broj($r['klikovi']) }}</td>
                                    <td class="px-3 py-2 text-right text-gray-600">{{ $udio($r['klikovi'], $r['prikazi']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
                <p class="mt-3 text-sm text-gray-600">Prikaz se broji kad je barem pola bannera vidljivo jednu sekundu. Pretraživači se ne broje. Brojke su zbirne po danu, bez podataka o osobama.</p>
            </div>
        </div>
    </div>
</x-app-layout>
