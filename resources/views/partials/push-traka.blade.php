{{-- Traka u aplikaciji: dopuštenje za push na ovom uređaju traži se tek na klik. "Ne sada" je skrije na 30 dana. --}}
<div x-data="{
        vidljivo: false,
        init() {
            let skriveno = false;
            try { skriveno = Date.now() < Number(localStorage.getItem('agrolog-push-skriveno') || 0) } catch (e) {}
            this.vidljivo = ! skriveno && window.agroPush?.stanje() === 'nije-ukljuceno';
        },
        async ukljuci() { this.vidljivo = false; await window.agroPush.ukljuci().catch(() => false) },
        neSada() { this.vidljivo = false; try { localStorage.setItem('agrolog-push-skriveno', Date.now() + 30 * 864e5) } catch (e) {} },
     }" x-show="vidljivo" x-cloak class="no-print bg-green-50 border-b border-green-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between text-sm">
        <p class="text-gray-800">Uključite obavijesti na ovom uređaju: rokovi, karence i skladište.</p>
        <div class="flex gap-2 shrink-0">
            <button type="button" @click="ukljuci()" class="btn-xs-green">Uključi</button>
            <button type="button" @click="neSada()" class="btn-xs-gray">Ne sada</button>
        </div>
    </div>
</div>
