@extends('layouts.public', [
    'title' => $n['title'],
    'description' => $n['excerpt'],
    'active' => 'novosti',
    'ads' => true,
    'oglasMjesto' => 'novosti',
    'ogType' => 'article',
    'jsonLd' => [
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'headline' => $n['title'],
        'description' => $n['excerpt'],
        'datePublished' => $n['date'],
        'inLanguage' => 'hr',
        'author' => ['@type' => 'Organization', 'name' => 'AgroLog', 'url' => route('about')],
        'publisher' => ['@type' => 'Organization', 'name' => 'AgroLog'],
    ],
])

@section('content')
    <a href="{{ route('novosti') }}" class="text-sm text-gray-500 hover:text-green-700">← Sve novosti</a>
    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mt-3 mb-3 leading-tight">{{ $n['title'] }}</h1>
    <div class="text-sm text-gray-400 mb-8">Uredništvo AgroLog · {{ \Illuminate\Support\Carbon::parse($n['date'])->format('d.m.Y.') }} · {{ $n['read'] }} čitanja</div>

    <article class="prose-agro">
        @include('novosti.posts.'.$n['slug'])
    </article>

    <section class="bg-green-600 rounded-2xl p-10 text-center mt-10">
        <h2 class="text-2xl font-bold text-white mb-2">Evidencija gnojidbe i prskanja bez papira</h2>
        <p class="text-green-100 mb-6">Zapisi po ARKOD parcelama, izvoz u formatu Ministarstva i ispis u standardnom obliku.</p>
        @auth
            <a href="{{ route('prskanje') }}" class="inline-block bg-white hover:bg-gray-50 text-green-700 font-bold px-8 py-3 rounded-xl transition shadow-md">Otvori evidenciju prskanja</a>
        @else
            <a href="{{ route('register') }}" class="inline-block bg-white hover:bg-gray-50 text-green-700 font-bold px-8 py-3 rounded-xl transition shadow-md">Kreirajte račun</a>
        @endauth
    </section>

    @if (count($ostali))
        <h2 class="text-xl font-bold text-gray-900 mt-12 mb-4">Ostale novosti</h2>
        <div class="grid sm:grid-cols-3 gap-4">
            @foreach ($ostali as $o)
                <a href="{{ route('novost', $o['slug']) }}" class="block border border-gray-200 rounded-xl p-5 hover:border-green-400 transition font-semibold text-gray-900">{{ $o['title'] }}</a>
            @endforeach
        </div>
    @endif
@endsection
