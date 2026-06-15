<template>
    <div style="height: 100%; width: 100%;">
        <div v-if="error" class="error-overlay">
            <p><strong>Errore nel caricamento dei dati</strong></p>
            <p>{{ error }}</p>
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
import { ref } from 'vue';
import "leaflet/dist/leaflet.css";
import L from 'leaflet';
import { LMap, LTileLayer, LMarker, LPopup } from "@vue-leaflet/vue-leaflet";

const zoom = ref(6);
const center = ref([42.8333, 12.8333]); // Centro Italia
const cantieri = ref([]);
const error = ref(null);

const loadCantieri = async () => {
    try {
        const response = await fetch(window.App.cantieriApiUrl);
        if (!response.ok) {
            throw new Error(`Il server ha risposto con stato: ${response.status}`);
        }
        const data = await response.json();
        cantieri.value = data;
        console.log(`Caricati ${data.length} cantieri.`);
    } catch (e) {
        error.value = 'Impossibile connettersi al server per recuperare i dati dei cantieri.';
        console.error("Impossibile caricare i cantieri:", e);
    }
};

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

    setTimeout(() => {
        leafletMapObject.invalidateSize();
    }, 0);
    loadCantieri();
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
</style>