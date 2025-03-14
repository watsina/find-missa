<template>
  <v-date-input
    v-bind="$attrs"
    v-model="model"
    :error-messages="error"
    hide-details="auto"
    density="compact"
    prepend-icon=""
    color="primary"
    prepend-inner-icon="$calendar"
  >
  </v-date-input>
</template>

<script setup>
  defineProps({
    error: { type: [String, Array], default: '' },
  });

  const model = defineModel({
    type: [String, Object],
    default: null,
  });
</script>
