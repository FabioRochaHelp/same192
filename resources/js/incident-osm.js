/**
 * Mapa Leaflet carregado via CDN (unpkg) para não depender de `npm install leaflet` no servidor de build.
 * Usa `$wire` no hospedeiro Livewire.
 */
let leafletCdnPromise = null;

async function loadLeafletFromCdn() {
    if (window.L) {
        return window.L;
    }
    if (leafletCdnPromise) {
        return leafletCdnPromise;
    }

    leafletCdnPromise = new Promise((resolve, reject) => {
        if (!document.querySelector('link[data-samu-leaflet-css]')) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            link.setAttribute('data-samu-leaflet-css', '1');
            link.crossOrigin = 'anonymous';
            document.head.appendChild(link);
        }

        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.async = true;
        script.crossOrigin = 'anonymous';
        script.onload = () => resolve(window.L);
        script.onerror = () => reject(new Error('leaflet_cdn_load_failed'));
        document.head.appendChild(script);
    });

    return leafletCdnPromise;
}

let incidentOsmHooksInstalled = false;

function registerIncidentOsmAlpine() {
    const Alpine = window.Alpine;
    if (!Alpine?.data) {
        return;
    }

    if (window.__samuIncidentOsmAlpineDone) {
        return;
    }

    window.__samuIncidentOsmAlpineDone = true;

    Alpine.data('incidentOsmMap', () => ({
        map: null,
        marker: null,

        lw() {
            return this.$wire;
        },

        readLatLng() {
            const wire = this.lw();
            if (!wire) {
                return [-15.793889, -47.882778];
            }
            const lat = parseFloat(wire.latitude);
            const lng = parseFloat(wire.longitude);
            if (Number.isFinite(lat) && Number.isFinite(lng)) {
                return [lat, lng];
            }

            return [-15.793889, -47.882778];
        },

        syncMarkerFromWire() {
            if (!this.map || !this.marker) {
                return;
            }
            const [lat, lng] = this.readLatLng();
            this.marker.setLatLng([lat, lng]);
            this.map.setView([lat, lng], this.map.getZoom(), { animate: false });
            requestAnimationFrame(() => this.map?.invalidateSize());
        },

        async init() {
            let L;
            try {
                L = await loadLeafletFromCdn();
            } catch {
                return;
            }

            const wire = this.lw();
            if (!wire || !this.$refs.mapEl) {
                return;
            }

            const [lat, lng] = this.readLatLng();
            this.map = L.map(this.$refs.mapEl, { zoomControl: true }).setView([lat, lng], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap',
            }).addTo(this.map);

            const icon = L.divIcon({
                className: 'incident-osm-pin',
                html: '<span class="block h-3.5 w-3.5 rounded-full border-2 border-cyan-700 bg-cyan-400 shadow-md ring-2 ring-white dark:border-cyan-300 dark:bg-cyan-500"></span>',
                iconSize: [14, 14],
                iconAnchor: [7, 7],
            });

            this.marker = L.marker([lat, lng], { draggable: true, icon }).addTo(this.map);

            this.marker.on('dragend', () => {
                const p = this.marker.getLatLng();
                wire.set('latitude', p.lat.toFixed(7));
                wire.set('longitude', p.lng.toFixed(7));
            });

            this.map.on('click', (e) => {
                this.marker.setLatLng(e.latlng);
                wire.set('latitude', e.latlng.lat.toFixed(7));
                wire.set('longitude', e.latlng.lng.toFixed(7));
            });

            const invalidate = () => {
                if (!this.map) {
                    return;
                }
                this.syncMarkerFromWire();
                setTimeout(() => this.map.invalidateSize(), 50);
            };

            window.addEventListener('incident-osm-invalidate', invalidate);

            requestAnimationFrame(invalidate);
        },
    }));
}

document.addEventListener('livewire:init', () => {
    registerIncidentOsmAlpine();

    if (!incidentOsmHooksInstalled && window.Livewire?.hook) {
        incidentOsmHooksInstalled = true;
        let morphInvalidateTimer = null;
        Livewire.hook('morph.updated', () => {
            clearTimeout(morphInvalidateTimer);
            morphInvalidateTimer = setTimeout(() => {
                window.dispatchEvent(new CustomEvent('incident-osm-invalidate'));
            }, 200);
        });
    }
});

document.addEventListener('alpine:init', () => {
    registerIncidentOsmAlpine();
});
