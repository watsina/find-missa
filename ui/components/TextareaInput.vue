<template>
  <v-textarea
    ref="input"
    v-bind="{ ...$attrs }"
    v-model="model"
    :error-messages="error"
    hide-details="auto"
    :hint="hint"
    :label="label"
    :placeholder="placeholder"
  ></v-textarea>
</template>

<script setup>
  defineProps({
    error: { type: [String, Array], default: '' },
    hint: { type: String, default: '' },
    label: { type: String, default: '' },
    placeholder: { type: String, default: '' },
  });

  const model = defineModel({ type: [String, Number], required: true });
</script>
