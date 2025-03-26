<script setup>
  defineProps({
    error: { type: [String, Array], default: '' },
    hint: { type: String, default: '' },
    label: { type: String, default: '' },
    density: { type: String, default: 'default' },
    color: { type: String, default: 'primary' },
  });

  const model = defineModel({
    type: [Array, Boolean],
    required: true,
  });
</script>

<template>
  <v-checkbox
    ref="input"
    v-bind="{ ...$attrs }"
    v-model="model"
    :color="color"
    :density="density"
    :error-messages="error"
    hide-details="auto"
    :hint="hint"
    :label="label"
  ></v-checkbox>
</template>
