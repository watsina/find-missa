<template>
  <v-layout class="rounded rounded-md">
    <global-notifications />
    <global-confirmation-dialog />
    <guest-top-bar>
      <template v-if="$slots.head" #header>
        <slot name="head" />
      </template>
    </guest-top-bar>

    <v-main class="tw-flex tw-items-center tw-justify-center" style="min-height: 300px">
      <slot />
    </v-main>
  </v-layout>
</template>

<script setup>
  import GuestTopBar from './GuestTopBar';
  import GlobalNotifications from '@/components/GlobalNotifications';
  import GlobalConfirmationDialog from '@/components/GlobalConfirmationDialog';
</script>
