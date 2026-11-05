// Pozadina od njiva: mozaik svijetlih parcela (vodoravne ili okomite brazde), traktori polako obrađuju nasumične parcele red po red.
// Koristi se na naslovnici (sekcija "Što sve dobivate") i kao nepomična pozadina aplikacije nakon prijave.
// Opcije: { min, max } broj traktora. Samo dok je vidljivo; bez animacije uz prefers-reduced-motion.
export function poljaTraktori(opcije = {}) {
    const RED = 14, BRZINA = 13;   // razmak redova (px), brzina traktora (px/s)
    const TRAKTOR = '<svg viewBox="0 0 26 18"><rect x="3" y="7" width="14" height="5" rx="1" fill="currentColor"/><rect x="11" y="2" width="6" height="6" rx="1" fill="currentColor" opacity=".85"/><rect x="12" y="3" width="4" height="3" fill="#f3f7f1" opacity=".7"/><rect x="18" y="8" width="5" height="3" rx="1" fill="currentColor"/><circle cx="7" cy="13" r="4.2" fill="#14532d"/><circle cx="7" cy="13" r="1.6" fill="#bbf7d0"/><circle cx="20" cy="14.5" r="2.6" fill="#14532d"/><circle cx="20" cy="14.5" r="1" fill="#bbf7d0"/></svg>';
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
                p.zauzeta = false;
                await pauza(1500 + Math.random() * 3000);
            }
        },
        async obradi(p, v) {
            const duz = p.vert ? p.h : p.w, sir = p.vert ? p.w : p.h, redova = Math.floor((sir - 6) / RED);
            const traktor = document.createElement('span');
            traktor.className = 'traktor'; traktor.innerHTML = TRAKTOR;
            p.el.appendChild(traktor);
            await cekaj(traktor.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 800, fill: 'forwards' }));
            const tragovi = [], trajanje = (duz - 12) / BRZINA * 1000;
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
                await cekaj(traktor.animate([{ transform: poz(a) }, { transform: poz(b) }], { duration: trajanje, easing: 'linear', fill: 'forwards' }));
            }
            await cekaj(traktor.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 800, fill: 'forwards' }));
            traktor.remove();
            // obrađeno ostaje neko vrijeme, pa polako izblijedi
            setTimeout(() => tragovi.forEach(t => cekaj(t.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 6000, fill: 'forwards' })).then(() => t.remove())), 15000 + Math.random() * 20000);
        },
    };
}
