<template>
  <v-app-bar app elevate-on-scroll elevation="1" flat>
    <template #prepend>
      <v-app-bar-nav-icon
        variant="text"
        @click.stop="!$vuetify.display.mobile ? (rail = !rail) : (drawer = !drawer)"
      ></v-app-bar-nav-icon>
      <div class="tw-flex tw-items-center shrink-0 tw-mr-4">
        <NavLink :href="{ name: 'dashboard.index' }">
          <ApplicationMark class="tw-block tw-w-auto tw-h-9" />
        </NavLink>
      </div>
      <v-app-bar-title>App Name</v-app-bar-title>
    </template>
    <template v-if="$slots.header" #extension>
      <header class="">
        <div class="tw-px-4 tw-mx-auto md:tw-px-6 lg:tw-px-8">
          <slot name="header" />
        </div>
      </header>
    </template>
    <v-spacer></v-spacer>
    <user-avatar></user-avatar>
    <div class="tw-ml-4"></div>
  </v-app-bar>
</template>

<script setup>
  import NavLink from '@/components/NavLink';
  import ApplicationMark from '@/components/ApplicationMark';
  import UserAvatar from './UserAvatar';

  const rail = defineModel('rail', { type: Boolean });
  const drawer = defineModel('drawer', { type: Boolean });
</script>
