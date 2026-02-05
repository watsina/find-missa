<script setup>
  import { mdiCheckCircle, mdiCircleOutline } from '@mdi/js';

  defineProps({
    steps: {
      type: Array,
      required: true,
      validator: (value) => value.every((step) => typeof step === 'string' || step.text),
    },
    title: {
      type: String,
      default: null,
    },
    numbered: {
      type: Boolean,
      default: true,
    },
  });
</script>

<template>
  <div class="tw:my-4">
    <h4 v-if="title" class="tw:text-base tw:font-semibold tw:text-gray-800 tw:mb-3">
      {{ title }}
    </h4>
    <v-timeline density="compact" side="end" truncate-line="both">
      <v-timeline-item
        v-for="(step, index) in steps"
        :key="index"
        :dot-color="step.completed ? 'success' : 'primary'"
        :icon="step.completed ? mdiCheckCircle : mdiCircleOutline"
        size="small"
      >
        <template #opposite>
          <span v-if="numbered" class="tw:text-sm tw:font-medium tw:text-gray-500">
            {{ index + 1 }}
          </span>
        </template>
        <div class="tw:pb-4">
          <p class="tw:text-sm tw:text-gray-700">
            {{ typeof step === 'string' ? step : step.text }}
          </p>
          <p v-if="step.description" class="tw:text-xs tw:text-gray-500 tw:mt-1">
            {{ step.description }}
          </p>
        </div>
      </v-timeline-item>
    </v-timeline>
  </div>
</template>
