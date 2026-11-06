// agro-log.app service worker: aplikacija se može instalirati, bez interneta se pokaže stranica "Nema veze", a stižu i push obavijesti.
// Ništa drugo se ne sprema — evidencija uvijek dolazi svježa sa servera.
const CACHE = 'agrolog-v1';
const OFFLINE = '/offline.html';

self.addEventListener('install', (e) => {
    e.waitUntil(caches.open(CACHE).then((c) => c.addAll([OFFLINE, '/icon-192.png', '/images/logo/agro-log.png'])).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (e) => {
    e.waitUntil(caches.keys().then((k) => Promise.all(k.filter((n) => n !== CACHE).map((n) => caches.delete(n)))).then(() => self.clients.claim()));
});

self.addEventListener('fetch', (e) => {
    // samo otvaranje stranica (navigacija); sve ostalo ide izravno na mrežu
    if (e.request.mode !== 'navigate') return;
    e.respondWith(fetch(e.request).catch(() => caches.match(OFFLINE)));
});

// push obavijest: naslov, tekst i poveznica sa servera
self.addEventListener('push', (e) => {
    let d = {};
    try { d = e.data ? e.data.json() : {}; } catch (err) { /* neispravan sadržaj: ipak pokaži obavijest */ }
    e.waitUntil(self.registration.showNotification(d.naslov || 'AgroLog', {
        body: d.tekst || '', icon: '/icon-192.png', badge: '/icon-192.png', data: { url: d.url || '/dashboard' }, lang: 'hr',
    }));
});

// klik: otvara stranicu, samo unutar agro-log.app
self.addEventListener('notificationclick', (e) => {
    e.notification.close();
    let cilj = new URL('/dashboard', self.location.origin).href;
    try {
        const u = new URL(e.notification.data?.url || '', self.location.origin);
        if (u.origin === self.location.origin) cilj = u.href;
    } catch (err) { /* ostaje početna */ }
    e.waitUntil(self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((prozori) => {
        const otvoren = prozori.find((p) => p.url === cilj);
        return otvoren ? otvoren.focus() : self.clients.openWindow(cilj);
    }));
});
