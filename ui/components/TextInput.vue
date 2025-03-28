<template>
  <v-text-field
    v-bind="$attrs"
    v-model="model"
    :error-messages="error"
    :label="label"
    :placeholder="placeholder"
    :type="type"
  ></v-text-field>
</template>

<script setup>
  defineProps({
    type: { type: String, default: 'text' },
    error: { type: [String, Array], default: '' },
    label: { type: String, default: '' },
    placeholder: { type: String, default: '' },
  });

  const model = defineModel({
    type: [String, Number, null],
    required: true,
  });
</script>

<script>
  export default {
    inheritAttrs: false,
  };
</script>
