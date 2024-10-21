<template>
  <v-select
    ref="input"
    v-bind="{ ...$attrs }"
    v-model="model"
    :color="color"
    :density="density"
    :error-messages="error"
    hide-details="auto"
    :hint="hint"
    :items="items"
    :label="label"
  ></v-select>
</template>

<script setup>
  defineProps({
    items: { type: Array, required: true },
    error: { type: String, default: '' },
    hint: { type: String, default: '' },
    label: { type: String, default: '' },
    density: { type: String, default: 'default' },
    color: { type: String, default: 'primary' },
  });

  const model = defineModel({
    required: true,
    type: [Object, String, Number],
  });
</script>

<script>
  export default {
    inheritAttrs: false,
  };
</script>
