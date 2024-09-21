<template>
  <v-file-input
    ref="input"
    v-bind="{ ...$attrs }"
    v-model="model"
    chips
    :color="color"
    :density="density"
    :error-messages="error"
    hide-details="auto"
    :hint="hint"
    :label="label"
    :placeholder="placeholder"
    show-size
  ></v-file-input>
</template>

<script setup>
  import { onMounted, ref } from 'vue';
  import { v4 as uuid } from 'uuid';

  defineProps({
    id: {
      type: String,
      default() {
        return `text-input-${uuid()}`;
      },
    },
    error: { type: [String, Array], default: '' },
    hint: { type: String, default: '' },
    label: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    density: { type: String, default: 'default' },
    color: { type: String, default: 'primary' },
  });

  const input = ref(null);
  const model = defineModel({
    type: Object,
    default: null,
  });

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
