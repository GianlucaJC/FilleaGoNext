<template>
    <div style="height: 100%; width: 100%; position: relative;">
        <div v-if="error" class="error-overlay" :style="{ top: loading ? '70px' : '20px' }">
            <p><strong>Errore nel caricamento dei dati</strong></p>
            <p>{{ error }}</p>
        </div>
        <div v-if="loading" class="loading-overlay">
            <div class="spinner"></div>
        </div>
        <l-map
            v-model:zoom="zoom"
            :center="center"
            @ready="onMapReady"
        >
            <l-tile-layer
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                layer-type="base"
                name="OpenStreetMap"
            ></l-tile-layer>

            <l-circle
                :lat-lng="currentCenter"
                :radius="2000"
                :color="'#d71e2b'"
                :fill-color="'#d71e2b'"
                :fill-opacity="0.2"
            />

            <l-marker v-for="cantiere in cantieri" :key="cantiere.id" :lat-lng="[cantiere.latitude, cantiere.longitude]">
                <l-popup>
                    <b>{{ cantiere.cantiere }}</b><br>
                    {{ cantiere.indirizzo_c }}, {{ cantiere.localita_c }}
                    <div v-if="cantiere.aziende && cantiere.aziende.length > 0" class="mt-2 pt-2 border-top">
                        <strong>Aziende:</strong>
                        <ul class="list-unstyled mb-0 small">
                            <li v-for="azienda in cantiere.aziende" :key="azienda.id">{{ azienda.denominazione }}</li>
                        </ul>
                    </div>
                </l-popup>
            </l-marker>
        </l-map>
    </div>
</template>

<script setup>
import { ref, defineProps } from 'vue';
import "leaflet/dist/leaflet.css";
import L from 'leaflet';
import { LMap, LTileLayer, LMarker, LPopup, LCircle } from "@vue-leaflet/vue-leaflet";

const props = defineProps({
    mode: { type: String, default: 'rome' } // 'rome' or 'geolocation'
});

const zoom = ref(6); // Inizia con una vista generale dell'Italia
const center = ref([42.8333, 12.8333]); // Default: Centro dell'Italia
const romeCenter = [41.9027835, 12.4963655]; // Coordinate di Roma per il cerchio e l'animazione

const cantieri = ref([]);
const error = ref(null);
const loading = ref(false);

const loadCantieri = async (lat = null, lon = null) => {
    let apiUrl = window.App.cantieriApiUrl;
    if (lat !== null && lon !== null) {
        apiUrl += `?lat=${lat}&lon=${lon}`;
    }
    try {
        const response = await fetch(apiUrl);
        if (!response.ok) {
            throw new Error(`Il server ha risposto con stato: ${response.status}`);
        }
        const data = await response.json();
        cantieri.value = data;
        console.log(`Caricati ${data.length} cantieri.`);
    } catch (e) {
        error.value = 'Impossibile connettersi al server per recuperare i dati dei cantieri.';
        console.error("Impossibile caricare i cantieri:", e);
    } finally {
        loading.value = false;
    }
};
const currentCenter = ref(romeCenter); // This will hold the center for the circle and map animation

const onMapReady = (leafletMapObject) => {
    // Questo codice è necessario per correggere il percorso dell'icona di default di Leaflet
    // quando si usa un bundler come Vite. Lo eseguiamo qui per essere sicuri
    // che le icone siano pronte prima di caricare i dati.
    delete L.Icon.Default.prototype._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
    });

    loading.value = true;
    error.value = null; // Clear previous errors

    if (props.mode === 'geolocation' && navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const userLat = position.coords.latitude;
                const userLon = position.coords.longitude;
                const userLocation = [userLat, userLon];
                currentCenter.value = userLocation;
                leafletMapObject.flyTo(userLocation, 13, { duration: 2.5 });
                loadCantieri(userLat, userLon);
            },
            (geoError) => {
                error.value = 'Geolocalizzazione fallita. Mostro i dati per Roma.';
                console.error("Geolocation failed:", geoError);
                currentCenter.value = romeCenter;
                leafletMapObject.flyTo(romeCenter, 13, { duration: 2.5 });
                loadCantieri(); // Load for Rome by default
            }
        );
    } else {
        if (props.mode === 'geolocation') {
            error.value = 'Geolocalizzazione non supportata dal browser. Mostro i dati per Roma.';
            console.warn("Geolocation is not supported by this browser. Falling back to Rome.");
        }
        // Default to Rome or if geolocation is not supported/requested
        currentCenter.value = romeCenter;
        leafletMapObject.flyTo(romeCenter, 13, { duration: 2.5 });
        loadCantieri();
    }

    setTimeout(() => {
        leafletMapObject.invalidateSize();
    }, 0);
};
</script>

<style>
.error-overlay {
    position: absolute;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
    background-color: #f8d7da;
    color: #721c24;
    padding: 15px 20px;
    border: 1px solid #f5c6cb;
    border-radius: 5px;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1001; /* Sopra la mappa ma sotto eventuali popup/modali */
}

.spinner {
    border: 5px solid #f3f3f3;
    border-top: 5px solid #d71e2b; /* Usa il colore del brand */
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>