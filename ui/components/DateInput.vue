<template>
  <v-date-input
    v-bind="$attrs"
    v-model="model"
    color="primary"
    :error-messages="error"
    :hide-actions="true"
    hide-details="auto"
    prepend-icon=""
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
