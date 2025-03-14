<template>
  <v-text-field
    ref="input"
    v-bind="$attrs"
    v-model="model"
    :error-messages="error"
    :label="label"
    :placeholder="placeholder"
    :type="type"
  ></v-text-field>
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
    type: { type: String, default: 'text' },
    error: { type: [String, Array], default: '' },
    label: { type: String, default: '' },
    placeholder: { type: String, default: '' },
  });

  const input = ref(null);
  const model = defineModel({
    type: [String, Number, null],
    required: true,
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
