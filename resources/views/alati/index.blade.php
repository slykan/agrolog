@extends('layouts.public', [
    'title' => 'Besplatni alati za poljoprivrednike',
    'description' => 'Kalkulator škropiva, kalkulator gnojiva (KAN, UREA, NPK), kalkulator karence i pretvornik površina (ha, jutro, čhv) — besplatno, bez prijave.',
    'active' => 'alati',
    'ads' => false, // list page — ads only next to the tools and guides themselves
])

@section('content')
    <span class="inline-block text-xs font-semibold uppercase tracking-widest text-green-600 bg-green-100 px-3 py-1 rounded-full mb-4">Besplatni alati</span>
    <h1 class="text-4xl font-bold text-gray-900 mb-3">Alati za poljoprivrednike</h1>
    <p class="text-lg text-gray-500 mb-10">Bez prijave i registracije. Sve se računa u pregledniku — ništa se ne šalje na poslužitelj.</p>

    <div class="grid sm:grid-cols-2 gap-5">
        @foreach ($alati as $slug => $a)
            <a href="{{ route('alat', $slug) }}" class="block border border-gray-200 rounded-2xl p-6 hover:border-green-400 hover:shadow-md transition">
                <div class="text-3xl mb-3">{{ $a['ico'] }}</div>
                <h2 class="text-lg font-bold text-gray-900 mb-1">{{ $a['naziv'] }}</h2>
                <p class="text-gray-500 text-sm">{{ $a['opis'] }}</p>
            </a>
        @endforeach
    </div>

    <div class="prose-agro mt-12">
        <h2>Vodiči uz alate</h2>
        <ul>
            @foreach (\App\Http\Controllers\SadrzajController::vodici() as $v)
                <li><a href="{{ route('vodic', $v['slug']) }}">{{ $v['title'] }}</a></li>
            @endforeach
        </ul>
    </div>
@endsection
