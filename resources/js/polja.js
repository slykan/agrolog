// Pozadina od njiva: mozaik svijetlih parcela (vodoravne ili okomite brazde), traktori polako obrađuju nasumične parcele red po red.
// Koristi se na naslovnici (sekcija "Što sve dobivate") i kao nepomična pozadina aplikacije nakon prijave.
// Opcije: { min, max } broj traktora. Samo dok je vidljivo; bez animacije uz prefers-reduced-motion.
export function poljaTraktori(opcije = {}) {
    const RED = 14, BRZINA = 13;   // razmak redova (px), brzina traktora (px/s)
    const TRAKTOR = '<svg viewBox="0 0 26 18"><rect x="3" y="7" width="14" height="5" rx="1" fill="currentColor"/><rect x="11" y="2" width="6" height="6" rx="1" fill="currentColor" opacity=".85"/><rect x="12" y="3" width="4" height="3" fill="#f3f7f1" opacity=".7"/><rect x="18" y="8" width="5" height="3" rx="1" fill="currentColor"/><circle cx="7" cy="13" r="4.2" fill="#14532d"/><circle cx="7" cy="13" r="1.6" fill="#bbf7d0"/><circle cx="20" cy="14.5" r="2.6" fill="#14532d"/><circle cx="20" cy="14.5" r="1" fill="#bbf7d0"/></svg>';
    // kulture koje izrastu iza traktora (nasumično po parceli)
    const KULTURE = {
        klice: '<svg viewBox="0 0 12 16"><path d="M6 16V9" stroke="#16a34a" stroke-width="1.2"/><path d="M6 10C3 10 1.5 8 1.5 6c2.5 0 4.5 1.5 4.5 4zM6 9c0-2.5 2-4 4.5-4C10.5 7 9 9 6 9z" fill="#4ade80"/></svg>',
        psenica: '<svg viewBox="0 0 12 16"><path d="M6 16V5" stroke="#65a30d" stroke-width="1"/><path d="M6 13c-2-.5-3-2-3-3.5M6 12c2-.5 3-2 3-3.5" stroke="#84cc16" stroke-width=".9" fill="none"/><ellipse cx="6" cy="3.6" rx="1.7" ry="3.4" fill="#eab308" opacity=".85"/><path d="M6 .2v1.2" stroke="#ca8a04" stroke-width=".7"/></svg>',
        kukuruz: '<svg viewBox="0 0 12 16"><path d="M6 16V2" stroke="#15803d" stroke-width="1.3"/><path d="M6 12C3 11 1.5 9 1 7M6 9c3-1 4.5-3 5-5M6 6C4 5 3 3.5 3 2" stroke="#22c55e" stroke-width="1.2" fill="none" stroke-linecap="round"/><rect x="6.4" y="8.5" width="2" height="4" rx="1" fill="#facc15" opacity=".9"/></svg>',
        suncokret: '<svg viewBox="0 0 12 16"><path d="M6 16V6" stroke="#16a34a" stroke-width="1.2"/><path d="M6 12c-2 0-3.5-1-4-2.5 2 0 3.5.8 4 2.5zM6 10c2 0 3.5-1 4-2.5-2 0-3.5.8-4 2.5z" fill="#4ade80"/><circle cx="6" cy="4" r="3.4" fill="#facc15"/><circle cx="6" cy="4" r="1.5" fill="#a16207"/></svg>',
        soja: '<svg viewBox="0 0 12 16"><path d="M6 16V6" stroke="#16a34a" stroke-width="1"/><circle cx="3.6" cy="8" r="2.3" fill="#4ade80"/><circle cx="8.4" cy="7" r="2.3" fill="#22c55e"/><circle cx="6" cy="4.2" r="2.3" fill="#86efac"/></svg>',
    };
    const pauza = ms => new Promise(r => setTimeout(r, ms));
    const cekaj = a => a.finished.catch(() => {});
    return {
        parcele: [], radi: false, radnika: 0, broj: 3, verzija: 0,
        init() {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || ! this.$el.animate) return;
            this.slozi();
            let t; window.addEventListener('resize', () => { clearTimeout(t); t = setTimeout(() => this.slozi(), 500) });
            new IntersectionObserver(([u]) => u.isIntersecting ? this.nastavi() : this.pauziraj(), { threshold: 0.05 }).observe(this.$el);
            document.addEventListener('visibilitychange', () => document.hidden ? this.pauziraj() : this.nastavi());
        },
        // mozaik parcela preko cijele sekcije
        slozi() {
            this.verzija++; this.radnika = 0; this.$el.innerHTML = ''; this.parcele = [];
            const w = this.$el.clientWidth, h = this.$el.clientHeight, o = this.$el.getBoundingClientRect();
            const rel = el => { if (! el) return null; const r = el.getBoundingClientRect(); return { l: r.left - o.left, t: r.top - o.top, r: r.right - o.left, b: r.bottom - o.top } };
            const zag = rel(this.$el.parentElement.querySelector('[data-zaglavlje]')), kar = rel(this.$el.parentElement.querySelector('[data-kartice]'));
            for (let x = 0; x < w - 20;) {
                const cw = Math.min(w - x, 150 + Math.random() * 200);
                for (let y = 0; y < h - 20;) {
                    const ch = Math.min(h - y, 80 + Math.random() * 120), vert = Math.random() < 0.3;
                    const el = document.createElement('div');
                    el.className = 'polje' + (vert ? ' polje-v' : '');
                    Object.assign(el.style, { left: (x + 3) + 'px', top: (y + 3) + 'px', width: (cw - 6) + 'px', height: (ch - 6) + 'px' });
                    el.style.setProperty('--t', (0.025 + Math.random() * 0.03).toFixed(3));
                    this.$el.appendChild(el);
                    const sijece = a => a && x < a.r && x + cw > a.l && y < a.b && y + ch > a.t;
                    const skrivena = kar && x > kar.l && x + cw < kar.r && y > kar.t && y + ch < kar.b;
                    // iza naslova se ne radi; potpuno iza kartica rijetko (ne vidi se)
                    this.parcele.push({ el, w: cw - 6, h: ch - 6, vert, zauzeta: sijece(zag), skrivena });
                    y += ch;
                }
                x += cw;
            }
            this.broj = Math.max(opcije.min ?? 3, Math.min(opcije.max ?? 7, Math.round(w * h / 260000)));
            if (this.radi) this.pokreni();
        },
        nastavi() { if (document.hidden) return; this.radi = true; this.$el.getAnimations({ subtree: true }).forEach(a => a.play()); this.pokreni() },
        pauziraj() { this.radi = false; this.$el.getAnimations({ subtree: true }).forEach(a => a.pause()) },
        pokreni() { while (this.radnika < this.broj) { this.radnika++; this.radnik(this.verzija) } },
        async radnik(v) {
            await pauza(Math.random() * 5000);
            while (v === this.verzija) {
                if (! this.radi) { await pauza(1000); continue }
                const slobodne = this.parcele.filter(p => ! p.zauzeta && p.w > 70 && p.h > 45 && (! p.skrivena || Math.random() < 0.1));
                if (! slobodne.length) { await pauza(2000); continue }
                const p = slobodne[Math.floor(Math.random() * slobodne.length)];
                p.zauzeta = true;
                await this.obradi(p, v);
                await pauza(1500 + Math.random() * 3000);
            }
        },
        async obradi(p, v) {
            const duz = p.vert ? p.h : p.w, sir = p.vert ? p.w : p.h, redova = Math.floor((sir - 6) / RED);
            const traktor = document.createElement('span');
            traktor.className = 'traktor'; traktor.innerHTML = TRAKTOR;
            p.el.appendChild(traktor);
            await cekaj(traktor.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 800, fill: 'forwards' }));
            const tragovi = [], biljke = [], trajanje = (duz - 12) / BRZINA * 1000;
            const vrste = Object.keys(KULTURE), kultura = vrste[Math.floor(Math.random() * vrste.length)];
            for (let r = 0; r < redova && v === this.verzija; r++) {
                const pos = 3 + r * RED + RED / 2, smjer = r % 2 === 0 ? 1 : -1;
                const [a, b] = smjer > 0 ? [6, duz - 6] : [duz - 6, 6];
                const trag = document.createElement('span');
                trag.className = 'polje-trag';
                if (p.vert) Object.assign(trag.style, { left: (pos - 4) + 'px', width: '8px', [smjer > 0 ? 'top' : 'bottom']: '6px' });
                else Object.assign(trag.style, { top: (pos - 4) + 'px', height: '8px', [smjer > 0 ? 'left' : 'right']: '6px' });
                p.el.appendChild(trag); tragovi.push(trag);
                trag.animate(p.vert ? [{ height: '0px' }, { height: (duz - 12) + 'px' }] : [{ width: '0px' }, { width: (duz - 12) + 'px' }], { duration: trajanje, easing: 'linear', fill: 'forwards' });
                const poz = s => p.vert ? `translate(${pos}px, ${s}px) rotate(90deg) scaleX(${smjer})` : `translate(${s}px, ${pos}px) scaleX(${smjer})`;
                if (r % 2 === 0 || redova <= 4) this.posadi(p, kultura, pos, a, b, biljke);
                await cekaj(traktor.animate([{ transform: poz(a) }, { transform: poz(b) }], { duration: trajanje, easing: 'linear', fill: 'forwards' }));
            }
            await cekaj(traktor.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 800, fill: 'forwards' }));
            traktor.remove();
            this.ocisti(p, tragovi.concat(biljke));
        },
        // biljke duž reda: svaka nikne čim traktor prođe preko nje (kašnjenje = put do nje / brzina), pa naraste
        posadi(p, kultura, pos, a, b, biljke) {
            const od = Math.min(a, b) + 8, do_ = Math.max(a, b) - 8, korak = 20;
            const mjesta = []; for (let s = od; s <= do_; s += korak) mjesta.push(s + (Math.random() * 6 - 3));
            if (a > b) mjesta.reverse();
            mjesta.forEach((s) => {
                const el = document.createElement('span');
                el.className = 'biljka'; el.innerHTML = KULTURE[kultura];
                Object.assign(el.style, p.vert ? { left: pos + 'px', top: s + 'px' } : { left: s + 'px', top: pos + 'px' });
                p.el.appendChild(el); biljke.push(el);
                el.animate([{ transform: 'scale(.2, 0)', opacity: 0 }, { transform: 'scale(.6, .5)', opacity: 1, offset: .35 }, { transform: 'scale(1, 1)', opacity: 1 }],
                    { duration: 4000 + Math.random() * 2500, delay: Math.abs(s - a) / BRZINA * 1000 + 250, easing: 'ease-out', fill: 'both' });
            });
        },
        // obrađeno i izraslo ostaje neko vrijeme, pa polako izblijedi; tek tada se parcela opet može obrađivati
        async ocisti(p, elementi) {
            await pauza(25000 + Math.random() * 25000);
            await Promise.all(elementi.map(el => cekaj(el.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 6000, fill: 'forwards' }))));
            elementi.forEach(el => el.remove());
            p.zauzeta = false;
        },
    };
}
