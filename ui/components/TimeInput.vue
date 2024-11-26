<template>
  <v-text-field
    v-model="model"
    :active="dialog"
    :density="density"
    :error-messages="error"
    :focus="dialog"
    hide-details="auto"
    :hint="hint"
    :label="label"
    :placeholder="placeholder"
    :prepend-icon="mdiClockTimeFourOutline"
    readonly
  >
    <v-menu
      v-model="dialog"
      activator="parent"
      :close-on-content-click="false"
      transition="scale-transition"
    >
      <v-time-picker
        v-if="dialog"
        v-bind="{ ...$attrs }"
        v-model="model"
        :color="color"
        :format="format"
        full-width
        scrollable
      >
        <template #actions>
          <v-btn color="primary" text @click="dialog = false">Done</v-btn>
        </template>
      </v-time-picker>
    </v-menu>
  </v-text-field>
</template>

<script setup>
  import { ref } from 'vue';
  import { mdiClockTimeFourOutline } from '@mdi/js';

  defineProps({
    type: { type: String, default: 'text' },
    format: { type: String, default: '24hr' },
    error: { type: [String, Array], default: '' },
    hint: { type: String, default: '' },
    label: { type: String, default: '' },
    placeholder: { type: String, default: '' },
    density: { type: String, default: 'compact' },
    color: { type: String, default: 'primary' },
  });

  const dialog = ref(false);
  const model = defineModel({
    type: [String, Number],
    required: true,
  });
</script>

<script>
  export default {
    inheritAttrs: false,
  };
</script>
