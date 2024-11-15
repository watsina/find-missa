<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import AppLayout from '@/layouts/AppLayout';
  import ChurchesTable from './ChurchesTable.vue';

  const churches = ref([]);
  const isFetching = ref(false);

  onMounted(() => {
    fetchChurches();
  });

  const fetchChurches = () => {
    isFetching.value = true;
    http.get('/api/v1/churches').then(({ data }) => {
      churches.value = data?.data;
    });
  };
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw-text-xl tw-font-semibold tw-leading-tight tw-text-gray-800">Churches</h2>
    </template>
    <v-container>
      <ChurchesTable :churches="churches" class="tw-py-12" :is-fetching="isFetching" />
    </v-container>
  </app-layout>
</template>
