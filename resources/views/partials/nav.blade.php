@php $active = $active ?? ''; @endphp
<header x-data="{ open: false }" class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="/" class="flex items-center">
            <x-application-logo style="width:180px; height:45px;" />
        </a>

        {{-- Desktop nav --}}
        <nav class="hidden md:flex items-center gap-3">
            <a href="{{ route('alati') }}" class="text-sm font-medium transition {{ $active === 'alati' ? 'text-green-700' : 'text-gray-600 hover:text-green-700' }}">Alati</a>
            <a href="{{ route('vodici') }}" class="text-sm font-medium transition {{ $active === 'vodici' ? 'text-green-700' : 'text-gray-600 hover:text-green-700' }}">Vodiči</a>
            <a href="{{ route('docs') }}" class="text-sm font-medium transition {{ $active === 'docs' ? 'text-green-700' : 'text-gray-600 hover:text-green-700' }}">Upute</a>
            <a href="{{ route('about') }}" class="text-sm font-medium transition {{ $active === 'about' ? 'text-green-700' : 'text-gray-600 hover:text-green-700' }}">O nama</a>
            <a href="{{ route('contact') }}" class="text-sm font-medium transition {{ $active === 'contact' ? 'text-green-700' : 'text-gray-600 hover:text-green-700' }}">Kontakt</a>
            @auth
                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-green-700 transition">Moj račun</a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-green-700 transition">Prijava</a>
                <a href="{{ route('register') }}" class="text-sm font-semibold bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition">Registracija</a>
            @endauth
        </nav>

        {{-- Hamburger --}}
        <button @click="open = !open" class="md:hidden p-2 rounded-lg text-gray-600 hover:text-green-700 hover:bg-gray-100 transition">
            <svg x-show="!open" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <svg x-show="open" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Mobile menu --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="md:hidden border-t border-gray-100 bg-white px-6 py-4 space-y-1"
    >
        <a href="{{ route('alati') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ $active === 'alati' ? 'text-green-700 bg-green-50' : 'text-gray-700 hover:bg-gray-50' }}">Alati</a>
        <a href="{{ route('vodici') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ $active === 'vodici' ? 'text-green-700 bg-green-50' : 'text-gray-700 hover:bg-gray-50' }}">Vodiči</a>
        <a href="{{ route('docs') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ $active === 'docs' ? 'text-green-700 bg-green-50' : 'text-gray-700 hover:bg-gray-50' }}">Upute</a>
        <a href="{{ route('about') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ $active === 'about' ? 'text-green-700 bg-green-50' : 'text-gray-700 hover:bg-gray-50' }}">O nama</a>
        <a href="{{ route('contact') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ $active === 'contact' ? 'text-green-700 bg-green-50' : 'text-gray-700 hover:bg-gray-50' }}">Kontakt</a>
        <div class="border-t border-gray-100 my-2"></div>
        @auth
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Moj račun</a>
        @else
            <a href="{{ route('login') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Prijava</a>
            <a href="{{ route('register') }}" class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold bg-green-600 hover:bg-green-700 text-white transition mt-2">Registracija</a>
        @endauth
    </div>
</header>
