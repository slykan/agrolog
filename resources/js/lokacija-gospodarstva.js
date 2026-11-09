// Lokacija gospodarstva u profilu (Alpine). Pretraga adrese samo približi kartu (Esri ključ je "Geocode, not stored":
// rezultat pretrage se ne sprema). Sprema se pin koji korisnik sam postavi klikom ili povlačenjem.
import { traziEsri, traziOpenMeteo } from './ucrtavanje';

export function lokacijaGospodarstva(pocetna, centar, kljucEsri = null) {
    let karta = null;
    let pin = null;

    return {
        lat: pocetna?.[0] ?? null,
        lng: pocetna?.[1] ?? null,
        trazi: '',
        rezultati: [],
        trazim: false,
        poruka: '',

        // karta se stvara tek kad je sekcija vidljiva (Leaflet u skrivenom elementu krivo izmjeri veličinu)
        pokreni() {
            if (karta) {
                setTimeout(() => karta.invalidateSize(), 50);
                return;
            }
            karta = window.agroKarta.karta(this.$refs.karta, { doubleClickZoom: false });
            karta.getContainer().style.cursor = 'crosshair';
            if (this.lat !== null) {
                karta.setView([this.lat, this.lng], 18);
                this.postaviPin(this.lat, this.lng);
            } else if (centar) {
                karta.setView(centar, 13);
            } else {
                karta.setView([45.1, 16.4], 7);
            }
            karta.on('click', (e) => this.postaviPin(e.latlng.lat, e.latlng.lng));
        },

        postaviPin(lat, lng) {
            this.lat = Math.round(lat * 1e7) / 1e7;
            this.lng = Math.round(lng * 1e7) / 1e7;
            if (! pin) {
                pin = window.L.marker([this.lat, this.lng], { draggable: true, autoPan: true }).addTo(karta);
                pin.on('dragend', () => { const p = pin.getLatLng(); this.postaviPin(p.lat, p.lng); });
            } else {
                pin.setLatLng([this.lat, this.lng]);
            }
            this.poruka = '';
        },

        async traziAdresu() {
            const upit = this.trazi.trim();
            this.rezultati = [];
            if (upit.length < 3) {
                return;
            }
            this.trazim = true;
            const nadjeno = await traziEsri(upit, kljucEsri).catch(() => []);
            this.rezultati = nadjeno.length ? nadjeno : await traziOpenMeteo(upit).catch(() => []);
            this.trazim = false;
            this.poruka = this.rezultati.length ? '' : 'Ništa nije pronađeno. Probajte samo mjesto pa pronađite kuću na karti.';
            if (this.rezultati.length === 1) {
                this.idiNa(this.rezultati[0]);
            }
        },

        // samo pomakne kartu; pin postavlja korisnik
        idiNa(r) {
            this.rezultati = [];
            karta.setView([r.lat, r.lng], Math.max(r.zoom, 17));
            this.poruka = 'Sada kliknite točno na svoje dvorište ili mjesto prodaje.';
        },
    };
}
