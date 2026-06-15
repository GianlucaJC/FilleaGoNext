<template>
    <div style="height: 100%; width: 100%;">
        <l-map
            :zoom="zoom"
            :center="[42.8333, 12.8333]"
            @ready="onMapReady"
        >
            <l-tile-layer
                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                layer-type="base"
                name="OpenStreetMap"
            ></l-tile-layer>
        </l-map>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import "leaflet/dist/leaflet.css";
import { LMap, LTileLayer } from "@vue-leaflet/vue-leaflet";

const zoom = ref(6);

const onMapReady = (leafletMapObject) => {
    // Se vedi questo alert, il problema è risolto e possiamo rimettere i dati.
    alert("LA MAPPA È PRONTA!");
    setTimeout(() => {
        leafletMapObject.invalidateSize();
    }, 0);
};
</script>

<style></style>