<script setup>
  import { mdiAlertOutline } from '@mdi/js';

  defineProps({
    title: {
      type: String,
      default: 'Warning',
    },
  });
</script>

<template>
  <v-alert border="start" class="tw:my-4" :icon="mdiAlertOutline" type="warning" variant="tonal">
    <template #title>
      <span class="tw:font-semibold">{{ title }}</span>
    </template>
    <slot />
  </v-alert>
</template>
