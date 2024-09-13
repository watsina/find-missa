<template>
  <v-app-bar app flat elevation="1" elevate-on-scroll>
    <template #prepend>
      <v-app-bar-nav-icon
        variant="text"
        @click.stop="!$vuetify.display.mobile ? (rail = !rail) : (drawer = !drawer)"
      ></v-app-bar-nav-icon>
      <div class="tw-flex tw-items-center shrink-0 tw-mr-4">
        <NavLink href="/">
          <ApplicationMark class="tw-block tw-w-auto tw-h-9" />
        </NavLink>
      </div>
      <v-app-bar-title>App Name</v-app-bar-title>
    </template>
    <v-spacer></v-spacer>
    <user-avatar v-if="store.hasUser" :user="store?.user"></user-avatar>
    <div class="tw-ml-4"></div>
  </v-app-bar>
</template>

<script setup>
  import NavLink from '@/components/NavLink';
  import ApplicationMark from '@/components/ApplicationMark';
  import UserAvatar from './UserAvatar';
  import useAuthStore from '@/stores/useAuthStore';

  const store = useAuthStore();

  const rail = defineModel('rail', { type: Boolean });
  const drawer = defineModel('drawer', { type: Boolean });
</script>
