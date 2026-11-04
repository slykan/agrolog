{{-- Leaflet + pomoćne funkcije za karte parcela (satelit + ARKOD sloj Agencije za plaćanja) --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<style>
    .agro-oznaka { background: rgba(6, 18, 10, .78); color: #fff; border: 0; border-radius: 6px; font-weight: 700; font-size: 12px; padding: 2px 6px; box-shadow: none; }
    .agro-oznaka::before { display: none; }
</style>
<script>
    window.agroKarta = {
        // nova karta u elementu (stara se ukloni); satelitska podloga i obrubi svih ARKOD parcela kad je karta dovoljno približena
        karta(el, opcije = {}) {
            if (el._agroKarta) { el._agroKarta.remove(); }
            const map = L.map(el, Object.assign({ scrollWheelZoom: false }, opcije));
            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19, attribution: 'Snimke &copy; Esri',
            }).addTo(map);
            L.tileLayer.wms('https://servisi.apprrr.hr/NIPP/wms', {
                layers: 'NIPP:hr.land_parcels', format: 'image/png', transparent: true, opacity: 0.55, minZoom: 14,
                attribution: 'ARKOD &copy; Agencija za plaćanja',
            }).addTo(map);
            el._agroKarta = map;
            setTimeout(() => map.invalidateSize(), 150);
            return map;
        },

        // jedna parcela (pregled pri unosu, popup)
        nacrtaj(el, poligon) {
            const map = this.karta(el);
            const p = L.polygon(poligon, { color: '#3ee36f', weight: 3, fillOpacity: 0.25 }).addTo(map);
            map.fitBounds(p.getBounds(), { padding: [24, 24], maxZoom: 17 });
            setTimeout(() => map.fitBounds(p.getBounds(), { padding: [24, 24], maxZoom: 17 }), 200);
            return map;
        },
    };
</script>
