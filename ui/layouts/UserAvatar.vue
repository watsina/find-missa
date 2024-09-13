<template>
  <v-menu v-if="user" offset="8, 0">
    <template #activator="{ props }">
      <v-btn icon v-bind="props">
        <v-avatar size="40">
          <v-img :alt="user.name" :src="user.profile_photo_url"></v-img>
        </v-avatar>
      </v-btn>
    </template>
    <v-sheet rounded="md" width="150">
      <div class="tw-block tw-px-4 tw-py-2 tw-text-xs tw-text-gray-400">Manage Account</div>

      <ResponsiveNavLink active="auth/profile" href="/auth/profile"> Profile </ResponsiveNavLink>
      <div class="tw-border-t tw-border-gray-200" />
      <form method="POST" @submit.prevent="logout">
        <ResponsiveNavLink as="button"> Log Out </ResponsiveNavLink>
      </form>
    </v-sheet>
  </v-menu>
</template>

<script setup>
  import { http } from '@/plugins';
  import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

  defineProps({
    user: { type: Object, required: true },
  });

  const logout = () => {
    http.post('/api/v1/logout').then(() => window.location.reload());
  };
</script>
