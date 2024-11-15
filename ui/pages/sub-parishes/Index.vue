<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import AppLayout from '@/layouts/AppLayout';
  import SubParishesTable from './SubParishesTable.vue';

  const subParishes = ref([]);
  const isFetching = ref(false);

  onMounted(() => {
    fetchSubParishes();
  });

  const fetchSubParishes = () => {
    isFetching.value = true;
    http.get('/api/v1/sub-parishes').then(({ data }) => {
      subParishes.value = data?.data;
    });
  };
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw-text-xl tw-font-semibold tw-leading-tight tw-text-gray-800">Sub Parishes</h2>
    </template>
    <v-container>
      <sub-parishes-table class="tw-py-12" :is-fetching="isFetching" :sub-parishes="subParishes" />
    </v-container>
  </app-layout>
</template>
