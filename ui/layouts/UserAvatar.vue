<template>
  <v-menu offset="8, 0" v-if="store.hasUser">
    <template #activator="{ props }">
      <v-btn icon v-bind="props">
        <v-avatar size="40">
          <v-img :alt="user.name" :src="user.profile_photo_url"></v-img>
        </v-avatar>
      </v-btn>
    </template>
    <v-sheet rounded="md" width="150">
      <div class="tw-block tw-px-4 tw-py-2 tw-text-xs tw-text-gray-400">Manage Account</div>

      <ResponsiveNavLink href="/auth/profile"> Profile </ResponsiveNavLink>
      <div class="tw-border-t tw-border-gray-200" />
      <form method="POST" @submit.prevent="logout">
        <ResponsiveNavLink as="button"> Log Out </ResponsiveNavLink>
      </form>
    </v-sheet>
  </v-menu>
</template>

<script setup>
  import { computed } from 'vue';
  import { http } from '@/plugins';
  import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
  import useAuthStore from '@/stores/useAuthStore';

  const store = useAuthStore();

  const user = computed(() => store?.user);

  const logout = () => {
    http.post('/api/v1/logout').then(() => window.location.reload());
  };
</script>
