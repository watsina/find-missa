<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import AppLayout from '@/layouts/AppLayout';
  import ParishesTable from './ParishesTable.vue';

  const parishes = ref([]);
  const isFetching = ref(false);

  onMounted(() => {
    fetchParishes();
  });

  const fetchParishes = () => {
    isFetching.value = true;
    http.get('/api/v1/parishes').then(({ data }) => {
      parishes.value = data?.data;
    });
  };
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw-text-xl tw-font-semibold tw-leading-tight tw-text-gray-800">Parishes</h2>
    </template>
    <v-container>
      <parishes-table class="tw-py-12" :is-fetching="isFetching" :parishes="parishes" />
    </v-container>
  </app-layout>
</template>
