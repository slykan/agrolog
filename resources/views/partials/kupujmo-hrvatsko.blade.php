{{-- Slogan Tržnice i Gospodarstava. Vlastita značka (ne službeni znak "Izvorno hrvatsko" HGK-a, koji smiju nositi samo certificirani proizvodi). --}}
<div class="relative overflow-hidden rounded-3xl bg-[#022a20] text-white px-6 py-7 sm:px-8 {{ $klasa ?? 'mb-8' }}">
    <div class="absolute -right-10 -top-12 h-48 w-48 rounded-full bg-[#86bc25]/20 blur-3xl" aria-hidden="true"></div>
    <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center">
        <div class="shrink-0 flex h-20 w-20 flex-col items-center justify-center rounded-full border-2 border-[#86bc25] text-center leading-none" aria-hidden="true">
            <span class="text-[10px] font-bold uppercase tracking-widest text-[#86bc25]">Domaće</span>
            <svg class="my-1 h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4-3-7-6.5-7-10.5A4.5 4.5 0 0112 7a4.5 4.5 0 017 3.5C19 14.5 16 18 12 21z"/></svg>
            <span class="text-[9px] font-semibold uppercase tracking-wider text-white/70">lokalno</span>
        </div>
        <div>
            <p class="text-2xl sm:text-3xl font-extrabold tracking-tight">Kupujmo hrvatsko</p>
            <p class="mt-1 text-lg text-[#86bc25] font-semibold">Podržimo domaće. Uživajmo lokalno. Vjerujmo u naše ljude.</p>
            @if($tekst ?? null)<p class="mt-2 text-sm text-white/80 max-w-2xl">{{ $tekst }}</p>@endif
        </div>
    </div>
</div>
