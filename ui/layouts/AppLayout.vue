<template>
  <v-layout class="tw-rounded-md">
    <session-expired-modal v-model="showSessionExpiredModal" />
    <global-notifications />
    <global-confirmation-dialog />
    <top-bar v-if="authUser" v-model:drawer="drawer" v-model:rail="rail">
      <template v-if="$slots.head" #header>
        <slot name="head" />
      </template>
    </top-bar>
    <guest-top-bar v-else></guest-top-bar>

    <nav-drawer v-if="authUser" v-model:drawer="drawer" v-model:rail="rail" />

    <v-main class="tw-flex tw-items-center tw-justify-center" style="min-height: 300px">
      <slot />
    </v-main>
  </v-layout>
</template>

<script setup>
  import { computed, ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import NavDrawer from './NavDrawer';
  import TopBar from './TopBar';
  import GuestTopBar from './GuestTopBar';
  import SessionExpiredModal from '@/pages/auth/SessionExpiredModal';
  import GlobalNotifications from '@/components/GlobalNotifications';
  import GlobalConfirmationDialog from '@/components/GlobalConfirmationDialog';
  import useAuthStore from '@/stores/useAuthStore';

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
