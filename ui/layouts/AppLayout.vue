<template>
  <v-layout class="rounded rounded-md">
    <global-notifications></global-notifications>
    <top-bar v-model:rail="rail" v-model:drawer="drawer">
      <template #header v-if="$slots.head">
        <slot name="head" />
      </template>
    </top-bar>

    <nav-drawer v-model:rail="rail" v-model:drawer="drawer" />

    <v-main class="d-flex align-center justify-center" style="min-height: 300px">
      <slot />
    </v-main>
  </v-layout>
</template>

<script setup>
  import { ref } from 'vue';
  import NavDrawer from './NavDrawer';
  import TopBar from './TopBar';
  import GlobalNotifications from '@/components/GlobalNotifications';

  const drawer = ref(true);
  const rail = ref(true);
</script>
