<template>
    <div style="height: 100%; width: 100%;">
        <div v-if="error" class="error-overlay">
            <p><strong>Errore nel caricamento dei dati</strong></p>
            <p>{{ error }}</p>
        </div>
        <l-map
            v-model:zoom="zoom"
            :center="[42.8333, 12.8333]"
            @ready="onMapReady"
        >
            <l-tile-layer
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                layer-type="base"
                name="OpenStreetMap"
            ></l-tile-layer>

            <l-marker v-for="cantiere in cantieri" :key="cantiere.id" :lat-lng="[cantiere.latitude, cantiere.longitude]" :icon="craneIcon">
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
import { ref, onMounted } from 'vue';
import "leaflet/dist/leaflet.css";
import L from 'leaflet';
import { LMap, LTileLayer, LMarker, LPopup } from "@vue-leaflet/vue-leaflet";

// Dati reattivi per la mappa
const zoom = ref(6);
const cantieri = ref([]);
const error = ref(null);

// Definisce l'icona personalizzata della gru usando il tuo codice SVG.
// Spostiamo lo stile nel tag <style> del componente per evitare problemi con il build di produzione.
const craneIcon = L.divIcon({
    html: `
     
    `,
    className: 'crane-svg-icon', // Classe CSS per lo stile
    iconSize: [40, 40], // Dimensioni dell'icona in pixel
    iconAnchor: [20, 40], // Punto dell'icona che corrisponde alla posizione del marker
    popupAnchor: [0, -40] // Punto da cui il popup si aprirà
});

// Funzione per caricare i dati dei cantieri dall'API
const loadCantieri = async () => {
    try {
        // Usa l'URL completo per l'API, fornito direttamente da Laravel.
       // alert(window.App.cantieriApiUrl)
        const response = await fetch(window.App.cantieriApiUrl);
        if (!response.ok) {
            throw new Error(`Il server ha risposto con stato: ${response.status}`);
        }
        const data = await response.json();
        cantieri.value = data;
        console.log(`Caricati ${data.length} cantieri.`);
    } catch (e) {
        error.value = 'Impossibile connettersi al server per recuperare i dati dei cantieri. Controlla la console per i dettagli tecnici.';
        console.error("Impossibile caricare i cantieri:", e);
    }
};

// Funzione chiamata quando la mappa è pronta, grazie all'evento @ready
const onMapReady = (leafletMapObject) => {
    // Carica i dati dei cantieri non appena la mappa è tecnicamente pronta
    loadCantieri();

    // A volte, anche con @ready, il browser non ha ancora finalizzato il layout del contenitore.
    // Usare setTimeout con un ritardo di 0 sposta l'esecuzione di invalidateSize()
    // alla fine della coda di eventi del browser, garantendo che il layout sia completo.
    // Questo è il trucco definitivo per risolvere il problema della "mappa bianca".
    setTimeout(() => {
        leafletMapObject.invalidateSize();
    }, 0);
};

// Hook che viene eseguito dopo che il componente è stato montato nel DOM
onMounted(() => {
    // Il codice per sovrascrivere l'icona di default non è più necessario
    // dato che stiamo specificando un'icona personalizzata per ogni marker.
});
</script>

<style>
.error-overlay {
    position: absolute;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000; /* Assicura che sia sopra la mappa */
    background-color: #f8d7da;
    color: #721c24;
    padding: 15px 20px;
    border: 1px solid #f5c6cb;
    border-radius: 5px;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Stile per il contenitore dell'icona SVG */
.crane-svg-icon {
    background: transparent;
    border: none;
}

/* Assicura che l'SVG riempia il contenitore definito da iconSize */
.crane-svg-icon svg {
    width: 100%;
    height: 100%;
}

/* Stili per l'SVG della gru, spostati qui dal codice JS */
.crane-svg-icon .crane-dark {
    fill: #2C3E50;
}
.crane-svg-icon .crane-accent {
    fill: #F39C12;
}
.crane-svg-icon .crane-line {
    stroke: #2C3E50;
    stroke-width: 1.5;
    stroke-linecap: round;
    stroke-linejoin: round;
}
</style>