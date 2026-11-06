// Ucrtavanje parcele bez ARKOD-a na karti (Alpine komponenta). Točke se šalju Livewireu, koji računa površinu.
const NAJMANJI_ZOOM = 14;
const MAX_TOCAKA = 200;

export function ucrtavanjeParcele(pocetne, okvir) {
    // Leaflet objekti ostaju izvan Alpineove reaktivnosti
    let karta = null;
    let oblik = null;
    let oznake = null;
    let mojaOznaka = null;
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

        // Pretraga mjesta preko Open-Meteo geokodiranja, koje aplikacija već koristi za prognozu
        async traziMjesto() {
            const upit = this.trazi.trim();
            this.rezultati = [];
            if (upit.length < 2) {
                return;
            }
            this.trazim = true;
            this.poruka = '';
            try {
                const adresa = 'https://geocoding-api.open-meteo.com/v1/search?count=5&language=hr&countryCode=HR&name=' + encodeURIComponent(upit);
                const odgovor = await fetch(adresa);
                const podaci = odgovor.ok ? await odgovor.json() : {};
                this.rezultati = podaci.results || [];
                if (! this.rezultati.length) {
                    this.poruka = 'Mjesto nije pronađeno. Probajte obližnje veće mjesto pa pomaknite kartu.';
                }
            } catch (e) {
                this.poruka = 'Pretraga trenutno ne radi. Pomaknite kartu rukom.';
            }
            this.trazim = false;
        },

        idiNa(mjesto) {
            this.rezultati = [];
            this.poruka = '';
            karta.setView([mjesto.latitude, mjesto.longitude], 15);
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
