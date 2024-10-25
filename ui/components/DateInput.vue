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
    :prepend-icon="mdiCalendarRange"
    readonly
  >
    <v-menu
      v-model="dialog"
      activator="parent"
      :close-on-content-click="false"
      transition="scale-transition"
    >
      <v-date-picker v-bind="{ ...$attrs }" v-model="model" hide-details="auto">
        <template #actions>
          <v-btn color="primary" text @click="dialog = false">Done</v-btn>
        </template>
      </v-date-picker>
    </v-menu>
  </v-text-field>
</template>

<script setup>
  import { ref } from 'vue';
  import { mdiCalendarRange } from '@mdi/js';

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
  const dialog = ref(false);
</script>

<script>
  export default {
    inheritAttrs: false,
  };
</script>
