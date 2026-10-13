@include('partials.cookie-consent')

<footer class="bg-gray-900 text-gray-400 py-8 mt-8">
    <div class="max-w-6xl mx-auto px-6 flex flex-col items-center gap-4 text-sm">
        <div class="flex flex-wrap justify-center gap-x-6 gap-y-2">
            <a href="{{ route('alati') }}" class="hover:text-white transition">Alati</a>
            <a href="{{ route('vodici') }}" class="hover:text-white transition">Vodiči</a>
            <a href="{{ route('docs') }}" class="hover:text-white transition">Upute</a>
            <a href="{{ route('about') }}" class="hover:text-white transition">O nama</a>
            <a href="{{ route('contact') }}" class="hover:text-white transition">Kontakt</a>
            <a href="{{ route('login') }}" class="hover:text-white transition">Prijava</a>
            <a href="{{ route('register') }}" class="hover:text-white transition">Registracija</a>
            <a href="{{ route('privacy') }}" class="hover:text-white transition">Politika privatnosti</a>
        </div>
        <span class="text-gray-500 text-center">© {{ date('Y') }} AgroLog. Sva prava pridržana. Izradio <a href="https://on-click.hr" target="_blank" class="hover:text-white transition">@on-click.hr</a></span>
    </div>
</footer>
