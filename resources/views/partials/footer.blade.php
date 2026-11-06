@include('partials.cookie-consent')

<footer class="bg-gray-900 text-gray-400 py-8 mt-8">
    <div class="max-w-6xl mx-auto px-6 flex flex-col items-center gap-4 text-sm">
        <a href="{{ url('/') }}" aria-label="agro-log.app — naslovnica"><x-application-logo bijeli class="h-9" /></a>
        <div class="flex flex-wrap justify-center gap-x-6 gap-y-2">
            <a href="{{ route('alati') }}" class="hover:text-white transition">Alati</a>
            <a href="{{ route('novosti') }}" class="hover:text-white transition">Novosti</a>
            <a href="{{ route('vodici') }}" class="hover:text-white transition">Vodiči</a>
            <a href="{{ route('docs') }}" class="hover:text-white transition">Upute</a>
            <a href="{{ route('about') }}" class="hover:text-white transition">O nama</a>
            <a href="{{ route('contact') }}" class="hover:text-white transition">Kontakt</a>
            <a href="{{ route('login') }}" class="hover:text-white transition">Prijava</a>
            <a href="{{ route('register') }}" class="hover:text-white transition">Registracija</a>
        </div>
        <div class="flex flex-wrap justify-center gap-x-6 gap-y-2">
            <a href="{{ route('uvjeti') }}" class="hover:text-white transition">Uvjeti korištenja</a>
            <a href="{{ route('privacy') }}" class="hover:text-white transition">Pravila privatnosti</a>
            <a href="{{ route('kolacici') }}" class="hover:text-white transition">Pravila o kolačićima</a>
            <a href="{{ route('brisanje') }}" class="hover:text-white transition">Brisanje računa</a>
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('agro-kolacici'))" class="hover:text-white transition">Postavke kolačića</button>
        </div>
        <span class="text-gray-500 text-center">© {{ date('Y') }} AgroLog. Sva prava pridržana. Izradio <a href="https://on-click.hr" target="_blank" class="hover:text-white transition">@on-click.hr</a></span>
    </div>
</footer>
