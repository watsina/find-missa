<template>
  <v-btn color="primary" v-bind="{ ...$attrs }" :variant="variant">
    <slot />
  </v-btn>
</template>

<script setup>
  defineProps({
    variant: { type: String, default: 'elevated' },
  });
</script>
