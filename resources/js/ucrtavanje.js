// Ucrtavanje parcele bez ARKOD-a na karti (Alpine komponenta). Točke se šalju Livewireu, koji računa površinu.
const NAJMANJI_ZOOM = 14;
const MAX_TOCAKA = 200;
const NAJMANJA_SIGURNOST = 70;   // Esri ocjena pogotka, od 0 do 100

export async function traziEsri(upit, kljuc) {
    const adresa = 'https://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/findAddressCandidates?f=json&maxLocations=5&countryCode=HRV&langCode=hr&outFields=Addr_type&singleLine=' + encodeURIComponent(upit)
        + (kljuc ? '&token=' + encodeURIComponent(kljuc) : '');
    const odgovor = await fetch(adresa);
    const podaci = odgovor.ok ? await odgovor.json() : {};
    if (podaci.error) {
        // npr. istekao ključ: Esri vraća 200 s greškom, pa pretraga prelazi na rezervu
        console.warn('Esri pretraga adrese:', podaci.error.code, podaci.error.message);
    }
    const vidjeno = new Set();

    return (podaci.candidates || [])
        .filter((k) => k.score >= NAJMANJA_SIGURNOST && ! vidjeno.has(k.address) && vidjeno.add(k.address))
        .map((k, i) => {
            const [naziv, ...ostalo] = k.address.split(', ');
            const vrsta = k.attributes?.Addr_type;

            return {
                id: i, naziv, opis: ostalo.join(', '), lat: k.location.y, lng: k.location.x,
                zoom: vrsta === 'PointAddress' ? 18 : (['StreetAddress', 'StreetName'].includes(vrsta) ? 17 : 15),
            };
        });
}

export async function traziOpenMeteo(upit) {
    const adresa = 'https://geocoding-api.open-meteo.com/v1/search?count=5&language=hr&countryCode=HR&name=' + encodeURIComponent(upit);
    const odgovor = await fetch(adresa);
    const podaci = odgovor.ok ? await odgovor.json() : {};

    return (podaci.results || []).map((m) => ({
        id: m.id, naziv: m.name, opis: [m.admin2, m.admin1].filter(Boolean).join(', '), lat: m.latitude, lng: m.longitude, zoom: 15,
    }));
}

