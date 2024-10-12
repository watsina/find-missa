<template>
  <v-navigation-drawer
    v-model="drawer"
    :permanent="!$vuetify.display.mobile"
    :rail="!$vuetify.display.mobile ? rail : false"
  >
    <v-list :lines="false" nav>
      <v-list-item
        v-for="(item, i) in navigation"
        :key="i"
        color="primary"
        :value="item"
        :to="{ name: item.routeName }"
      >
        <template #prepend>
          <v-icon :icon="item.icon" :title="item.name"></v-icon>
        </template>
        <v-list-item-title class="!tw-capitalize">
          {{ item.name }}
        </v-list-item-title>
      </v-list-item>
    </v-list>
  </v-navigation-drawer>
</template>

<script setup>
  import { computed } from 'vue';
  import {
    mdiHomeAnalytics,
    mdiChurch,
    mdiHomeCity,
    mdiHomeGroup,
    mdiMap,
    mdiRunFast,
    mdiAccountDetailsOutline,
  } from '@mdi/js';

  const drawer = defineModel('drawer', { type: Boolean });
  const rail = defineModel('rail', { type: Boolean });

  const links = [
    {
      name: 'Dashboard',
      routeName: 'dashboard.index',
      icon: mdiHomeAnalytics,
      current: true,
      show: true,
    },
    {
      name: 'dioceses',
      routeName: 'dioceses.index',
      icon: mdiMap,
      current: true,
      show: true,
    },
    {
      name: 'parishes',
      routeName: 'parishes.index',
      icon: mdiHomeCity,
      current: true,
      show: true,
    },
    {
      name: 'sub parishes',
      routeName: 'sub-parishes.index',
      icon: mdiHomeGroup,
      current: true,
      show: true,
    },
    {
      name: 'churches',
      routeName: 'churches.index',
      icon: mdiChurch,
      current: true,
      show: true,
    },
    {
      name: 'activities',
      routeName: 'activities.index',
      icon: mdiRunFast,
      current: true,
      show: true,
    },
    {
      name: 'priests',
      routeName: 'priests.index',
      icon: mdiAccountDetailsOutline,
      current: true,
      show: true,
    },
  ];

  const navigation = computed(() => links.filter((link) => link.show));
</script>
