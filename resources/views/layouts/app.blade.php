<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        @include('partials.favicon')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="relative min-h-screen bg-gray-100">
            {{-- nepomična pozadina od njiva (vidi se u razmacima oko sadržaja); manje traktora nego na naslovnici --}}
            <div class="fixed inset-0 z-0 pointer-events-none" aria-hidden="true" x-data="poljaTraktori({ min: 2, max: 4 })"></div>
            <div class="relative z-10">
            @include('layouts.navigation')

            {{-- savjetnik radi u klijentovom gospodarstvu --}}
            @if($kp = request()->attributes->get('pristup'))
                @php $kv = $kp->vlasnik; @endphp
                <div class="bg-[#86bc25] text-[#022a20]">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between text-sm">
                        <p>
                            <span class="font-semibold">Radite u gospodarstvu {{ $kv->naziv_gospodarstva ?: $kv->name }}</span>
                            <span class="opacity-80"> · {{ \App\Models\Pristup::PRAVA[$kp->prava] }}{{ $kp->prava === 'unos' ? ' — izmjene se bilježe u povijest gospodarstva' : ' — izmjene nisu moguće' }}</span>
                        </p>
                        <div class="flex gap-2 shrink-0">
                            <a href="{{ route('klijenti') }}" class="px-3 py-1 rounded-lg bg-white/40 hover:bg-white/60 font-medium">Klijenti</a>
                            <form method="post" action="{{ route('klijenti.zatvori') }}">@csrf
                                <button class="px-3 py-1 rounded-lg bg-[#022a20] text-white hover:bg-[#033b2c] font-semibold">← Natrag na svoje</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
            @if(session('status') && ! in_array(session('status'), ['profile-updated', 'password-updated', 'verification-link-sent', 'savjetnik-poslano', 'savjetnik-prava', 'savjetnik-ukinut'], true) && ! request()->routeIs('klijenti'))
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-3"><p class="rounded-lg bg-amber-50 border border-amber-200 px-4 py-2 text-sm text-amber-900">{{ session('status') }}</p></div>
            @endif

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-4 sm:py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
            </div>
        </div>
        @include('partials.instaliraj')
    </body>
</html>
