{{--
    Traka "Instalirajte aplikaciju" na mobitelu: Android (Chrome) pravi dijalog za instalaciju, iPhone (Safari) upute
    Podijeli → Dodaj na početni zaslon. Ne prikazuje se kad je aplikacija već instalirana; "Ne sada" je skrije na 30 dana.
--}}
<div x-data="{
        vidljivo: false, ios: false, upute: false,
        init() {
            const instalirano = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
            let skriveno = false;
            try { skriveno = Date.now() < Number(localStorage.getItem('agrolog-instalacija-skriveno') || 0) } catch (e) {}
            const mobitel = window.matchMedia('(max-width: 1024px)').matches && ('ontouchstart' in window || navigator.maxTouchPoints > 0);
            if (instalirano || skriveno || ! mobitel) return;
            this.ios = /iphone|ipad|ipod/i.test(navigator.userAgent) && ! /crios|fxios|edgios/i.test(navigator.userAgent);
            if (this.ios) { setTimeout(() => this.vidljivo = true, 2500); return }
            const pokazi = () => { if (window.agroInstalacija) setTimeout(() => this.vidljivo = true, 1500) };
            pokazi(); window.addEventListener('agro-instalacija', pokazi);
            window.addEventListener('appinstalled', () => this.vidljivo = false);
        },
        async instaliraj() {
            if (this.ios) { this.upute = true; return }
            const p = window.agroInstalacija; if (! p) return;
            p.prompt(); await p.userChoice.catch(() => {}); window.agroInstalacija = null; this.vidljivo = false;
        },
        neSada() { this.vidljivo = false; try { localStorage.setItem('agrolog-instalacija-skriveno', Date.now() + 30 * 864e5) } catch (e) {} },
     }"
     x-show="vidljivo" x-cloak x-transition.opacity
     class="fixed inset-x-3 bottom-3 z-[60] sm:left-auto sm:right-4 sm:w-96">
    <div class="rounded-2xl bg-white shadow-2xl ring-1 ring-black/10 p-4">
        <div class="flex items-start gap-3">
            <img src="{{ asset('icon-192.png') }}" alt="" class="w-12 h-12 rounded-xl shrink-0">
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-gray-900">Instalirajte agro-log.app</p>
                <p class="text-sm text-gray-600 leading-snug">Ikona na početnom zaslonu, otvara se kao aplikacija — preko cijelog ekrana.</p>
            </div>
            <button type="button" @click="neSada()" class="-mt-1 -mr-1 w-8 h-8 rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600" aria-label="Zatvori">✕</button>
        </div>

        <ol x-show="upute" x-cloak class="mt-3 space-y-2 text-sm text-gray-700 bg-[#f3f7f1] rounded-xl p-3">
            <li class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-green-700 text-white text-xs font-bold flex items-center justify-center shrink-0">1</span>
                <span>Dodirnite <strong>Podijeli</strong>
                    <svg class="inline w-5 h-5 -mt-1 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0-12l-4 4m4-4l4 4M5 13v6a2 2 0 002 2h10a2 2 0 002-2v-6"/></svg>
                    na dnu Safarija</span>
            </li>
            <li class="flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-green-700 text-white text-xs font-bold flex items-center justify-center shrink-0">2</span>
                <span>Odaberite <strong>Dodaj na početni zaslon</strong> <span class="text-gray-400">(Add to Home Screen)</span></span>
            </li>
        </ol>

        <div class="mt-3 flex justify-end gap-2" x-show="! upute">
            <button type="button" @click="neSada()" class="px-4 py-2 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-100">Ne sada</button>
            <button type="button" @click="instaliraj()" class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-green-700 hover:bg-green-800" x-text="ios ? 'Kako?' : 'Instaliraj'">Instaliraj</button>
        </div>
    </div>
</div>
