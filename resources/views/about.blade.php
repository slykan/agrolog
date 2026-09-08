<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>O nama – AgroLog</title>
    <meta name="description" content="AgroLog je digitalni alat za vođenje evidencije gnojidbe i prskanja za hrvatske poljoprivrednike. Razvijen s razumijevanjem stvarnih potreba na terenu.">
    <link rel="canonical" href="{{ route('about') }}">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('about') }}">
    <meta property="og:title" content="O nama – AgroLog">
    <meta property="og:description" content="AgroLog je digitalni alat za vođenje evidencije gnojidbe i prskanja za hrvatske poljoprivrednike. Razvijen s razumijevanjem stvarnih potreba na terenu.">
    <meta property="og:locale" content="hr_HR">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-800 bg-white">

    {{-- NAV --}}
    @include('partials.nav', ['active' => 'about'])

    {{-- HERO --}}
    <section class="bg-gradient-to-br from-green-50 via-white to-emerald-50 py-20">
        <div class="max-w-3xl mx-auto px-6 text-center">
            <span class="inline-block text-xs font-semibold uppercase tracking-widest text-green-600 bg-green-100 px-3 py-1 rounded-full mb-4">O nama</span>
            <h1 class="text-4xl md:text-5xl font-bold text-gray-900 leading-tight mb-5">
                Nastali iz potrebe,<br>
                <span class="text-green-600">ostali iz strasti.</span>
            </h1>
            <p class="text-lg text-gray-500 leading-relaxed">
                AgroLog je nastao kao odgovor na svakodnevni izazov s kojim se suočavaju hrvatski poljoprivrednici — ručno vođenje evidencije gnojidbe i prskanja, hrpe papira i izgubljeno vrijeme.
            </p>
        </div>
    </section>

    <main class="max-w-4xl mx-auto px-6 py-16 space-y-20">

        {{-- MISIJA --}}
        <section class="grid md:grid-cols-2 gap-12 items-center">
            <div>
                <span class="text-xs font-semibold uppercase tracking-widest text-green-600">Naša misija</span>
                <h2 class="text-2xl font-bold text-gray-900 mt-2 mb-4">Digitalizacija tamo gdje je najpotrebnija</h2>
                <p class="text-gray-600 leading-relaxed mb-4">
                    Svaki poljoprivrednik u Hrvatskoj obvezan je voditi evidenciju o primjeni gnojiva i sredstava za zaštitu bilja. AgroLog tu obavezu pretvara u jednostavan, brz i pregledan digitalni proces.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    Naš cilj je da svaki farmer — bez obzira na veličinu gospodarstva — može u nekoliko klikova unijeti, pregledati i ispisati svoju evidenciju u skladu s AGRONET zahtjevima.
                </p>
            </div>
            <div class="bg-green-50 border border-green-100 rounded-2xl p-8 space-y-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800 text-sm">Jednostavnost prije svega</div>
                        <div class="text-gray-500 text-sm">Sučelje prilagođeno stvarnim korisnicima, ne IT stručnjacima.</div>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800 text-sm">Usklađenost s propisima</div>
                        <div class="text-gray-500 text-sm">Obrazci i ispisi u skladu s važećim AGRONET standardima.</div>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800 text-sm">Otvorena komunikacija</div>
                        <div class="text-gray-500 text-sm">Svaki prijedlog ili problem rješavamo zajedno s korisnicima.</div>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800 text-sm">Vaš uspjeh je i naš uspjeh</div>
                        <div class="text-gray-500 text-sm">Rastemo i razvijamo se zajedno s našom zajednicom korisnika.</div>
                    </div>
                </div>
            </div>
        </section>

        {{-- TKO SMO --}}
        <section class="text-center">
            <span class="text-xs font-semibold uppercase tracking-widest text-green-600">Iza projekta</span>
            <h2 class="text-2xl font-bold text-gray-900 mt-2 mb-12">Razvoj i podrška</h2>
            <div class="max-w-2xl mx-auto bg-white border border-gray-100 rounded-2xl shadow-sm p-10">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-1">Alan Fadljević</h3>
                <p class="text-green-600 text-sm font-medium mb-4">ON CLICK — web dizajn i razvoj aplikacija, Osijek</p>
                <p class="text-gray-500 leading-relaxed text-sm mb-6">
                    AgroLog je razvijen u sklopu agencije <strong class="text-gray-700">ON CLICK</strong> iz Osijeka, s ciljem pružanja modernog i pristupačnog digitalnog alata hrvatskim poljoprivrednicima. Specijaliziramo se za razvoj web aplikacija prilagođenih stvarnim poslovnim potrebama.
                </p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="mailto:info@agro-log.app" class="inline-flex items-center gap-2 bg-green-50 hover:bg-green-100 text-green-700 text-sm font-medium px-4 py-2 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        info@agro-log.app
                    </a>
                    <a href="https://on-click.hr" target="_blank" class="inline-flex items-center gap-2 bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        on-click.hr
                    </a>
                </div>
            </div>
        </section>

        {{-- CTA --}}
        <section class="bg-green-600 rounded-2xl p-12 text-center">
            <h2 class="text-2xl font-bold text-white mb-3">Isprobajte AgroLog besplatno</h2>
            <p class="text-green-100 mb-6">Registracija traje manje od minute. Bez kreditne kartice.</p>
            <a href="{{ route('register') }}" class="inline-block bg-white hover:bg-gray-50 text-green-700 font-bold px-8 py-3 rounded-xl transition shadow-md">
                Kreirajte račun
            </a>
        </section>

    </main>

    {{-- FOOTER --}}
    @include('partials.footer')

</body>
</html>
