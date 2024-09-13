<template>
  <v-layout class="rounded rounded-md">
    <top-bar v-model:rail="rail" v-model:drawer="drawer" />

    <!-- <nav-drawer /> -->

    <main-view />
  </v-layout>
</template>

<script setup>
  import { ref } from 'vue';
  //   import NavDrawer from './NavDrawer';
  import TopBar from './TopBar';
  import MainView from './MainView';

  const drawer = ref(true);
  const rail = ref(true);
</script>
