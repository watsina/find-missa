<script setup>
  import { computed } from 'vue';

  const emit = defineEmits(['update:checked']);

  const props = defineProps({
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
  const proxyChecked = computed({
    get() {
      return props.checked;
    },

    set(val) {
      emit('update:checked', val);
    },
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
