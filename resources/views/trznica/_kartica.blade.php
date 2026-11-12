{{-- Kartica proizvoda na Tržnici. Treba $pr (Proizvod s učitanom prezentacijom); $bezProdavaca skriva red s gospodarstvom. --}}
<a href="{{ route('trznica.proizvod', $pr) }}" class="group block h-full overflow-hidden rounded-2xl border border-gray-200 bg-white hover:border-green-400 hover:shadow-md transition">
    @if($pr->slika)
        <img src="{{ $pr->slikaUrl() }}" alt="{{ $pr->naziv }}" loading="lazy" class="h-40 w-full object-cover">
    @else
        @php [$ikona, $boje] = $pr->izgled(); @endphp
        <div class="relative h-40 flex items-end p-3" style="background: linear-gradient(135deg, {{ $boje }})">
            <span class="absolute inset-0 flex items-center justify-center text-6xl drop-shadow-sm transition group-hover:scale-110" aria-hidden="true">{{ $ikona }}</span>
            <span class="relative rounded-full bg-white/90 px-2.5 py-0.5 text-xs font-semibold text-gray-800">{{ $pr->vrsta }}</span>
        </div>
    @endif
    <div class="p-4">
        <h3 class="font-bold text-gray-900 group-hover:text-green-800 line-clamp-2">{{ $pr->naziv }}</h3>
        <p class="mt-1 text-lg font-extrabold text-green-800">{{ $pr->cijenaTekst() }}</p>
        @unless($bezProdavaca ?? false)
            <p class="mt-1 text-sm text-gray-500 truncate">{{ $pr->prezentacija->naziv }} · {{ $pr->prezentacija->mjesto }}</p>
        @endunless
    </div>
</a>
