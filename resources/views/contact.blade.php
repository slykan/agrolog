<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kontakt – AgroLog</title>
    <meta name="description" content="Kontaktirajte nas za pitanja, prijedloge ili tehničku podršku vezanu uz AgroLog aplikaciju.">
    <link rel="canonical" href="{{ route('contact') }}">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('contact') }}">
    <meta property="og:title" content="Kontakt – AgroLog">
    <meta property="og:description" content="Kontaktirajte nas za pitanja, prijedloge ili tehničku podršku vezanu uz AgroLog aplikaciju.">
    <meta property="og:locale" content="hr_HR">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-800 bg-white">

    {{-- NAV --}}
    @include('partials.nav', ['active' => 'contact'])

    {{-- HERO --}}
    <section class="bg-gradient-to-br from-green-50 via-white to-emerald-50 py-20">
        <div class="max-w-3xl mx-auto px-6 text-center">
            <span class="inline-block text-xs font-semibold uppercase tracking-widest text-green-600 bg-green-100 px-3 py-1 rounded-full mb-4">Kontakt</span>
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 leading-tight mb-5">
                Tu smo za vas.<br>
                <span class="text-green-600">Javite se slobodno.</span>
            </h1>
            <p class="text-lg text-gray-500 leading-relaxed">
                Imate pitanje, prijedlog ili problem? Odgovaramo brzo — obično isti dan.
            </p>
        </div>
    </section>

    <main class="max-w-4xl mx-auto px-6 py-16">

        {{-- Kontakt kartice --}}
        <div class="grid md:grid-cols-3 gap-6 mb-16">

            <a href="mailto:info@agro-log.app" class="group bg-white border border-gray-100 hover:border-green-200 hover:shadow-lg rounded-2xl p-8 text-center transition">
                <div class="w-14 h-14 bg-green-100 group-hover:bg-green-600 rounded-2xl flex items-center justify-center mx-auto mb-5 transition">
                    <svg class="w-6 h-6 text-green-600 group-hover:text-white transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">E-mail</h3>
                <p class="text-sm text-gray-500 mb-3">Pišite nam u bilo koje doba</p>
                <span class="text-sm font-medium text-green-700">info@agro-log.app</span>
            </a>

            <a href="tel:+385989876697" class="group bg-white border border-gray-100 hover:border-green-200 hover:shadow-lg rounded-2xl p-8 text-center transition">
                <div class="w-14 h-14 bg-green-100 group-hover:bg-green-600 rounded-2xl flex items-center justify-center mx-auto mb-5 transition">
                    <svg class="w-6 h-6 text-green-600 group-hover:text-white transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">Telefon</h3>
                <p class="text-sm text-gray-500 mb-3">Pon – Pet, 8:00 – 16:00</p>
                <span class="text-sm font-medium text-green-700">+385 98 987 66 97</span>
            </a>

            <a href="https://on-click.hr" target="_blank" class="group bg-white border border-gray-100 hover:border-green-200 hover:shadow-lg rounded-2xl p-8 text-center transition">
                <div class="w-14 h-14 bg-green-100 group-hover:bg-green-600 rounded-2xl flex items-center justify-center mx-auto mb-5 transition">
                    <svg class="w-6 h-6 text-green-600 group-hover:text-white transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">Web</h3>
                <p class="text-sm text-gray-500 mb-3">Razvoj i podrška</p>
                <span class="text-sm font-medium text-green-700">on-click.hr</span>
            </a>

        </div>

        {{-- Info box --}}
        <div class="bg-green-50 border border-green-100 rounded-2xl p-8 flex flex-col md:flex-row gap-6 items-start">
            <div class="w-10 h-10 bg-green-600 rounded-xl flex items-center justify-center flex-shrink-0 mt-1">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 mb-2">Prijedlozi i poboljšanja</h3>
                <p class="text-gray-600 text-sm leading-relaxed">
                    AgroLog se stalno razvija prema potrebama korisnika. Ako vam nedostaje neka funkcionalnost, imate prijedlog za poboljšanje ili ste naišli na grešku — javite nam se. Svaki prijedlog uzimamo ozbiljno i trudimo se odgovoriti u najkraćem mogućem roku.
                </p>
            </div>
        </div>

    </main>

    {{-- FOOTER --}}
    @include('partials.footer')

</body>
</html>
