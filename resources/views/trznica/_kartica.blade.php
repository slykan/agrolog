{{-- Kartica proizvoda na Tržnici. Treba $pr (Proizvod s učitanom prezentacijom); $bezProdavaca skriva red s gospodarstvom. --}}
<a href="{{ route('trznica.proizvod', $pr) }}" class="group block overflow-hidden rounded-2xl border border-gray-200 bg-white hover:border-green-400 hover:shadow-md transition">
    @if($pr->slika)
        <img src="{{ $pr->slikaUrl() }}" alt="{{ $pr->naziv }}" loading="lazy" class="h-40 w-full object-cover">
    @else
        <div class="h-40 bg-gradient-to-br from-green-50 to-green-100 flex items-center justify-center text-green-800/60 text-sm">{{ $pr->vrsta }}</div>
    @endif
    <div class="p-4">
        <h3 class="font-bold text-gray-900 group-hover:text-green-800 line-clamp-2">{{ $pr->naziv }}</h3>
        <p class="mt-1 text-lg font-extrabold text-green-800">{{ $pr->cijenaTekst() }}</p>
        @unless($bezProdavaca ?? false)
            <p class="mt-1 text-sm text-gray-500 truncate">{{ $pr->prezentacija->naziv }} · {{ $pr->prezentacija->mjesto }}</p>
        @endunless
    </div>
</a>
