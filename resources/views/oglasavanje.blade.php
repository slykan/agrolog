@extends('layouts.public', [
    'title' => 'Oglašavanje',
    'description' => 'Oglašavajte proizvode i usluge hrvatskim poljoprivrednicima u AgroLogu: banner, sponzorirani članak, tehnički uvjeti i upit za cijenu.',
    'active' => '',
    'ads' => false,
])

@section('content')
    @php
        $broj = fn ($v) => number_format($v, 0, ',', '.');
        $mjesta = [
            ['Dashboard', 'Prijavljeni poljoprivrednici, pri svakom ulasku u aplikaciju. Ispod karte parcela i prognoze.', 'dashboard'],
            ['Naslovna', 'Posjetitelji koji prvi put upoznaju AgroLog. U bijelom dijelu, odmah ispod uvodnog dijela stranice.', 'naslovna'],
            ['Vodiči i novosti', 'Čitatelji stručnih tekstova o gnojidbi, zaštiti bilja i propisima. Na vrhu svakog teksta.', 'clanci'],
        ];
    @endphp

    <header class="mb-10">
        <p class="text-xs font-bold uppercase tracking-[0.3em] text-green-700">Oglašavanje</p>
        <h1 class="mt-3 text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight">Predstavite svoje proizvode i usluge poljoprivrednicima</h1>
        <p class="mt-3 text-lg text-gray-600">AgroLog koriste gospodarstva za evidenciju prskanja, gnojidbe i berbe. Vaš oglas vide ljudi koji upravo planiraju radove na svojim parcelama.</p>
        <a href="#poruka" class="btn-primary mt-6 inline-flex">Zatražite ponudu</a>
    </header>

    @if($gospodarstava !== null)
        <section aria-label="AgroLog u brojkama" class="mb-12 grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-gray-200 p-4">
                <p class="text-sm text-gray-600">Gospodarstava s parcelama</p>
                <p class="text-2xl font-bold text-gray-900">{{ $broj($gospodarstava) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 p-4">
                <p class="text-sm text-gray-600">Hektara u evidenciji</p>
                <p class="text-2xl font-bold text-gray-900">{{ $broj($hektara) }}</p>
            </div>
            @if($prikazi !== null)
                <div class="rounded-xl border border-gray-200 p-4">
                    <p class="text-sm text-gray-600">Prikaza bannera u 30 dana</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $broj($prikazi) }}</p>
                </div>
            @endif
        </section>
    @endif

    <section class="mb-12">
        <h2 class="text-2xl font-bold text-gray-900">Banner: gdje se prikazuje</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-3">
            @foreach($mjesta as [$naziv, $opis, $kljuc])
                <div class="flex flex-col rounded-xl border border-gray-200 p-5">
                    {{-- mala skica stranice s označenim mjestom oglasa --}}
                    <div class="mb-4 space-y-1.5 rounded-lg bg-gray-100 p-3" aria-hidden="true">
                        <div class="h-2 w-2/5 rounded bg-gray-300"></div>
                        @if($kljuc === 'dashboard')
                            <div class="h-10 rounded bg-gray-300"></div>
                            <div class="h-3 rounded bg-[#86bc25]"></div>
                            <div class="h-5 rounded bg-gray-300"></div>
                        @elseif($kljuc === 'naslovna')
                            <div class="h-10 rounded bg-[#033b2c]"></div>
                            <div class="h-3 rounded bg-[#86bc25]"></div>
                            <div class="h-5 rounded bg-gray-300"></div>
                        @else
                            <div class="h-3 rounded bg-[#86bc25]"></div>
                            <div class="h-2 rounded bg-gray-300"></div>
                            <div class="h-2 rounded bg-gray-300"></div>
                            <div class="h-2 w-4/5 rounded bg-gray-300"></div>
                            <div class="h-2 w-3/5 rounded bg-gray-300"></div>
                        @endif
                    </div>
                    <h3 class="font-bold text-gray-900">{{ $naziv }}</h3>
                    <p class="mt-1 text-sm leading-relaxed text-gray-600">{{ $opis }}</p>
                    <p class="mt-auto pt-3 text-sm font-semibold text-green-800">Cijena na upit</p>
                </div>
            @endforeach
        </div>
        <p class="mt-4 text-sm text-gray-600">Na stranici je uvijek samo jedan banner, označen s „Oglas”. Oglase ne prikazujemo na stranicama za unos evidencije ni na ispisima. Možete izabrati jedno ili sva mjesta te razdoblje prikazivanja.</p>
    </section>

    <section class="mb-12 rounded-xl border border-green-200 bg-green-50 p-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-baseline sm:justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Sponzorirani članak</h2>
            <p class="text-sm font-semibold text-green-800">Cijena na upit</p>
        </div>
        <p class="mt-3 text-gray-700">Predstavite proizvod, uslugu ili savjet u stručnom tekstu objavljenom među novostima na AgroLogu, s poveznicom na vašu stranicu. Tekst možete pripremiti sami ili ga možemo napisati zajedno.</p>
        <p class="mt-3 text-sm text-gray-700">Članak je jasno označen kao sponzorirani sadržaj, a poveznice kao plaćene. Prije objave pregledavamo tekst, koji mora biti točan i koristan poljoprivrednicima.</p>
    </section>

    <section class="mb-12 grid gap-8 md:grid-cols-2">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Što dobivate uz banner</h2>
            <ul class="mt-3 list-disc space-y-2 pl-6 text-gray-600">
                <li>klik vodi izravno na vašu stranicu,</li>
                <li>izvještaj o broju prikaza i klikova po mjestu,</li>
                <li>prikaz se broji tek kad je oglas stvarno bio vidljiv, a pretraživači se ne broje,</li>
                <li>bez kolačića za praćenje posjetitelja.</li>
            </ul>
        </div>
        <div>
            <h2 class="text-xl font-bold text-gray-900">Tehnički uvjeti za banner</h2>
            <ul class="mt-3 list-disc space-y-2 pl-6 text-gray-600">
                <li>računalo: 2172 × 242 px,</li>
                <li>mobitel: 1080 × 400 px,</li>
                <li>JPG, PNG, WebP ili GIF, do 4 MB po slici,</li>
                <li>poveznica na vašu stranicu.</li>
            </ul>
        </div>
    </section>

    <section class="mb-12">
        <h2 class="text-xl font-bold text-gray-900">Pravila</h2>
        <p class="mt-3 text-gray-600">Oglasi moraju biti u skladu s propisima, a posebno oni za sredstva za zaštitu bilja. Za sadržaj oglasa te za proizvode i usluge odgovara oglašivač. Oglas koji nije prikladan za naše korisnike možemo odbiti.</p>
    </section>

    @include('partials.kontakt-obrazac', ['izvor' => 'oglasavanje', 'naslovObrasca' => 'Zatražite ponudu'])
@endsection
