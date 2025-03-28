<template>
  <v-text-field
    v-bind="$attrs"
    v-model="model"
    :active="dialog"
    :error-messages="error"
    :focus="dialog"
    :prepend-inner-icon="mdiClockTimeFourOutline"
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
        v-bind="$attrs"
        v-model="model"
        color="primary"
        :format="format"
        full-width
        scrollable
      >
        <template #actions>
          <v-btn color="primary" variant="text" @click="dialog = false">Done</v-btn>
        </template>
      </v-time-picker>
    </v-menu>
  </v-text-field>
</template>

<script setup>
  import { ref } from 'vue';
  import { mdiClockTimeFourOutline } from '@mdi/js';

  defineProps({
    format: { type: String, default: '24hr' },
    error: { type: [String, Array], default: '' },
  });

  const dialog = ref(false);
  const model = defineModel({
    type: [String, Number],
    required: true,
  });
</script>
