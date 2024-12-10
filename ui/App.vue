<template>
  <global-notifications />
  <global-confirmation-dialog />
  <router-view />
</template>

<script setup>
  import GlobalNotifications from '@/components/GlobalNotifications';
  import GlobalConfirmationDialog from '@/components/GlobalConfirmationDialog';
</script>
