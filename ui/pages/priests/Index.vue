<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import AppLayout from '@/layouts/AppLayout';
  import PriestsTable from './PriestsTable.vue';

  const priests = ref([]);
  const isFetching = ref(false);

  onMounted(() => {
    fetchPriests();
  });

  const fetchPriests = () => {
    isFetching.value = true;
    http.get('/api/v1/priests').then(({ data }) => {
      priests.value = data?.data;
    });
  };
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw-text-xl tw-font-semibold tw-leading-tight tw-text-gray-800">Priests</h2>
    </template>
    <v-container>
      <PriestsTable v-model:priests="priests" :is-fetching="isFetching" />
    </v-container>
  </app-layout>
</template>