export function ucrtavanjeParcele(pocetne, okvir, kljucEsri = null) {
    // Leaflet objekti ostaju izvan Alpineove reaktivnosti
    let karta = null;
    let oblik = null;
    let oznake = null;
    let mojaOznaka = null;
    let nadjenaOznaka = null;
    let odgoda = null;

    return {
        tocke: Array.isArray(pocetne) ? pocetne : [],
        trazi: '',
        rezultati: [],
        trazim: false,
        poruka: '',

        init() {
            karta = window.agroKarta.karta(this.$refs.karta, { doubleClickZoom: false });
            karta.getContainer().style.cursor = 'crosshair';
            oznake = window.L.layerGroup().addTo(karta);
            karta.on('click', (e) => this.dodajSKarte(e.latlng));

            if (this.tocke.length) {
                karta.fitBounds(window.L.latLngBounds(this.tocke), { padding: [30, 30], maxZoom: 18 });
            } else if (okvir) {
                karta.fitBounds(okvir, { padding: [30, 30], maxZoom: 15 });
            } else {
                karta.setView([45.1, 16.4], 7);
            }
            this.nacrtaj();
        },

        dodajSKarte(tocka) {
            if (karta.getZoom() < NAJMANJI_ZOOM) {
                this.poruka = 'Približite kartu do svoje parcele, a onda klikćite njene uglove.';
                return;
            }
            this.dodaj(tocka.lat, tocka.lng);
        },

        dodaj(lat, lng) {
            if (this.tocke.length >= MAX_TOCAKA) {
                this.poruka = 'Parcela može imati najviše ' + MAX_TOCAKA + ' točaka.';
                return;
            }
            this.poruka = '';
            this.tocke.push([Number(lat.toFixed(6)), Number(lng.toFixed(6))]);
            this.promjena();
        },

        ponisti() {
            this.tocke.pop();
            this.promjena();
        },

        obrisi() {
            this.tocke = [];
            this.promjena();
        },

        promjena() {
            this.nacrtaj();
            clearTimeout(odgoda);
            odgoda = setTimeout(() => this.posalji(), 250);
        },

        posalji() {
            const id = this.$el.closest('[wire\\:id]')?.getAttribute('wire:id');
            const komponenta = id && window.Livewire ? window.Livewire.find(id) : null;
            komponenta?.set('rucniPoligon', this.tocke.length >= 3 ? JSON.parse(JSON.stringify(this.tocke)) : null);
        },

        nacrtaj() {
            if (oblik) {
                oblik.remove();
                oblik = null;
            }
            oznake.clearLayers();
            const tocke = JSON.parse(JSON.stringify(this.tocke));
            const izgled = { color: '#86bc25', weight: 3, fillOpacity: 0.25, interactive: false };
            if (tocke.length >= 3) {
                oblik = window.L.polygon(tocke, izgled).addTo(karta);
            } else if (tocke.length === 2) {
                oblik = window.L.polyline(tocke, izgled).addTo(karta);
            }
            tocke.forEach((t) => window.L.circleMarker(t, { radius: 6, color: '#ffffff', weight: 2, fillColor: '#033b2c', fillOpacity: 1, interactive: false }).addTo(oznake));
        },

        // Pretraga adrese i mjesta: Esri nalazi i kućne brojeve, a Open-Meteo je rezerva ako Esri ne odgovori ili ne nađe ništa
        async traziMjesto() {
            const upit = this.trazi.trim();
            this.rezultati = [];
            if (upit.length < 2) {
                return;
            }
            this.trazim = true;
            this.poruka = '';
            const rezultati = await traziEsri(upit, kljucEsri).catch(() => []);
            this.rezultati = rezultati.length ? rezultati : await traziOpenMeteo(upit).catch(() => []);
            if (! this.rezultati.length) {
                this.poruka = 'Adresa nije pronađena. Probajte samo mjesto pa pomaknite kartu.';
            }
            this.trazim = false;
        },

        idiNa(mjesto) {
            this.rezultati = [];
            this.poruka = '';
            karta.setView([mjesto.lat, mjesto.lng], mjesto.zoom);
            if (nadjenaOznaka) {
                nadjenaOznaka.remove();
            }
            nadjenaOznaka = window.L.circleMarker([mjesto.lat, mjesto.lng], { radius: 7, color: '#ffffff', weight: 2, fillColor: '#2a78d6', fillOpacity: 1, interactive: false }).addTo(karta);
        },

        lokacija(uspjeh) {
            if (! navigator.geolocation) {
                this.poruka = 'Ovaj preglednik ne daje lokaciju. Nađite mjesto pretragom.';
                return;
            }
            this.poruka = 'Tražim lokaciju…';
            navigator.geolocation.getCurrentPosition(
                (p) => {
                    this.poruka = '';
                    uspjeh(p.coords);
                },
                () => {
                    this.poruka = 'Lokaciju nije moguće dobiti. Dopustite je u pregledniku ili nađite mjesto pretragom.';
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 },
            );
        },

        mojaLokacija() {
            this.lokacija((k) => {
                karta.setView([k.latitude, k.longitude], 17);
                if (mojaOznaka) {
                    mojaOznaka.remove();
                }
                mojaOznaka = window.L.circleMarker([k.latitude, k.longitude], { radius: 7, color: '#ffffff', weight: 2, fillColor: '#2a78d6', fillOpacity: 1, interactive: false }).addTo(karta);
            });
        },

        // Hodanje po rubu parcele: svaki klik dodaje točku na mjestu gdje korisnik stoji
        tockaGdjeStojim() {
            this.lokacija((k) => {
                this.dodaj(k.latitude, k.longitude);
                karta.setView([k.latitude, k.longitude], Math.max(karta.getZoom(), 17));
            });
        },
    };
}
