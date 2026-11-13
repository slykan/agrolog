<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('naslov') – AgroLog</title>
    <meta name="description" content="@yield('opis')">
    <link rel="canonical" href="{{ url()->current() }}">
    @include('partials.favicon')
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('naslov') – AgroLog">
    <meta property="og:description" content="@yield('opis')">
    <meta property="og:locale" content="hr_HR">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-800 bg-white">
    @include('partials.nav')

    <main class="max-w-3xl mx-auto px-6 py-16">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">@yield('naslov')</h1>
        <p class="text-sm text-gray-500 mb-10">Zadnja izmjena: 06.11.2026.</p>

        <div class="pravno space-y-8 text-gray-600 leading-relaxed">
            @yield('sadrzaj')
        </div>

        <nav class="mt-12 pt-8 border-t border-gray-100 flex flex-wrap gap-x-5 gap-y-2 text-sm" aria-label="Pravne stranice">
            <a href="{{ route('uvjeti') }}" class="text-green-700 hover:underline">Uvjeti korištenja</a>
            <a href="{{ route('privacy') }}" class="text-green-700 hover:underline">Pravila privatnosti</a>
            <a href="{{ route('kolacici') }}" class="text-green-700 hover:underline">Pravila o kolačićima</a>
            <a href="{{ route('brisanje') }}" class="text-green-700 hover:underline">Brisanje računa</a>
        </nav>
    </main>

    @include('partials.footer')
    @include('partials.puls')
</body>
</html>
