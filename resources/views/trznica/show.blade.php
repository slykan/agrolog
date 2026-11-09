@extends('layouts.public', [
    'title' => $proizvod->naziv . ' · ' . $p->naziv,
    'description' => \Illuminate\Support\Str::limit($proizvod->naziv . ', ' . $proizvod->cijenaTekst() . '. ' . $p->naziv . ', ' . $p->mjesto . '. ' . ($proizvod->opis ?? ''), 155),
    'active' => 'trznica',
    'ads' => false,
    'ogType' => 'product',
    'jsonLd' => array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $proizvod->naziv,
        'description' => $proizvod->opis ? \Illuminate\Support\Str::limit($proizvod->opis, 300) : null,
        'image' => $proizvod->slikaUrl(),
        'category' => $proizvod->vrsta,
        'offers' => ['@type' => 'Offer', 'price' => number_format((float) $proizvod->cijena, 2, '.', ''), 'priceCurrency' => 'EUR',
            'availability' => 'https://schema.org/InStock', 'seller' => ['@type' => 'Organization', 'name' => $p->naziv]],
    ]),
])

@section('content')
    <a href="{{ route('trznica') }}" class="text-sm text-green-700 hover:underline">&larr; Tržnica</a>

    <div class="mt-4 grid gap-8 lg:grid-cols-[1fr_20rem]">
        <div class="min-w-0">
            @if($proizvod->slika)
                <img src="{{ $proizvod->slikaUrl() }}" alt="{{ $proizvod->naziv }}" class="w-full max-h-[30rem] object-cover rounded-2xl border border-gray-200">
            @endif
            <p class="mt-5 text-sm text-gray-500"><a href="{{ route('trznica', ['vrsta' => $proizvod->vrsta]) }}" class="rounded-full bg-green-50 px-3 py-1 text-green-900 hover:bg-green-100">{{ $proizvod->vrsta }}</a></p>
            <h1 class="mt-3 text-3xl sm:text-4xl font-bold text-gray-900">{{ $proizvod->naziv }}</h1>
            <p class="mt-2 text-3xl font-extrabold text-green-800">{{ $proizvod->cijenaTekst() }}</p>
            <p class="mt-1 text-xs text-gray-400">Cijena ažurirana {{ $proizvod->updated_at->timezone('Europe/Zagreb')->format('d.m.Y.') }}</p>
            @if($proizvod->opis)
                <div class="mt-5 text-gray-700 leading-relaxed whitespace-pre-line break-words">{{ $proizvod->opis }}</div>
            @endif
        </div>

        {{-- prodavač: tko prodaje, kontakt i svi njegovi oglasi --}}
        <aside class="space-y-4">
            <div class="rounded-2xl border border-gray-200 p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Prodaje</p>
                <div class="mt-2 flex items-center gap-3">
                    @if($p->slika)<img src="{{ $p->slikaUrl() }}" alt="" class="h-12 w-12 rounded-full object-cover border border-gray-200">@endif
                    <div class="min-w-0">
                        <a href="{{ route('gospodarstvo', $p->slug) }}" class="block font-bold text-gray-900 hover:text-green-800">{{ $p->naziv }}</a>
                        <p class="text-sm text-gray-500">{{ $p->mjesto }} · {{ $p->zupanija }}</p>
                    </div>
                </div>
                <a href="{{ route('trznica', ['prodavac' => $p->slug]) }}" class="mt-4 block rounded-lg border border-green-600 px-3 py-2 text-center text-sm font-semibold text-green-800 hover:bg-green-50">Svi oglasi ovog prodavača ({{ $ukupno }})</a>
                @if($p->lat !== null)
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ (float) $p->lat }},{{ (float) $p->lng }}" target="_blank" rel="noopener" class="mt-2 block text-center text-sm text-green-700 underline">Upute za dolazak →</a>
                @endif
            </div>
            @include('gospodarstva._kontakt', ['p' => $p, 'naslov' => 'Kontakt prodavača'])
        </aside>
    </div>

    @if($jos->isNotEmpty())
        <h2 class="mt-12 text-xl font-bold text-gray-900">Još od {{ $p->naziv }}</h2>
        <div class="mt-4 grid gap-4 grid-cols-2 lg:grid-cols-3">
            @foreach($jos as $pr)
                @php $pr->setRelation('prezentacija', $p); @endphp
                @include('trznica._kartica', ['pr' => $pr, 'bezProdavaca' => true])
            @endforeach
        </div>
    @endif

    <p class="mt-12 text-xs text-gray-400">AgroLog ne prodaje i ne posreduje u prodaji. Za proizvod, cijenu i dogovor odgovara gospodarstvo. <a href="{{ route('contact') }}" class="underline">Prijavite neprimjeren oglas</a>.</p>
@endsection
