<script setup>
  import NavLink from '@/components/NavLink';
  import ApplicationMark from '@/components/ApplicationMark';
  import UserAvatar from './UserAvatar';
  import TeamsMenu from './TeamsMenu.vue';
  import { useDisplay } from 'vuetify';

  const { smAndDown } = useDisplay();

  const rail = defineModel('rail', { type: Boolean });
  const drawer = defineModel('drawer', { type: Boolean });
</script>

<template>
  <v-app-bar elevation="1" scroll-behavior="elevate">
    <template #prepend>
      <v-app-bar-nav-icon
        variant="text"
        @click.stop="!$vuetify.display.mobile ? (rail = !rail) : (drawer = !drawer)"
      ></v-app-bar-nav-icon>
      <div class="tw:flex tw:items-center shrink-0 tw:md:mr-4">
        <NavLink :href="{ name: 'dashboard.index' }">
          <ApplicationMark class="tw:block tw:w-auto tw:h-9" />
        </NavLink>
      </div>
      <v-app-bar-title v-if="!smAndDown">Find Missa</v-app-bar-title>
    </template>
    <template v-if="$slots.header" #extension>
      <header class="tw:font-semibold tw:text-xl tw:text-gray-800 tw:leading-tight">
        <div class="tw:px-4 tw:mx-auto tw:md:px-6 tw:lg:px-8">
          <slot name="header" />
        </div>
      </header>
    </template>
    <v-spacer v-if="!smAndDown"></v-spacer>
    <teams-menu />
    <div class="tw:md:mr-4 tw:mr-2"></div>
    <user-avatar></user-avatar>
    <div class="tw:md:ml-4 tw:ml-2"></div>
  </v-app-bar>
</template>
