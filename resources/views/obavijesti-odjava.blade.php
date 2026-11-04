@extends('layouts.public', [
    'title' => 'Odjava od obavijesti',
    'description' => 'Odjava od obavijesti AgroLoga.',
    'active' => '',
    'ads' => false,
])

@section('content')
    <div class="max-w-xl mx-auto text-center py-12">
        <div class="text-5xl mb-4" aria-hidden="true">&#9993;&#65039;</div>
        <h1 class="text-3xl font-bold text-gray-900 mb-3">Odjavljeni ste od obavijesti</h1>
        <p class="text-gray-600 mb-6">
            Na adresu <strong>{{ $user->email }}</strong> više nećemo slati obavijesti o novostima u AgroLogu.
            Vaš račun i evidencije ostaju netaknuti, a važne poruke o računu (npr. promjena lozinke) i dalje stižu.
        </p>
        <p class="text-sm text-gray-500">
            Predomislili ste se? Javite nam se na <a href="mailto:info@on-click.hr" class="underline">info@on-click.hr</a> i ponovno ćemo vas uključiti.
        </p>
        <a href="{{ url('/') }}" class="inline-block mt-8 bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-xl transition">Na AgroLog</a>
    </div>
@endsection
