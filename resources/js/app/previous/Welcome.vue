<script setup>
  import { Head, Link } from '@inertiajs/vue3';
  import { computed } from 'vue';
  import { onBeforeMount } from 'vue';
  import { reactive } from 'vue';
  import { ref } from 'vue';
  // import { GmvMap } from '@gmap-vue/v3';

  defineProps({
    canLogin: {
      type: Boolean,
    },
  });

  const churches = ref([]);
  const infoWindowOptions = reactive({
    pixelOffset: {
      width: 0,
      height: -35,
    },
  });
  const activeChurch = ref({});
  const infoWindowOpened = ref(false);

  onBeforeMount(() => {
    axios
      .get('/churches')
      .then(({ data }) => {
        churches.value = data?.data;
      })
      .catch((error) => console.error(error));
  });

  const mapCenter = computed(() => {
    if (!churches.value.length) {
      return {
        lat: 10,
        lng: 10,
      };
    }

    return {
      lat: parseFloat(churches.value[0].latitude),
      lng: parseFloat(churches.value[0].longitude),
    };
  });

  const infoWindowPosition = computed(() => {
    return {
      lat: parseFloat(activeChurch.value.latitude),
      lng: parseFloat(activeChurch.value.longitude),
    };
  });

  function getPosition(r) {
    return {
      lat: parseFloat(r.latitude),
      lng: parseFloat(r.longitude),
    };
  }

  function handleMarkerClicked(r) {
    activeChurch.value = r;
    infoWindowOpened.value = true;
  }

  function handleInfoWindowClose() {
    activeChurch.value = {};
    infoWindowOpened.value = false;
  }

  function handleMapClick(e) {
    churches.value.push({
      name: 'Placeholder',
      hours: '00:00am-00:00pm',
      city: 'Orlando',
      state: 'FL',
      latitude: e.latLng.lat(),
      longitude: e.latLng.lng(),
    });
  }
</script>

<template>
  <Head title="Welcome" />

  <div
    class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white"
  >
    <div v-if="canLogin" class="sm:fixed sm:top-0 sm:right-0 p-6 text-end">
      <Link
        v-if="$page.props.auth.user"
        class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
        :href="route('dashboard')"
        >Dashboard</Link
      >

      <template v-else>
        <Link
          class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
          :href="route('login')"
          >Log in</Link
        >
      </template>
    </div>

    <div class="max-w-7xl mx-auto p-6 lg:p-8">
      <h2>Churches</h2>
      <div class="mt-16">
        <!-- map with churches goes here -->
        <gmap-map
          :center="mapCenter"
          style="width: 100%; height: 440px"
          :zoom="10"
          @click="handleMapClick"
        >
          <gmap-info-window
            :opened="infoWindowOpened"
            :options="infoWindowOptions"
            :position="infoWindowPosition"
            @closeclick="handleInfoWindowClose"
          >
            <div class="info-window">
              <h2 v-text="activeChurch.name"></h2>
              <!-- <h5 v-text="'Hours: ' + activeChurch.hours"></h5> -->
              <!-- <p v-text="activeChurch.address"></p> -->
              <!-- <p v-text="activeChurch.city + ', ' + activeChurch.state"></p> -->
            </div>
          </gmap-info-window>
          <gmap-marker
            v-for="(r, index) in churches"
            :key="r.id"
            :clickable="true"
            :draggable="false"
            :position="getPosition(r)"
            @click="handleMarkerClicked(r)"
          ></gmap-marker>
        </gmap-map>
      </div>

      <div class="flex justify-center mt-16 px-6 sm:items-center sm:justify-between"></div>
    </div>
  </div>
</template>

<style>
  .bg-dots-darker {
    background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E");
  }

  @media (prefers-color-scheme: dark) {
    .dark\:bg-dots-lighter {
      background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(255,255,255,0.07)'/%3E%3C/svg%3E");
    }
  }
</style>
