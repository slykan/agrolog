{{-- Zvono s obavijestima (rokovi, evidencija 2027., skladište). Brojka nestane kad se popis otvori; vrati se kad se popis promijeni. --}}
@php
    $upozorenja = \App\Support\Upozorenja::za(auth()->user());
    $potpis = md5($upozorenja->pluck('kljuc')->implode('|'));
@endphp
<div class="relative flex items-center ms-auto xl:ms-4"
     x-data="{
         otvoreno: false,
         novo: false,
         init() { try { this.novo = {{ $upozorenja->count() }} > 0 && localStorage.getItem('agrolog-zvono') !== '{{ $potpis }}' } catch (e) { this.novo = {{ $upozorenja->count() }} > 0 } },
         prebaci() { this.otvoreno = ! this.otvoreno; if (this.otvoreno) { this.novo = false; try { localStorage.setItem('agrolog-zvono', '{{ $potpis }}') } catch (e) {} } },
     }"
     @click.outside="otvoreno = false" @keydown.escape.window="otvoreno = false">
    <button type="button" @click="prebaci()" :aria-expanded="otvoreno" aria-label="Obavijesti ({{ $upozorenja->count() }})"
            class="relative inline-flex items-center justify-center w-10 h-10 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none transition">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
        </svg>
        @if($upozorenja->isNotEmpty())
            <span x-show="novo" x-cloak
                  class="absolute -top-0.5 -right-0.5 min-w-[1.25rem] h-5 px-1 rounded-full bg-red-600 text-white text-[11px] font-bold leading-5 text-center ring-2 ring-white">{{ $upozorenja->count() > 9 ? '9+' : $upozorenja->count() }}</span>
        @endif
    </button>

    <div x-show="otvoreno" x-cloak x-transition.origin.top.right
         class="absolute right-0 top-full mt-2 z-50 w-80 max-w-[calc(100vw-1.5rem)] rounded-xl bg-white shadow-xl ring-1 ring-black/5 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
            <span class="font-semibold text-gray-800">Obavijesti</span>
            @if($upozorenja->isNotEmpty())<span class="text-xs text-gray-500">{{ $upozorenja->count() }}</span>@endif
        </div>
        @if($upozorenja->isEmpty())
            <p class="px-4 py-6 text-sm text-gray-500 text-center">✓ Nema ništa što traži pažnju.</p>
        @else
            <ul class="max-h-96 overflow-y-auto divide-y divide-gray-100">
                @foreach($upozorenja as $u)
                    <li class="px-4 py-3 flex gap-3">
                        <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ $u['razina'] === 'crveno' ? 'bg-red-600' : 'bg-amber-500' }}" aria-hidden="true"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 line-clamp-2">{{ $u['naslov'] }}</p>
                            <p class="text-xs {{ $u['razina'] === 'crveno' ? 'text-red-700' : 'text-amber-800' }}">{{ $u['tekst'] }}</p>
                        </div>
                        <a href="{{ $u['link'] }}" class="self-center shrink-0 text-xs font-semibold text-green-700 hover:text-green-800 whitespace-nowrap">Više →</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
