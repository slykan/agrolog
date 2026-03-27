<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AgroLog – Digitalni dnevnik gnojidbe i prskanja</title>
    <meta name="description" content="AgroLog je jednostavna web aplikacija za vođenje evidencije gnojidbe i prskanja po ARKOD parcelama. Digitalizirajte svoje agro zapise i ispišite ih u standardnom obliku.">
    <meta name="robots" content="index, follow">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-800 bg-white">

    {{-- NAV --}}
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="/" class="flex items-center">
                <x-application-logo style="width:180px; height:45px;" />
            </a>
            <nav class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-green-700 transition">Moj račun</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-green-700 transition">Prijava</a>
                    <a href="{{ route('register') }}" class="text-sm font-semibold bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">Registracija</a>
                @endauth
            </nav>
        </div>
    </header>

    {{-- HERO --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-green-50 via-white to-emerald-50 pt-20 pb-24">
        <div class="max-w-6xl mx-auto px-6 grid md:grid-cols-2 gap-12 items-center">
            <div>
                <span class="inline-block text-xs font-semibold uppercase tracking-widest text-green-600 bg-green-100 px-3 py-1 rounded-full mb-4">Digitalna evidencija</span>
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 leading-tight mb-5">
                    Agro zapisi<br>
                    <span class="text-green-600">bez papira.</span>
                </h1>
                <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                    Vodite evidenciju gnojidbe i prskanja po ARKOD parcelama, filtrirajte zapise i ispišite ih u standardnom obliku — sve na jednom mjestu.
                </p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-xl transition shadow-sm">
                        Isprobaj besplatno
                    </a>
                    <a href="{{ route('login') }}" class="bg-white hover:bg-gray-50 text-gray-700 font-semibold px-6 py-3 rounded-xl border border-gray-200 transition">
                        Već imam račun
                    </a>
                </div>
            </div>
            <div class="hidden md:block">
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="bg-green-600 px-5 py-3 flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-white/40"></div>
                        <div class="w-3 h-3 rounded-full bg-white/40"></div>
                        <div class="w-3 h-3 rounded-full bg-white/40"></div>
                        <span class="ml-2 text-white/80 text-xs font-mono">Gnojidba – {{ date('Y') }}</span>
                    </div>
                    <div class="p-5">
                        <table class="w-full text-xs text-gray-700">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="text-left pb-2 font-semibold text-gray-500">ARKOD</th>
                                    <th class="text-left pb-2 font-semibold text-gray-500">Kultura</th>
                                    <th class="text-left pb-2 font-semibold text-gray-500">Gnojivo</th>
                                    <th class="text-right pb-2 font-semibold text-gray-500">kg/ha</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr><td class="py-2">1234567</td><td class="py-2">Pšenica</td><td class="py-2">NPK 15-15-15</td><td class="py-2 text-right font-mono">320</td></tr>
                                <tr><td class="py-2">1234567</td><td class="py-2">Pšenica</td><td class="py-2">UREA 46%</td><td class="py-2 text-right font-mono">180</td></tr>
                                <tr><td class="py-2">9876543</td><td class="py-2">Kukuruz</td><td class="py-2">NPK 7-20-30</td><td class="py-2 text-right font-mono">400</td></tr>
                                <tr><td class="py-2">9876543</td><td class="py-2">Kukuruz</td><td class="py-2">KAN 27%</td><td class="py-2 text-right font-mono">210</td></tr>
                            </tbody>
                        </table>
                        <div class="mt-4 flex justify-end">
                            <span class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full font-medium">↓ Ispis PDF</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FEATURES --}}
    <section class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center mb-14">
                <h2 class="text-3xl font-bold text-gray-900 mb-3">Sve što vam treba za agro evidenciju</h2>
                <p class="text-gray-500 max-w-xl mx-auto">Dizajnirano prema stvarnim potrebama poljoprivrednika i zahtjevima AGRONET sustava.</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="group p-7 rounded-2xl border border-gray-100 hover:border-green-200 hover:shadow-lg transition bg-white">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-5 group-hover:bg-green-600 transition">
                        <svg class="w-6 h-6 text-green-600 group-hover:text-white transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">ARKOD Parcele</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Upravljajte parcelama i kulturama. Pratite posađene površine po ARKOD broju i godini uzgoja.</p>
                </div>
                <div class="group p-7 rounded-2xl border border-gray-100 hover:border-green-200 hover:shadow-lg transition bg-white">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-5 group-hover:bg-green-600 transition">
                        <svg class="w-6 h-6 text-green-600 group-hover:text-white transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Evidencija gnojidbe</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Bilježite datum, vrstu gnojiva i količinu po ha. Filtrirajte i pretražujte zapise po parcelama i kulturama.</p>
                </div>
                <div class="group p-7 rounded-2xl border border-gray-100 hover:border-green-200 hover:shadow-lg transition bg-white">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-5 group-hover:bg-green-600 transition">
                        <svg class="w-6 h-6 text-green-600 group-hover:text-white transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Evidencija prskanja</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Svi podaci o tretiranju: sredstvo, doza, površina, vrijeme tretiranja i količina vode — prema obrascu.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section class="py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-6">
            <div class="text-center mb-14">
                <h2 class="text-3xl font-bold text-gray-900 mb-3">Kako funkcionira?</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-10">
                <div class="text-center">
                    <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg mx-auto mb-4">1</div>
                    <h3 class="font-semibold text-gray-900 mb-2">Registrirajte se</h3>
                    <p class="text-sm text-gray-500">Unesite naziv gospodarstva, MIPG i OIB. Registracija traje manje od minute.</p>
                </div>
                <div class="text-center">
                    <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg mx-auto mb-4">2</div>
                    <h3 class="font-semibold text-gray-900 mb-2">Dodajte parcele</h3>
                    <p class="text-sm text-gray-500">Unesite ARKOD brojeve parcela i kulture koje uzgajate s posađenom površinom.</p>
                </div>
                <div class="text-center">
                    <div class="w-10 h-10 bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg mx-auto mb-4">3</div>
                    <h3 class="font-semibold text-gray-900 mb-2">Vodite evidenciju</h3>
                    <p class="text-sm text-gray-500">Bilježite gnojidbu i prskanje u tablici. Filtrirajte i ispišite PDF u jednom kliku.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-20 bg-green-600">
        <div class="max-w-2xl mx-auto px-6 text-center">
            <h2 class="text-3xl font-bold text-white mb-4">Počnite danas — besplatno</h2>
            <p class="text-green-100 mb-8 text-lg">Digitalizirajte svoje agro zapise i uvijek imajte pregled nad parcelama.</p>
            <a href="{{ route('register') }}" class="inline-block bg-white hover:bg-gray-50 text-green-700 font-bold px-8 py-4 rounded-xl transition shadow-md text-lg">
                Kreirajte račun
            </a>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="bg-gray-900 text-gray-400 py-8">
        <div class="max-w-6xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4 text-sm">
            <span class="text-gray-500">© {{ date('Y') }} AgroLog. Sva prava pridržana.</span>
            <div class="flex gap-6">
                <a href="{{ route('login') }}" class="hover:text-white transition">Prijava</a>
                <a href="{{ route('register') }}" class="hover:text-white transition">Registracija</a>
            </div>
        </div>
    </footer>

</body>
</html>
