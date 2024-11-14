<template>
  <v-date-input
    v-bind="$attrs"
    v-model="model"
    :density="density"
    :error-messages="error"
    hide-details="auto"
    :hint="hint"
    :label="label"
    :placeholder="placeholder"
    prepend-icon=""
    prepend-inner-icon="$calendar"
  >
  </v-date-input>
</template>

<script setup>
  defineProps({
    error: { type: [String, Array], default: '' },
    hint: { type: String, default: '' },
    label: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    density: { type: String, default: 'default' },
    color: { type: String, default: 'primary' },
  });

  const model = defineModel({
    type: [String, Object],
    default: null,
  });
</script>
