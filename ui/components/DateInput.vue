<template>
  <v-date-input
    v-bind="$attrs"
    v-model="model"
    :error-messages="error"
    hide-details="auto"
    prepend-icon=""
    prepend-inner-icon="$calendar"
  >
  </v-date-input>
</template>

<script setup>
  defineProps({
    error: { type: [String, Array], default: '' },
    color: { type: String, default: 'primary' },
  });

  const model = defineModel({
    type: [String, Object],
    default: null,
  });
</script>
