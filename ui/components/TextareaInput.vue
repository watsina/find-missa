<template>
  <v-textarea
    ref="input"
    v-bind="{ ...$attrs }"
    v-model="model"
    :color="color"
    :density="density"
    :error-messages="error"
    hide-details="auto"
    :hint="hint"
    :label="label"
    :placeholder="placeholder"
    :rows="rows"
  ></v-textarea>
</template>

<script setup>
  import { onMounted, ref } from 'vue';
  import { v4 as uuid } from 'uuid';

  defineProps({
    id: {
      type: String,
      default() {
        return `textarea-${uuid()}`;
      },
    },
    error: { type: [String, Array], default: '' },
    hint: { type: String, default: '' },
    label: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    density: { type: String, default: 'default' },
    color: { type: String, default: 'primary' },
    rows: { type: Number, default: 3 },
  });

  const model = defineModel({ type: [String, Number], required: true });

  const input = ref(null);

  onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
      input.value.focus();
    }
  });

  defineExpose({ focus: () => input.value.focus() });
</script>

<script>
  export default {
    inheritAttrs: false,
  };
</script>
