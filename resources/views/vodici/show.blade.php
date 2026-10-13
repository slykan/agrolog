@extends('layouts.public', [
    'title' => $v['title'],
    'description' => $v['excerpt'],
    'active' => 'vodici',
    'ads' => true,
    'ogType' => 'article',
    'jsonLd' => [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $v['title'],
        'description' => $v['excerpt'],
        'datePublished' => $v['date'],
        'inLanguage' => 'hr',
        'author' => ['@type' => 'Organization', 'name' => 'AgroLog', 'url' => route('about')],
        'publisher' => ['@type' => 'Organization', 'name' => 'AgroLog'],
    ],
])

@section('content')
    <a href="{{ route('vodici') }}" class="text-sm text-gray-500 hover:text-green-700">← Svi vodiči</a>
    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mt-3 mb-3 leading-tight">{{ $v['title'] }}</h1>
    <div class="text-sm text-gray-400 mb-8">Uredništvo AgroLog · {{ \Illuminate\Support\Carbon::parse($v['date'])->format('d.m.Y.') }} · {{ $v['read'] }} čitanja</div>

    <article class="prose-agro">
        @include('vodici.posts.'.$v['slug'])
    </article>

    <p class="text-sm text-gray-400 mt-8">Vodič je informativnog karaktera i ne zamjenjuje etiketu sredstva, propis ni savjet savjetodavne službe. Ako primijetiš da nešto više nije točno, javi nam se na <a href="{{ route('contact') }}" class="underline">stranici Kontakt</a>.</p>

    <section class="bg-green-600 rounded-2xl p-10 text-center mt-10">
        <h2 class="text-2xl font-bold text-white mb-2">Evidencija gnojidbe i prskanja bez papira</h2>
        <p class="text-green-100 mb-6">Zapisi po ARKOD parcelama, filtriranje i ispis u standardnom obliku.</p>
        <a href="{{ route('register') }}" class="inline-block bg-white hover:bg-gray-50 text-green-700 font-bold px-8 py-3 rounded-xl transition shadow-md">Kreirajte račun</a>
    </section>

    <h2 class="text-xl font-bold text-gray-900 mt-12 mb-4">Pročitaj i ovo</h2>
    <div class="grid sm:grid-cols-3 gap-4">
        @foreach ($ostali as $o)
            <a href="{{ route('vodic', $o['slug']) }}" class="block border border-gray-200 rounded-xl p-5 hover:border-green-400 transition font-semibold text-gray-900">{{ $o['title'] }}</a>
        @endforeach
    </div>
@endsection
