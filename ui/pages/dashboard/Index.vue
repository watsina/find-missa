<script setup>
  import AppLayout from '@/layouts/AppLayout.vue';
  import { ref } from 'vue';

  const uploading = ref(false);
</script>

<template>
  <AppLayout>
    <template #head>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Dashboard
      </h2>
    </template>

    <v-container>
      <v-row class="py-12">
        <v-col cols="12">
          <div class="p-6 text-gray-900 dark:text-gray-100">Welcome to your dashboard</div>
        </v-col>
        <v-col cols="12"></v-col>
        <v-col cols="12"></v-col>
        <v-col cols="12"></v-col>
      </v-row>
    </v-container>
  </AppLayout>
</template>
