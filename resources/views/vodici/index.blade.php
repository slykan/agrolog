@extends('layouts.public', [
    'title' => 'Vodiči za poljoprivrednike',
    'description' => 'Vodiči: elektronička evidencija sredstava za zaštitu bilja, izračun škropiva, karenca i gnojidba dušikom (KAN, UREA, NPK).',
    'active' => 'vodici',
    'ads' => false, // list page — ads only next to the tools and guides themselves
])

@section('content')
    <span class="inline-block text-xs font-semibold uppercase tracking-widest text-green-600 bg-green-100 px-3 py-1 rounded-full mb-4">Vodiči</span>
    <h1 class="text-4xl font-bold text-gray-900 mb-3">Vodiči za poljoprivrednike</h1>
    <p class="text-lg text-gray-500 mb-10">Propisi, izračuni i praktični savjeti za evidenciju, zaštitu bilja i gnojidbu.</p>
    <div class="space-y-5">
        @foreach ($vodici as $v)
            <a href="{{ route('vodic', $v['slug']) }}" class="block border border-gray-200 rounded-2xl p-6 hover:border-green-400 hover:shadow-md transition">
                <div class="text-xs text-gray-400 mb-2">{{ \Illuminate\Support\Carbon::parse($v['date'])->format('d.m.Y.') }} · {{ $v['read'] }} čitanja</div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $v['title'] }}</h2>
                <p class="text-gray-500">{{ $v['excerpt'] }}</p>
            </a>
        @endforeach
    </div>
@endsection
