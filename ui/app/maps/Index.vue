<template>
  <GMapMap :center="center" map-type-id="terrain" style="width: 100vw; height: 20rem" :zoom="10">
    <GMapCluster :zoom-on-click="true">
      <GMapMarker
        v-for="(m, index) in markers"
        :key="index"
        :clickable="true"
        :draggable="true"
        :position="m.position"
        @click="center = m.position"
      />
    </GMapCluster>
  </GMapMap>
</template>

<script>
  export default {
    data() {
      return {
        center: { lat: 51.093048, lng: 6.84212 },
        markers: [
          {
            position: {
              lat: 51.093048,
              lng: 6.84212,
            },
          },
          {
            position: {
              lat: 51.198429,
              lng: 6.69529,
            },
          },
          {
            position: {
              lat: 51.165218,
              lng: 7.067116,
            },
          },
          {
            position: {
              lat: 51.09256,
              lng: 6.84074,
            },
          },
        ],
      };
    },
  };
</script>

<style>
  body {
    margin: 0;
  }
</style>
