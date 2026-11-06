@php
    $jaStvarni = request()->attributes->get('savjetnik') ?? Auth::user();
    $uKlijentu = request()->attributes->has('savjetnik');
    $brojKlijenata = \App\Models\Pristup::aktivni()->where('savjetnik_id', $jaStvarni->id)->count();
@endphp
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 2xl:space-x-8 xl:-my-px xl:ms-8 2xl:ms-10 xl:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Početna
                    </x-nav-link>
                    <x-nav-link :href="route('parcele')" :active="request()->routeIs('parcele')">
                        Parcele &amp; Kulture
                    </x-nav-link>
                    {{-- Aktivnosti: podizbornik s gnojidbom i prskanjem --}}
                    <div class="relative inline-flex" x-data="{ akt: false }" @click.outside="akt = false" @keydown.escape.window="akt = false">
                        <button type="button" @click="akt = ! akt" :aria-expanded="akt"
                                class="inline-flex items-center gap-1 px-1 pt-1 border-b-2 text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out {{ request()->routeIs('gnojidba') || request()->routeIs('prskanje') || request()->routeIs('berba') ? 'border-green-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Aktivnosti
                            <svg class="h-4 w-4 transition" :class="akt && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>
                        <div x-show="akt" x-cloak x-transition.origin.top.left
                             class="absolute left-0 top-full z-50 mt-1 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black/5">
                            <a href="{{ route('gnojidba') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('gnojidba') ? 'bg-green-50 text-green-800 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">Gnojidba</a>
                            <a href="{{ route('prskanje') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('prskanje') ? 'bg-green-50 text-green-800 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">Prskanje</a>
                            <a href="{{ route('berba') }}" class="block px-4 py-2 text-sm {{ request()->routeIs('berba') ? 'bg-green-50 text-green-800 font-medium' : 'text-gray-700 hover:bg-gray-100' }}">Berba i žetva</a>
                        </div>
                    </div>
                    <x-nav-link :href="route('skladiste')" :active="request()->routeIs('skladiste')">
                        Skladište
                    </x-nav-link>
                    <x-nav-link :href="route('mehanizacija')" :active="request()->routeIs('mehanizacija')">
                        Mehanizacija
                    </x-nav-link>
                    <x-nav-link :href="route('troskovi')" :active="request()->routeIs('troskovi')">
                        Troškovi
                    </x-nav-link>
                    <x-nav-link :href="route('izvjestaji')" :active="request()->routeIs('izvjestaji*')">
                        Izvještaji
                    </x-nav-link>
                </div>
            </div>

            @include('partials.zvono')

            <!-- Settings Dropdown -->
            <div class="hidden xl:flex xl:items-center xl:ms-2">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ $jaStvarni->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @unless($uKlijentu)
                        <x-dropdown-link :href="route('profile.edit')">
                            Profil
                        </x-dropdown-link>
                        @endunless
                        @if($brojKlijenata)
                        <x-dropdown-link :href="route('klijenti')">
                            Klijenti ({{ $brojKlijenata }})
                        </x-dropdown-link>
                        @endif
                        <x-dropdown-link :href="route('docs')">
                            Upute
                        </x-dropdown-link>

                        @can('admin')
                            <x-dropdown-link :href="route('admin.korisnici')">
                                Admin
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.obavijesti')">
                                Obavijesti korisnicima
                            </x-dropdown-link>
                        @endcan

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                Odjava
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 ms-1 flex items-center xl:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden xl:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Početna</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('parcele')" :active="request()->routeIs('parcele')">Parcele &amp; Kulture</x-responsive-nav-link>
            <div class="ps-4 pt-2 pb-1 text-xs font-semibold uppercase tracking-wide text-gray-400">Aktivnosti</div>
            <x-responsive-nav-link :href="route('gnojidba')" :active="request()->routeIs('gnojidba')" class="ps-7">Gnojidba</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('prskanje')" :active="request()->routeIs('prskanje')" class="ps-7">Prskanje</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('berba')" :active="request()->routeIs('berba')" class="ps-7">Berba i žetva</x-responsive-nav-link>
            <div class="border-t border-gray-100 my-1"></div>
            <x-responsive-nav-link :href="route('skladiste')" :active="request()->routeIs('skladiste')">Skladište</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('mehanizacija')" :active="request()->routeIs('mehanizacija')">Mehanizacija</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('troskovi')" :active="request()->routeIs('troskovi')">Troškovi</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('izvjestaji')" :active="request()->routeIs('izvjestaji*')">Izvještaji</x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ $jaStvarni->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ $jaStvarni->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                @unless($uKlijentu)
                <x-responsive-nav-link :href="route('profile.edit')">
                    Profil
                </x-responsive-nav-link>
                @endunless
                @if($brojKlijenata)
                <x-responsive-nav-link :href="route('klijenti')">
                    Klijenti ({{ $brojKlijenata }})
                </x-responsive-nav-link>
                @endif
                <x-responsive-nav-link :href="route('docs')">
                    Upute
                </x-responsive-nav-link>

                @can('admin')
                    <x-responsive-nav-link :href="route('admin.korisnici')" :active="request()->routeIs('admin.korisni*')">
                        Admin
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.obavijesti')" :active="request()->routeIs('admin.obavijesti*')">
                        Obavijesti korisnicima
                    </x-responsive-nav-link>
                @endcan

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        Odjava
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
