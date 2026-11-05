// agro-log.app service worker: aplikacija se može instalirati, a bez interneta se pokaže stranica "Nema veze".
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
