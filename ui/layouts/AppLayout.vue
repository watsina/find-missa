<template>
  <v-layout class="rounded rounded-md">
    <session-expired-modal v-model="showSessionExpiredModal" />
    <top-bar v-if="authUser" v-model:drawer="drawer" v-model:rail="rail">
      <template v-if="$slots.head" #header>
        <slot name="head" />
      </template>
    </top-bar>
    <guest-top-bar v-else></guest-top-bar>

    <nav-drawer v-if="authUser" v-model:drawer="drawer" v-model:rail="rail" />

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
  import GuestTopBar from './GuestTopBar';
  import SessionExpiredModal from '@/pages/auth/SessionExpiredModal';
  import useAuthStore from '@/stores/useAuthStore';
  import { computed } from 'vue';

  const authStore = useAuthStore();

  const authUser = computed(() => authStore?.user);

  const drawer = ref(true);
  const rail = ref(true);
  const showSessionExpiredModal = ref(false);

  onMounted(async () => {
    http.interceptors.response.use(null, async (error) => {
      if (error.response?.status === 401 || error.response?.status === 419) {
        showSessionExpiredModal.value = true;
      }

      if (error?.response?.status === 403 && this.$route.name !== 'errors.403') {
        await this.$router.push({ name: 'errors.403' });
      }

      return Promise.reject(error);
    });
  });
</script>
