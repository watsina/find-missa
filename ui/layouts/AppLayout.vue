<template>
  <v-layout class="rounded rounded-md">
    <global-notifications />

    <session-expired-modal v-model="showSessionExpiredModal" />
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
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import NavDrawer from './NavDrawer';
  import TopBar from './TopBar';
  import GlobalNotifications from '@/components/GlobalNotifications';
  import SessionExpiredModal from '@/pages/auth/SessionExpiredModal';

  const drawer = ref(true);
  const rail = ref(true);
  const showSessionExpiredModal = ref(false);

  onMounted(async () => {
    http.interceptors.response.use(null, async (error) => {
      if (error.response?.status === 401 || error.response?.status === 419) {
        showSessionExpiredModal.value = true;
      }

      // if (error?.response?.status === 403 && this.$route.name !== 'errors.403') {
      //   await this.$router.push({ name: 'errors.403' });
      // }

      return Promise.reject(error);
    });
  });
</script>
