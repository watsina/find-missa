<template>
  <v-navigation-drawer
    v-model="drawer"
    expand-on-hover
    :permanent="!$vuetify.display.mobile"
    :rail="!$vuetify.display.mobile ? rail : false"
  >
    <v-list :lines="false" nav>
      <v-list-item
        v-for="(item, i) in navigation"
        :key="i"
        color="primary"
        :to="{ name: item.routeName }"
        :value="item"
      >
        <template #prepend>
          <v-icon :icon="item.icon" :title="item.name"></v-icon>
        </template>
        <v-list-item-title class="tw:capitalize!">
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
    mdiAccountGroup,
    mdiTimerStopOutline,
    mdiCalendarStar,
    mdiCalendarClock,
    mdiBookOpenPageVariant,
  } from '@mdi/js';
  import useAuthStore from '@/stores/useAuthStore';

  const authStore = useAuthStore();

  const drawer = defineModel('drawer', { type: Boolean });
  const rail = defineModel('rail', { type: Boolean });
  const authUser = computed(() => authStore.user);

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
      name: 'global exceptions',
      routeName: 'global-activity-exceptions.index',
      icon: mdiTimerStopOutline,
      current: true,
      show: authUser.value.is_admin,
    },
    {
      name: 'holidays',
      routeName: 'holidays.index',
      icon: mdiCalendarStar,
      current: true,
      show: true,
    },
    {
      name: 'schedule periods',
      routeName: 'schedule-periods.index',
      icon: mdiCalendarClock,
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
    {
      name: 'users',
      routeName: 'users.index',
      icon: mdiAccountGroup,
      current: true,
      show: authUser.value.is_admin,
    },
    {
      name: 'Documentation',
      routeName: 'docs.index',
      icon: mdiBookOpenPageVariant,
      current: true,
      show: true,
    },
  ];

  const navigation = computed(() => links.filter((link) => link.show));
</script>
