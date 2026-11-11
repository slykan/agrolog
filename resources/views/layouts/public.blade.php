<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} – AgroLog</title>
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">
    @include('partials.favicon')
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $title }} – AgroLog">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:locale" content="hr_HR">
    <meta name="google-adsense-account" content="ca-pub-3054841074530422">
    @if ($ads ?? false)
        {{-- Oglasi samo na sadržajnim stranicama (alati, vodiči) i tek uz privolu za kolačiće (partials/cookie-consent) --}}
        <script>
            window.agroOglasi = function () {
                if (window.agroOglasiUcitani) return;
                window.agroOglasiUcitani = true;
                var s = document.createElement('script');
                s.async = true; s.crossOrigin = 'anonymous';
                s.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3054841074530422';
                document.head.appendChild(s);
            };
            try { if (localStorage.getItem('cookie_consent') === 'accepted') window.agroOglasi(); } catch (e) {}
        </script>
    @endif
    @isset($jsonLd)
        <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endisset
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .prose-agro { color: #374151; font-size: 1.05rem; line-height: 1.75; }
        .prose-agro h2 { font-size: 1.45rem; font-weight: 700; color: #111827; margin: 2.2rem 0 .8rem; }
        .prose-agro h3 { font-size: 1.15rem; font-weight: 700; color: #19653a; margin: 1.6rem 0 .5rem; }
        .prose-agro p { margin-bottom: 1rem; }
        .prose-agro ul, .prose-agro ol { margin: 0 0 1rem 1.4rem; }
        .prose-agro ul { list-style: disc; }
        .prose-agro ol { list-style: decimal; }
        .prose-agro li { margin-bottom: .4rem; }
        .prose-agro a { color: #19653a; font-weight: 600; text-decoration: underline; }
        .prose-agro strong { color: #111827; }
        .tbl { width: 100%; border-collapse: collapse; margin: .5rem 0 1.25rem; font-size: .97rem; }
        .tbl th, .tbl td { text-align: left; padding: .6rem .75rem; border-bottom: 1px solid #e5e7eb; }
        .tbl th { background: #f3f8ed; color: #0d4f34; font-weight: 700; }
        .tbl td.num, .tbl th.num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .box-note { background: #f3f8ed; border-left: 4px solid #2b7d3b; border-radius: .75rem; padding: 1rem 1.25rem; margin: 1.25rem 0; color: #033b2c; }
        .box-warn { background: #fffbeb; border-left: 4px solid #f59e0b; border-radius: .75rem; padding: 1rem 1.25rem; margin: 1.25rem 0; color: #78350f; }
        .fld label { display: block; font-size: .85rem; font-weight: 600; color: #4b5563; margin-bottom: .35rem; }
        .fld input, .fld select { width: 100%; border: 1.5px solid #d1d5db; border-radius: .6rem; padding: .65rem .8rem; font-size: 1rem; background: #fff; }
        .fld input:focus, .fld select:focus { outline: none; border-color: #2b7d3b; box-shadow: 0 0 0 3px rgba(22,163,74,.15); }
        .fld .hint { font-size: .8rem; color: #6b7280; margin-top: .3rem; }
        .result { background: #f3f8ed; border-radius: 1rem; padding: 1.25rem 1.5rem; margin-top: 1.25rem; }
        .result .big { font-size: 1.9rem; font-weight: 800; color: #19653a; }
        .result .lbl { font-size: .85rem; color: #0d4f34; font-weight: 600; }
        @media print { header, footer, .no-print { display: none !important; } }
    </style>
</head>
<body class="font-sans antialiased text-gray-800 bg-white">

    @include('partials.nav', ['active' => $active ?? ''])

    <main class="max-w-4xl mx-auto px-6 py-12">
        @if(! empty($oglasMjesto))
            @include('partials.oglas', ['mjesto' => $oglasMjesto, 'klasa' => 'mb-8'])
        @endif
        @yield('content')
    </main>

    @include('partials.footer')
    @stack('scripts')
    @include('partials.tawk')
</body>
</html>
