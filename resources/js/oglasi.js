// Prikaz oglasa broji se tek kad je barem pola bannera bilo vidljivo cijelu sekundu, jednom po učitavanju stranice.
export function pokreniOglase() {
    const oglasi = document.querySelectorAll('[data-oglas-prikaz]');
    if (! oglasi.length || ! ('IntersectionObserver' in window) || ! navigator.sendBeacon) return;

    const posalji = (el) => {
        const d = new FormData();
        d.append('_token', el.dataset.token);
        navigator.sendBeacon(el.dataset.oglasPrikaz, d);
    };

    oglasi.forEach((el) => {
        let timer = null;
        const o = new IntersectionObserver(([u]) => {
            if (u.isIntersecting && ! document.hidden) {
                timer = timer || setTimeout(() => { o.disconnect(); posalji(el); }, 1000);
            } else {
                clearTimeout(timer);
                timer = null;
            }
        }, { threshold: 0.5 });
        o.observe(el);
    });
}
