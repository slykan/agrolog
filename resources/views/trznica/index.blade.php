@extends('layouts.public', [
    'title' => $prodavac ? 'Tržnica: ' . $prodavac->naziv : 'Tržnica',
    'description' => $prodavac
        ? 'Proizvodi gospodarstva ' . $prodavac->naziv . ' iz mjesta ' . $prodavac->mjesto . ' s cijenama. Kupite izravno od proizvođača.'
        : 'Tržnica domaćih proizvoda izravno od obiteljskih gospodarstava: med, voće, povrće, vino, ulje i drugo, s cijenama i kontaktom proizvođača.',
    'active' => 'trznica',
    'ads' => false,
])

@section('content')
    <span class="inline-block text-xs font-semibold uppercase tracking-widest text-green-600 bg-green-100 px-3 py-1 rounded-full mb-4">Tržnica</span>
    @if($prodavac)
        <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ $prodavac->naziv }}</h1>
        <p class="text-lg text-gray-500 mb-6">Svi oglasi ovog prodavača · {{ $prodavac->mjesto }}, {{ $prodavac->zupanija }}</p>
        <div class="mb-8 grid gap-4 sm:grid-cols-2">
            @include('gospodarstva._kontakt', ['p' => $prodavac, 'naslov' => 'Kontakt prodavača'])
            <div class="rounded-2xl border border-gray-200 p-5 text-sm text-gray-600 space-y-3">
                <p>{{ \Illuminate\Support\Str::limit($prodavac->opis, 260) }}</p>
                <a href="{{ route('gospodarstvo', $prodavac->slug) }}" class="inline-block font-medium text-green-700 underline">Više o gospodarstvu i lokacija →</a>
                <a href="{{ route('trznica') }}" class="block text-gray-500 underline">← Svi oglasi na Tržnici</a>
            </div>
        </div>
    @else
        <h1 class="text-4xl font-bold text-gray-900 mb-3">Domaće, izravno od proizvođača</h1>
        <p class="text-lg text-gray-500 mb-6">Proizvodi obiteljskih gospodarstava s cijenama. Javite se gospodarstvu i dogovorite kupnju izravno.</p>
        @include('partials.kupujmo-hrvatsko', ['tekst' => 'Med, voće, povrće, vino, ulje i sir od ljudi iz vašeg kraja. Bez posrednika: cijena ide gospodarstvu, a vi znate tko je i gdje je proizveo.'])

        <form method="get" action="{{ route('trznica') }}" class="rounded-2xl border border-gray-200 bg-gray-50 p-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <input type="search" name="q" value="{{ $q }}" placeholder="Proizvod, mjesto ili gospodarstvo" aria-label="Pretraga" class="form-input w-full sm:col-span-2 lg:col-span-1">
            <select name="vrsta" aria-label="Vrsta" class="form-input w-full">
                <option value="">Sve vrste</option>
                @foreach(\App\Models\Prezentacija::OZNAKE as $o)
                    <option value="{{ $o }}" @selected($vrsta === $o)>{{ $o }}</option>
                @endforeach
            </select>
            <select name="zupanija" aria-label="Županija" class="form-input w-full">
                <option value="">Sve županije</option>
                @foreach(\App\Models\Prezentacija::ZUPANIJE as $z)
                    <option value="{{ $z }}" @selected($zupanija === $z)>{{ $z }}</option>
                @endforeach
            </select>
            <button class="btn-primary">Traži</button>
            @if($filtrirano)
                <a href="{{ route('trznica') }}" class="text-sm text-green-700 underline sm:col-span-2 lg:col-span-4">Poništi filtere</a>
            @endif
        </form>
    @endif

    @if($proizvodi->isEmpty())
        <div class="mt-10 text-center text-gray-500">
            <p>{{ $filtrirano ? 'Nema proizvoda za odabrane filtere.' : 'Tržnica se tek puni.' }}</p>
            <p class="text-sm mt-2">Imate gospodarstvo? Predstavite se na popisu <a href="{{ route('gospodarstva') }}" class="text-green-700 underline">Gospodarstva</a> i dodajte proizvode.</p>
        </div>
    @else
        <div class="mt-8 grid gap-4 grid-cols-2 lg:grid-cols-3">
            @foreach($proizvodi as $pr)
                @include('trznica._kartica', ['pr' => $pr, 'bezProdavaca' => (bool) $prodavac])
            @endforeach
        </div>
        <div class="mt-8">{{ $proizvodi->links() }}</div>
    @endif

    <div class="mt-12 rounded-2xl bg-green-50 border border-green-200 p-6">
        <h2 class="text-lg font-bold text-gray-900">Prodajete domaće proizvode?</h2>
        <p class="mt-1 text-gray-600">Oglasi su besplatni. Uključite <strong>Prezentaciju na AgroLogu</strong> u profilu, a kad je odobrimo, proizvode dodajete pod <strong>Tržnica → Moji proizvodi</strong>.</p>
        <a href="{{ auth()->check() ? route('trznica.moji') : route('register') }}" class="inline-block mt-4 btn-primary">{{ auth()->check() ? 'Moji proizvodi' : 'Otvori besplatan račun' }}</a>
    </div>
    <p class="mt-6 text-xs text-gray-400">AgroLog ne prodaje i ne posreduje u prodaji. Za proizvod, cijenu i dogovor odgovara gospodarstvo koje je objavilo oglas.</p>
@endsection
