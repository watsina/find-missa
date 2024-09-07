<script setup>
  defineProps({
    variant: { type: String, default: 'elevated' },
  });
</script>

<template>
  <v-btn color="error" :variant="variant" v-bind="{ ...$attrs }">
    <slot />
  </v-btn>
</template>
