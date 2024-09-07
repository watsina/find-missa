<script setup>
  defineProps({
    variant: { type: String, default: 'elevated' },
  });
</script>

<template>
  <v-btn :variant="variant">
    <slot />
  </v-btn>
</template>
