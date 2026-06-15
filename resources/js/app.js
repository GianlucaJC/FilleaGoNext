// c:\progetti\FilleaGoNext\resources\js\app.js

import './bootstrap';
import { createApp } from 'vue';

// Importa il componente che vogliamo usare
import MapComponent from './components/MapComponent.vue';

// Cerca l'elemento nel DOM dove vogliamo montare il componente
const mapElement = document.getElementById('map-container');

// Se l'elemento esiste, crea un'app Vue con MapComponent e montala
if (mapElement) {
    createApp(MapComponent).mount('#map-container');
}
