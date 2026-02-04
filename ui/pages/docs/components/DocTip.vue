<script setup>
  import { mdiLightbulbOnOutline } from '@mdi/js';

  defineProps({
    title: {
      type: String,
      default: 'Tip',
    },
  });
</script>

<template>
  <v-alert border="start" class="tw:my-4" :icon="mdiLightbulbOnOutline" type="info" variant="tonal">
    <template #title>
      <span class="tw:font-semibold">{{ title }}</span>
    </template>
    <slot />
  </v-alert>
</template>
