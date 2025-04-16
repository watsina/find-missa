<template>
  <v-text-field
    v-bind="$attrs"
    active
    :error-messages="error"
    :prepend-inner-icon="mdiClockTimeFourOutline"
    readonly
  >
    <template v-slot:prepend-inner>
      <v-chip
        v-for="(time, index) in model"
        :key="time"
        class="tw-mx-1"
        closable
        color="primary"
        rounded="xl"
        size="x-small"
        @click:close="removeTime(index)"
      >
        {{ time }}
      </v-chip>
    </template>
    <v-menu
      v-model="dialog"
      activator="parent"
      :close-on-content-click="false"
      transition="scale-transition"
    >
      <v-time-picker
        v-if="dialog"
        v-model="pickerTime"
        v-bind="$attrs"
        color="primary"
        :format="format"
        full-width
        scrollable
      >
        <template #actions>
          <v-btn color="primary" variant="text" @click="((dialog = false), addTime())">Done</v-btn>
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
  const pickerTime = ref(null);
  const model = defineModel({
    type: Array,
    required: true,
  });
  function addTime() {
    if (pickerTime.value && !model.value.includes(pickerTime.value)) {
      model.value.push(pickerTime.value); // Add new time to array
    }
    pickerTime.value = null; // Reset picker
    dialog.value = false; // Close menu
  }
  function removeTime(index) {
    model.value?.splice(index, 1); // Remove time at index
  }
</script>
