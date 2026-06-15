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
// L.divIcon permette di usare HTML (e quindi SVG) come icona.
const craneIcon = L.divIcon({
    // Incolla il tuo codice SVG qui dentro ai backtick (`).
    html: `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100%" height="100%">
        <style>
            .crane-dark { fill: #2C3E50; }
            .crane-accent { fill: #F39C12; }
            .crane-line { stroke: #2C3E50; stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round; }
        </style>

        <g id="construction-crane">
            <path class="crane-dark" d="M 25,88 L 45,88 L 43,83 L 27,83 Z" />
            <rect class="crane-dark" x="20" y="88" width="30" height="3" rx="1" />

            <line class="crane-line" x1="31" y1="83" x2="31" y2="25" />
            <line class="crane-line" x1="39" y1="83" x2="39" y2="25" />
            <line class="crane-line" x1="31" y1="80" x2="39" y2="70" />
            <line class="crane-line" x1="39" y1="80" x2="31" y2="70" />
            <line class="crane-line" x1="31" y1="65" x2="39" y2="55" />
            <line class="crane-line" x1="39" y1="65" x2="31" y2="55" />
            <line class="crane-line" x1="31" y1="50" x2="39" y2="40" />
            <line class="crane-line" x1="39" y1="50" x2="31" y2="40" />
            <line class="crane-line" x1="31" y1="35" x2="39" y2="25" />
            <line class="crane-line" x1="39" y1="35" x2="31" y2="25" />

            <rect class="crane-accent" x="40" y="26" width="10" height="10" rx="1.5" />
            <path class="crane-dark" d="M 46,28 L 49,28 L 49,32 L 44,32 L 44,30 Z" opacity="0.8" />

            <polygon class="crane-dark" points="31,25 39,25 35,10" />

            <line class="crane-line" x1="10" y1="25" x2="85" y2="25" />
            <line class="crane-line" x1="31" y1="20" x2="80" y2="25" />
            <line class="crane-line" x1="39" y1="25" x2="45" y2="21" />
            <line class="crane-line" x1="45" y1="21" x2="52" y2="25" />
            <line class="crane-line" x1="52" y1="25" x2="60" y2="22" />
            <line class="crane-line" x1="60" y1="22" x2="68" y2="25" />
            <line class="crane-line" x1="68" y1="25" x2="75" y2="23" />
            <line class="crane-line" x1="75" y1="23" x2="82" y2="25" />

            <rect class="crane-dark" x="12" y="21" width="8" height="8" rx="1" />

            <line class="crane-line" x1="35" y1="10" x2="15" y2="21" opacity="0.7" />
            <line class="crane-line" x1="35" y1="10" x2="65" y2="23" opacity="0.7" />

            <g id="hook-and-load">
            <rect class="crane-dark" x="62" y="25" width="6" height="3" />
            <line class="crane-line" x1="65" y1="28" x2="65" y2="48" style="stroke-width: 1;" />
            <polygon class="crane-accent" points="63,48 67,48 65,53" />
            <path d="M 65,53 Q 63,55 63,57 Q 65,59 66,57" fill="none" stroke="#2C3E50" stroke-width="1.2" stroke-linecap="round" />
            
            <rect class="crane-dark" x="58" y="60" width="14" height="10" rx="1" />
            <line class="crane-line" x1="65" y1="56" x2="59" y2="60" style="stroke-width: 0.8;" />
            <line class="crane-line" x1="65" y1="56" x2="71" y2="60" style="stroke-width: 0.8;" />
            </g>
        </g>
        </svg>    
    `,
    className: 'crane-svg-icon', // Classe CSS per lo stile
    iconSize: [40, 40], // Dimensioni dell'icona in pixel
    iconAnchor: [20, 40], // Punto dell'icona che corrisponde alla posizione del marker
    popupAnchor: [0, -40] // Punto da cui il popup si aprirà
});

// Funzione per caricare i dati dei cantieri dall'API
const loadCantieri = async () => {
    try {
        // Costruisce l'URL completo per la chiamata API usando l'URL di base fornito da Laravel.
        // Questo garantisce che funzioni sia in locale che in produzione, anche in sottocartelle.
        const response = await fetch(`${window.App.baseUrl}/public/api/cantieri`);
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
</style>