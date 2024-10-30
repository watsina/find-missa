<template>
  <v-layout class="rounded rounded-md">
    <session-expired-modal v-model="showSessionExpiredModal" />
    <top-bar v-model:drawer="drawer" v-model:rail="rail">
      <template v-if="$slots.head" #header>
        <slot name="head" />
      </template>
    </top-bar>

    <nav-drawer v-model:drawer="drawer" v-model:rail="rail" />

    <v-main class="d-flex align-center justify-center" style="min-height: 300px">
      <slot />
    </v-main>
  </v-layout>
</template>

<script setup>
  import { ref, onMounted } from 'vue';
  import { useRouter } from 'vue-router';
  import { http } from '@/plugins';
  import NavDrawer from './NavDrawer';
  import TopBar from './TopBar';
  import SessionExpiredModal from '@/pages/auth/SessionExpiredModal';

  const drawer = ref(true);
  const rail = ref(true);
  const router = useRouter();
  const showSessionExpiredModal = ref(false);

  onMounted(async () => {
    http.interceptors.response.use(null, async (error) => {
      if (error.response?.status === 401 || error.response?.status === 419) {
        showSessionExpiredModal.value = true;
      }

      if (error?.response?.status === 403 && router.currentRoute.value.name !== 'errors.403') {
        await router.push({ name: 'errors.403' });
      }

      return Promise.reject(error);
    });
  });
</script>
