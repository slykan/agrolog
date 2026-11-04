@extends('layouts.public', [
    'title' => 'Novosti',
    'description' => 'Novosti o AgroLogu i propisima za evidenciju gnojidbe i prskanja: nove mogućnosti, rokovi i promjene obrazaca.',
    'active' => 'novosti',
    'ads' => false,
])

@section('content')
    <span class="inline-block text-xs font-semibold uppercase tracking-widest text-green-600 bg-green-100 px-3 py-1 rounded-full mb-4">Novosti</span>
    <h1 class="text-4xl font-bold text-gray-900 mb-3">Novosti</h1>
    <p class="text-lg text-gray-500 mb-10">Što je novo u AgroLogu i što se mijenja u propisima o evidencijama.</p>
    <div class="space-y-5">
        @foreach ($novosti as $n)
            <a href="{{ route('novost', $n['slug']) }}" class="block border border-gray-200 rounded-2xl p-6 hover:border-green-400 hover:shadow-md transition">
                <div class="text-xs text-gray-400 mb-2">{{ \Illuminate\Support\Carbon::parse($n['date'])->format('d.m.Y.') }} · {{ $n['read'] }} čitanja</div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $n['title'] }}</h2>
                <p class="text-gray-500">{{ $n['excerpt'] }}</p>
            </a>
        @endforeach
    </div>
@endsection
