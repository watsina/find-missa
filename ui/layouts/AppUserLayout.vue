<template>
  <v-layout class="tw:min-h-screen tw:flex tw:flex-col rounded rounded-md">
    <global-notifications />
    <global-confirmation-dialog />
    <guest-top-bar>
      <template v-if="$slots.head" #header>
        <slot name="head" />
      </template>
    </guest-top-bar>

    <v-main class="tw:flex tw:flex-1 tw:items-center tw:justify-center" style="min-height: 300px">
      <slot />
    </v-main>

    <Footer class="tw:mt-4" />
  </v-layout>
</template>

<script setup>
  import GuestTopBar from './GuestTopBar';
  import GlobalNotifications from '@/components/GlobalNotifications';
  import GlobalConfirmationDialog from '@/components/GlobalConfirmationDialog';
  import Footer from './Footer.vue';
</script>
