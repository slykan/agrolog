{{-- Oglas za mjesto (App\Support\Oglasi). Prikaz broji preglednik kad je oglas stvarno na ekranu, klik broji server. Ne pokazuje se savjetniku u tuđem gospodarstvu. --}}
@php $oglas = request()->attributes->get('pristup') ? null : \App\Support\Oglasi::za($mjesto); @endphp
@if($oglas)
    @if(! empty($omotac))<div class="{{ $omotac }}">@endif
    <aside aria-label="Oglas" class="no-print {{ $klasa ?? '' }}"
           data-oglas-prikaz="{{ route('oglas.prikaz', [$oglas->id, $mjesto]) }}" data-token="{{ csrf_token() }}">
        <a href="{{ route('oglas.klik', [$oglas->id, $mjesto]) }}" rel="sponsored nofollow" @if(str_starts_with($oglas->url, 'http')) target="_blank" @endif
           class="relative block overflow-hidden rounded-lg shadow-sm transition hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[#86bc25] focus-visible:ring-offset-2">
            <picture>
                @if($oglas->slika_mobitel)
                    <source media="(max-width: 639px)" srcset="{{ asset($oglas->slika_mobitel) }}" width="{{ $oglas->mobitel_w }}" height="{{ $oglas->mobitel_h }}">
                @endif
                <img src="{{ asset($oglas->slika) }}" alt="Oglas: {{ $oglas->naziv }}" width="{{ $oglas->slika_w }}" height="{{ $oglas->slika_h }}" class="block w-full h-auto">
            </picture>
            <span class="absolute left-2 top-1.5 rounded bg-black/40 px-1.5 py-px text-[10px] font-medium tracking-wide text-white/90 backdrop-blur-sm">Oglas</span>
        </a>
    </aside>
    @if(! empty($omotac))</div>@endif
@endif
