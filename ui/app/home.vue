<template>
  <v-container>
    <v-row no-gutters>
      <v-col align="start" :cols="12">
        <div>Let's get started</div>
      </v-col>
      <v-col :cols="12">
        <GoogleMap
          ref="mapRef"
          :api-key="map.key"
          :center="center"
          :max-zoom="map.maxZoom"
          :min-zoom="map.minZoom"
          style="width: 100%; height: 500px"
          :zoom="map.zoom"
          @center_changed="centerChanged"
          @zoom_changed="zoomChanged"
        >
          <Marker :options="{ position: map.center }" />
        </GoogleMap>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
  import { ref, watch } from 'vue';
  import { GoogleMap, Marker } from 'vue3-google-map';

  const center = { lat: 40.689247, lng: -74.044502 };
  const map = {
    key: import.meta.env.VITE_GOOGLE_API_KEY, // Place a Google API Key here
    center: {
      lat: 40.689247,
      lng: -74.044502,
    },
    zoom: 15,
    minZoom: 2,
    maxZoom: null,
    streetViewControl: false,
  };

  let gmap = null;

  const mapRef = ref(null);

  watch(
    () => mapRef.value?.ready,
    (ready) => {
      if (ready) {
        gmap = mapRef.value.map;
        console.log('[GMap] Version:', mapRef.value.api.version);
      }
    },
  );

  function zoomChanged() {
    console.log('[GMap] Zoom:', gmap.getZoom());
  }

  function centerChanged() {
    const center = gmap.getCenter();
    console.log('[GMap] Center: (', center.lat(), ',', center.lng(), ')');
  }
</script>

<style></style>
