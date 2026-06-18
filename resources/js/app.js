// c:\progetti\FilleaGoNext\resources\js\app.js

import './bootstrap';
import { createApp } from 'vue';

// Importa il componente che vogliamo usare
import MapComponent from './components/MapComponent.vue';

// Cerca l'elemento nel DOM dove vogliamo montare il componente
const mapElement = document.getElementById('map-container');

// Se l'elemento esiste, crea un'app Vue con MapComponent e montala, passando la prop 'mode'
if (mapElement) {
    const props = {
        mode: mapElement.dataset.mode || 'coords',
        // Converte le coordinate in numeri, fornendo un default se non presenti
        lat: parseFloat(mapElement.dataset.lat || '41.9027835'),
        lon: parseFloat(mapElement.dataset.lon || '12.4963655'),
        searchLocation: mapElement.dataset.searchLocation || 'Roma'
    };
    createApp(MapComponent, props).mount('#map-container');
}
