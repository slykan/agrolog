{{-- Kontakt gospodarstva: telefon tek na klik (zaseban endpoint), ostalo iz prezentacije. Treba $p (Prezentacija); po želji $naslov. --}}
<div class="rounded-2xl border border-gray-200 p-5 space-y-4" x-data="{ k: null, greska: false, ucitava: false,
        async prikazi() { this.ucitava = true; try { const r = await fetch(@js(route('gospodarstvo.telefon', $p->slug)), { headers: { Accept: 'application/json' } }); if (! r.ok) throw 0; this.k = await r.json(); } catch (e) { this.greska = true; } this.ucitava = false; } }">
    <h2 class="font-bold text-gray-900">{{ $naslov ?? 'Kontakt' }}</h2>
    <div>
        <button type="button" x-show="! k" @click="prikazi()" :disabled="ucitava" class="btn-primary w-full">📞 Prikaži broj</button>
        <template x-if="k">
            <div class="space-y-2">
                <a :href="'tel:' + k.tel" class="block text-xl font-bold text-gray-900 hover:text-green-800" x-text="k.telefon"></a>
                <div class="flex gap-2">
                    <a x-show="k.whatsapp" :href="k.whatsapp" target="_blank" rel="noopener" class="flex-1 rounded-lg bg-[#25D366] px-3 py-2 text-center text-sm font-semibold text-white">WhatsApp</a>
                    <a x-show="k.viber" :href="k.viber" class="flex-1 rounded-lg bg-[#7360F2] px-3 py-2 text-center text-sm font-semibold text-white">Viber</a>
                </div>
            </div>
        </template>
        <p x-show="greska" x-cloak class="mt-2 text-sm text-red-700">Broj se trenutno ne može prikazati. Pokušajte za minutu.</p>
    </div>
    @if($p->email)
        <p class="text-sm"><span class="text-gray-500">E-mail:</span> <a href="mailto:{{ $p->email }}" class="text-green-700 underline break-all">{{ $p->email }}</a></p>
    @endif
    @if($p->adresa)
        <p class="text-sm"><span class="text-gray-500">Adresa:</span> {{ $p->adresa }}</p>
    @endif
    @if($p->kucni_prag)
        <div class="rounded-lg bg-green-50 px-3 py-2 text-sm text-green-900">
            <strong>Prodaja na kućnom pragu</strong>
            @if($p->radno_vrijeme)<span class="block">{{ $p->radno_vrijeme }}</span>@endif
        </div>
    @endif
    @php $veze = array_filter(['Web' => $p->web, 'Facebook' => $p->facebook, 'Instagram' => $p->instagram]); @endphp
    @if($veze)
        <div class="flex flex-wrap gap-3 text-sm">
            @foreach($veze as $naziv => $url)
                <a href="{{ $url }}" target="_blank" rel="noopener nofollow ugc" class="text-green-700 underline">{{ $naziv }}</a>
            @endforeach
        </div>
    @endif
</div>
