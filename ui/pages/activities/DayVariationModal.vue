<script setup>
  import { computed, watch } from 'vue';
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import SelectInput from '@/components/SelectInput.vue';
  import TextInput from '@/components/TextInput.vue';
  import TimeInput from '@/components/TimeInput.vue';

  const props = defineProps({
    schedule: { type: Object, required: true },
    variation: { type: Object, default: null },
    existingDays: { type: Array, default: () => [] },
  });

  const showModal = defineModel('showModal', { type: Boolean });
  const emit = defineEmits(['saved']);

  const allDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
  const weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

  const form = useForm({
    day_of_week: props.variation?.day_of_week || '',
    start_time: props.variation?.start_time || '',
    end_time: props.variation?.end_time || '',
    language: props.variation?.language || '',
    is_skipped: props.variation?.is_skipped || false,
  });

  const availableDays = computed(() => {
    const pattern = props.schedule?.day_pattern;
    let days = [];

    if (pattern === 'daily') {
      days = [...allDays];
    } else if (pattern === 'weekdays') {
      days = [...weekdays];
    } else if (pattern === 'specific') {
      days = props.schedule?.days_of_week || [];
    } else {
      return [];
    }

    // Filter out days that already have variations (unless editing that day)
    const editingDay = props.variation?.day_of_week;
    return days.filter((day) => day === editingDay || !props.existingDays.includes(day));
  });

  const supportsVariations = computed(() => {
    const pattern = props.schedule?.day_pattern;
    return ['daily', 'weekdays', 'specific'].includes(pattern);
  });

  const isEditing = computed(() => !!props.variation?.id || !!props.variation?.day_of_week);

  watch(
    () => props.variation,
    (newValue) => {
      if (newValue) {
        form.day_of_week = newValue.day_of_week || '';
        form.start_time = newValue.start_time || '';
        form.end_time = newValue.end_time || '';
        form.language = newValue.language || '';
        form.is_skipped = newValue.is_skipped || false;
      } else {
        form.reset();
      }
    },
    { deep: true, immediate: true },
  );

  watch(showModal, (newValue) => {
    if (!newValue) {
      form.clearErrors();
    }
  });

  const submit = () => {
    if (!form.day_of_week) {
      return notify.error('Please select a day of the week');
    }

    // At least one override field must be set (unless skipping)
    if (!form.is_skipped && !form.start_time && !form.end_time && !form.language) {
      return notify.error('Please provide at least one variation (time, language, or skip)');
    }

    const variationData = {
      day_of_week: form.day_of_week,
      start_time: form.start_time || null,
      end_time: form.end_time || null,
      language: form.language || null,
      is_skipped: form.is_skipped,
    };

    emit('saved', variationData);
    closeModal();
  };

  const closeModal = () => {
    form.reset();
    showModal.value = false;
  };
</script>

<template>
  <div>
    <dialog-modal max-width="500" :show="showModal" @close="closeModal">
      <template #title>
        {{ isEditing ? 'Edit Day Variation' : 'Add Day Variation' }}
      </template>

      <template #content>
        <div v-if="!supportsVariations" class="tw:text-gray-600">
          This schedule pattern does not support day variations.
        </div>

        <form v-else>
          <div class="tw:mt-4">
            <SelectInput
              v-model="form.day_of_week"
              class="tw:block tw:w-full tw:mt-1"
              :disabled="isEditing"
              :error="form.errors.day_of_week"
              :items="availableDays"
              label="Day of Week*"
              required
            />
          </div>

          <v-divider class="tw:my-4" />

          <div class="tw:mb-2 tw:text-sm tw:text-gray-600">
            Override settings for this day (leave blank to use base schedule values):
          </div>

          <div class="tw:mt-4">
            <v-checkbox
              v-model="form.is_skipped"
              color="error"
              density="compact"
              hide-details
              label="Skip this day (no activity)"
            />
          </div>

          <template v-if="!form.is_skipped">
            <div class="tw:mt-4">
              <TimeInput
                v-model="form.start_time"
                :error="form.errors.start_time"
                label="Override Start Time"
                :placeholder="schedule?.start_time || 'Same as base schedule'"
              />
              <div class="tw:text-xs tw:text-gray-500 tw:mt-1">
                Base schedule: {{ schedule?.start_time || 'Not set' }}
              </div>
            </div>

            <div class="tw:mt-4">
              <TimeInput
                v-model="form.end_time"
                :error="form.errors.end_time"
                label="Override End Time"
                :placeholder="schedule?.end_time || 'Same as base schedule'"
              />
              <div class="tw:text-xs tw:text-gray-500 tw:mt-1">
                Base schedule: {{ schedule?.end_time || 'Not set' }}
              </div>
            </div>

            <div class="tw:mt-4">
              <TextInput
                v-model="form.language"
                class="tw:block tw:w-full tw:mt-1"
                density="default"
                :error="form.errors.language"
                label="Override Language"
                :placeholder="schedule?.language || 'Same as base schedule'"
              />
              <div class="tw:text-xs tw:text-gray-500 tw:mt-1">
                Base schedule: {{ schedule?.language || 'Not set' }}
              </div>
            </div>
          </template>
        </form>
      </template>

      <template #footer>
        <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>

        <PrimaryButton class="tw:ml-3" :disabled="!supportsVariations" @click="submit">
          {{ isEditing ? 'Update Variation' : 'Add Variation' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>
