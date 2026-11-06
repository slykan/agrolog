// Web push: pretplata ovog uređaja. Dopuštenje preglednika traži se samo na klik korisnika.
const meta = () => document.querySelector('meta[name="agro-push"]');
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;

const uBajtove = (b64) => {
    const s = atob(b64.replace(/-/g, '+').replace(/_/g, '/') + '='.repeat((4 - (b64.length % 4)) % 4));
    return Uint8Array.from(s, (c) => c.charCodeAt(0));
};

const zahtjev = (metoda, put, podaci) => fetch(put, {
    method: metoda, credentials: 'same-origin', keepalive: true,
    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
    body: JSON.stringify(podaci),
});

const podrzano = () => 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
const ios = () => /iphone|ipad|ipod/i.test(navigator.userAgent);
const instalirano = () => window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;

async function pretplati() {
    const m = meta();
    if (! m) return false;
    const reg = await navigator.serviceWorker.ready;
    const sub = (await reg.pushManager.getSubscription())
        || await reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: uBajtove(m.content) });
    const r = await zahtjev('POST', m.dataset.spremi, sub.toJSON());
    return r.ok;
}

export const agroPush = {
    /** nepodrzano | ios-instaliraj | blokirano | nije-ukljuceno | ukljuceno */
    stanje() {
        if (! podrzano()) return ios() && ! instalirano() ? 'ios-instaliraj' : 'nepodrzano';
        if (Notification.permission === 'denied') return 'blokirano';
        return Notification.permission === 'granted' ? 'ukljuceno' : 'nije-ukljuceno';
    },
    async ukljuci() {
        if (await Notification.requestPermission() !== 'granted') return false;
        return pretplati();
    },
    async proba() {
        const m = meta();
        const r = await zahtjev('POST', m.dataset.proba, {});
        return r.ok ? (await r.json()).uredjaja : 0;
    },
};

// već dopušteno: pretplata se osvježi i pripadne korisniku koji je sada prijavljen (jednom po sesiji preglednika)
function uskladi() {
    const m = meta();
    if (! m || m.dataset.ukljuceno !== '1' || ! podrzano() || Notification.permission !== 'granted') return;
    try {
        if (sessionStorage.getItem('agro-push-' + m.dataset.korisnik)) return;
        sessionStorage.setItem('agro-push-' + m.dataset.korisnik, '1');
    } catch (e) { /* bez pohrane samo se ponovi */ }
    pretplati().catch(() => {});
}

// odjava: uređaj prestaje primati obavijesti, da ih ne vidi sljedeća osoba na istom uređaju
function odjavaUredjaja() {
    document.addEventListener('submit', (e) => {
        const f = e.target;
        if (! f.action?.endsWith('/logout') || f.dataset.pushGotovo || ! podrzano()) return;
        e.preventDefault();
        f.dataset.pushGotovo = '1';
        const posao = navigator.serviceWorker.getRegistration().then((reg) => reg?.pushManager.getSubscription()).then((sub) => {
            if (! sub) return null;
            const m = meta();
            if (m) zahtjev('DELETE', m.dataset.spremi, { endpoint: sub.endpoint }).catch(() => {});
            try { sessionStorage.removeItem('agro-push-' + m?.dataset.korisnik); } catch (err) { /* nije važno */ }
            return sub.unsubscribe();
        }).catch(() => {});
        Promise.race([posao, new Promise((r) => setTimeout(r, 800))]).finally(() => f.submit());
    });
}

export function pokreniPush() {
    window.agroPush = agroPush;
    odjavaUredjaja();
    uskladi();
}
