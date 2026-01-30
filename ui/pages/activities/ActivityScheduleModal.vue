<script setup>
  import { computed, onMounted, ref, watch } from 'vue';
  import { useForm } from '@/plugins';
  import { notify, useDayjs } from '@/utils';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import SelectInput from '@/components/SelectInput.vue';
  import TextInput from '@/components/TextInput.vue';
  import TimeInput from '@/components/TimeInput.vue';
  import DateInput from '@/components/DateInput.vue';
  import { useDate } from 'vuetify/lib/framework.mjs';
  import { http } from '@/plugins';

  const props = defineProps({
    schedule: { type: Object, default: null },
    church: { type: Object, required: true },
  });

  const showModal = defineModel('showModal', { type: Boolean });
  const emit = defineEmits(['created', 'updated']);
  const parser = useDate();
  const currentDate = new Date().toLocaleDateString('en-US');
  const date = ref(parser.date(currentDate));

  // Schedule periods
  const schedulePeriods = ref([]);
  const loadingSchedulePeriods = ref(false);
  const effectiveFromDate = ref(null);
  const effectiveUntilDate = ref(null);

  const activityTypes = [
    { title: 'Mass', value: 'mass' },
    { title: 'Confession', value: 'confession' },
    { title: 'Adoration', value: 'adoration' },
  ];

  const activityTypeDescriptions = [
    { title: 'Mass', value: 'mass' },
    { title: 'Confession', value: 'confession' },
    { title: 'Adoration', value: 'adoration' },
    { title: 'Benediction', value: 'benediction' },
    { title: 'Traditional Latin Mass', value: 'traditional-latin-mass' },
    { title: 'Anticipation Mass', value: 'anticipation-mass' },
  ];

  const dayPatterns = [
    { title: 'Daily', value: 'daily' },
    { title: 'Weekdays (Mon-Fri)', value: 'weekdays' },
    { title: 'Saturday', value: 'saturday' },
    { title: 'Sunday', value: 'sunday' },
    { title: 'Specific Days', value: 'specific' },
    { title: 'Monthly (Specific Week)', value: 'monthly-nth' },
  ];

  const weekOfMonthOptions = [
    { title: 'First', value: 1 },
    { title: 'Second', value: 2 },
    { title: 'Third', value: 3 },
    { title: 'Fourth', value: 4 },
    { title: 'Last', value: 5 },
  ];

  const daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

  const form = useForm({
    type: props.schedule?.type || '',
    activity_type_description: props.schedule?.activity_type_description || '',
    start_time: props.schedule?.start_time || '',
    end_time: props.schedule?.end_time || '',
    language: props.schedule?.language || '',
    day_pattern: props.schedule?.day_pattern || '',
    day_of_week: props.schedule?.day_of_week || '',
    days_of_week: props.schedule?.days_of_week || [],
    church_id: props.church?.id || '',
    schedule_period_id: props.schedule?.schedule_period_id || null,
    effective_from: props.schedule?.effective_from || null,
    effective_until: props.schedule?.effective_until || null,
    week_of_month: props.schedule?.week_of_month || null,
    has_day_variations: props.schedule?.has_day_variations || false,
    day_details: props.schedule?.day_details || [],
    tags: props.schedule?.tags || [],
  });

  // Computed properties for conditional display
  const requiresDaysOfWeek = computed(() => {
    return form.day_pattern === 'specific';
  });

  const requiresMonthlyFields = computed(() => {
    return form.day_pattern === 'monthly-nth';
  });

  const hasDayVariations = computed(() => {
    return form.has_day_variations;
  });

  // Generate helpful label for the occurrence pattern
  const dayPatternLabel = computed(() => {
    if (form.day_pattern === 'monthly-nth' && form.week_of_month && form.day_of_week) {
      const weekLabel = weekOfMonthOptions.find((w) => w.value === form.week_of_month)?.title;
      return `${weekLabel} ${form.day_of_week} of every month`;
    } else if (form.day_pattern === 'specific' && form.days_of_week.length > 0) {
      return `Every ${form.days_of_week.join(', ')}`;
    } else if (form.day_pattern === 'weekdays') {
      return 'Every Monday, Tuesday, Wednesday, Thursday, Friday';
    } else if (form.day_pattern === 'daily') {
      return 'Every day';
    } else if (form.day_pattern === 'saturday') {
      return 'Every Saturday';
    } else if (form.day_pattern === 'sunday') {
      return 'Every Sunday';
    }
    return null;
  });

  const schedulePeriodItems = computed(() => {
    return [
      { title: 'No Schedule Period (Always Active)', value: null },
      ...schedulePeriods.value.map((period) => ({
        title: `${period.name} (${period.formatted_date_range})`,
        value: period.id,
      })),
    ];
  });

  async function fetchSchedulePeriods() {
    loadingSchedulePeriods.value = true;
    try {
      const response = await http.get('/api/v1/schedule-periods', {
        params: { per_page: 100, is_active: true },
      });
      schedulePeriods.value = response.data.data;
    } catch (error) {
      console.error('Failed to fetch schedule periods:', error);
    } finally {
      loadingSchedulePeriods.value = false;
    }
  }

  onMounted(() => {
    fetchSchedulePeriods();
  });

  watch(
    () => props.schedule,
    (newValue) => {
      if (newValue?.id) {
        form.type = newValue?.type || '';
        form.activity_type_description = newValue?.activity_type_description || '';
        form.start_time = newValue?.start_time || '';
        form.end_time = newValue?.end_time || '';
        form.language = newValue?.language || '';
        form.day_pattern = newValue?.day_pattern || '';
        form.days_of_week = newValue?.days_of_week || [];
        form.day_of_week = newValue?.day_of_week || '';
        form.church_id = newValue?.church_id || '';
        form.schedule_period_id = newValue?.schedule_period_id || null;
        form.effective_from = newValue?.effective_from || null;
        form.effective_until = newValue?.effective_until || null;
        form.week_of_month = newValue?.week_of_month || null;
        form.has_day_variations = newValue?.has_day_variations || false;
        form.day_details = newValue?.day_details || [];
        form.tags = newValue?.tags || [];

        effectiveFromDate.value = newValue?.effective_from
          ? parser.date(newValue.effective_from)
          : null;
        effectiveUntilDate.value = newValue?.effective_until
          ? parser.date(newValue.effective_until)
          : null;
      }
    },
    { deep: true },
  );

  watch(showModal, (newValue) => {
    if (!newValue) {
      form.clearErrors();
    }
  });

  // Reset related fields when day pattern changes
  watch(
    () => form.day_pattern,
    (newPattern) => {
      // Clear fields that don't apply to the new pattern
      if (newPattern !== 'specific') {
        form.days_of_week = [];
      }
      if (newPattern !== 'monthly-nth') {
        form.week_of_month = null;
        form.day_of_week = '';
      }
    },
  );

  const isEditing = computed(() => !!props.schedule?.id);

  const submit = () => {
    if (!props.church?.id) {
      return notify.error('Church details not provided');
    }

    const url = isEditing.value
      ? `/api/v1/activity-schedules/${props.schedule?.id}`
      : `/api/v1/activity-schedules`;

    const method = isEditing.value ? 'put' : 'post';

    // Handle effective date formatting
    form.effective_from = effectiveFromDate.value
      ? useDayjs(effectiveFromDate.value).format('YYYY-MM-DD')
      : null;
    form.effective_until = effectiveUntilDate.value
      ? useDayjs(effectiveUntilDate.value).format('YYYY-MM-DD')
      : null;

    form.church_id = props.church.id;

    return form[method](url, {
      onSuccess: ({ data }) => {
        const message = isEditing.value
          ? 'Schedule was updated successfully'
          : 'Schedule was added successfully.';
        notify.success(message);
        closeModal(data);
      },
      onError: () => notify.error('There was an error saving the schedule.'),
    });
  };

  const closeModal = (data = null) => {
    form.reset();
    effectiveFromDate.value = null;
    effectiveUntilDate.value = null;
    if ((data?.id || data?.length) && !isEditing.value) emit('created', data);
    if (data?.id && isEditing.value) emit('updated', data);

    showModal.value = false;
  };
</script>

<template>
  <div>
    <dialog-modal max-width="600" :show="showModal" @close="closeModal">
      <template #title> {{ isEditing ? 'Update Schedule' : 'New Schedule' }} </template>

      <template #content>
        <form>
          <div class="tw:mt-4">
            <v-text-field
              class="tw:block tw:w-full tw:mt-1 tw:capitalize"
              :error="form.errors.church_id"
              label="Church*"
              :model-value="`${church?.name} - ${church?.city}`"
              readonly
              variant="solo"
            />
          </div>

          <div class="tw:mt-4">
            <SelectInput
              v-model="form.type"
              class="tw:block tw:w-full tw:mt-1 tw:capitalize"
              :error="form.errors.type"
              :items="activityTypes"
              label="Activity Type*"
              required
            />
          </div>

          <div class="tw:mt-4">
            <v-autocomplete
              v-model="form.activity_type_description"
              class="tw:block tw:w-full tw:mt-1 tw:capitalize"
              :error="form.errors.activity_type_description"
              hide-details="auto"
              :items="activityTypeDescriptions"
              label="Activity Type Description*"
              required
            />
          </div>

          <div class="tw:mt-4">
            <SelectInput
              v-model="form.day_pattern"
              class="tw:block tw:w-full tw:mt-1 tw:capitalize"
              :error="form.errors.day_pattern"
              :items="dayPatterns"
              label="Frequency*"
              required
            />
          </div>

          <!-- Specific Days Selection -->
          <div v-if="requiresDaysOfWeek" class="tw:mt-4">
            <SelectInput
              v-model="form.days_of_week"
              chips
              class="tw:block tw:w-full tw:mt-1"
              :error="form.errors.days_of_week"
              :items="daysOfWeek"
              label="Days of Week"
              multiple
              placeholder="Select days"
              required
            />
          </div>

          <!-- Monthly Specific Week Selection -->
          <div v-if="requiresMonthlyFields" class="tw:mt-4 tw:grid tw:grid-cols-2 tw:gap-4">
            <SelectInput
              v-model="form.week_of_month"
              class="tw:block tw:w-full"
              :error="form.errors.week_of_month"
              :items="weekOfMonthOptions"
              label="Week of Month"
              placeholder="First"
              required
            />
            <SelectInput
              v-model="form.day_of_week"
              class="tw:block tw:w-full"
              :error="form.errors.day_of_week"
              :items="daysOfWeek"
              label="Day of Week"
              placeholder="Sunday"
              required
            />
          </div>

          <!-- Pattern Preview -->
          <div v-if="dayPatternLabel" class="tw:mt-4">
            <v-alert color="info" density="compact" variant="tonal">
              <span class="tw:text-sm">{{ dayPatternLabel }}</span>
            </v-alert>
          </div>

          <div class="tw:mt-4">
            <TimeInput
              v-model="form.start_time"
              :error="form.errors.start_time"
              label="Start Time"
              required
            />
          </div>

          <div class="tw:mt-4">
            <TimeInput
              v-model="form.end_time"
              :error="form.errors.end_time"
              label="End Time (Optional)"
            />
          </div>

          <div class="tw:mt-4">
            <TextInput
              v-model="form.language"
              autofocus
              class="tw:block tw:w-full tw:mt-1"
              density="default"
              :error="form.errors.language"
              label="Language"
              placeholder="English"
              required
            />
          </div>

          <!-- Schedule Period Section -->
          <v-divider class="tw:my-6" />
          <div class="tw:text-sm tw:text-gray-600 tw:mb-2">Schedule Period (Optional)</div>

          <div class="tw:mt-4">
            <SelectInput
              v-model="form.schedule_period_id"
              class="tw:block tw:w-full tw:mt-1"
              clearable
              :error="form.errors.schedule_period_id"
              :items="schedulePeriodItems"
              label="Schedule Period"
              :loading="loadingSchedulePeriods"
            />
          </div>

          <div class="tw:grid tw:grid-cols-2 tw:gap-4 tw:mt-4">
            <DateInput
              v-model="effectiveFromDate"
              class="tw:block tw:w-full"
              clearable
              :error="form.errors.effective_from"
              label="Effective From"
            />
            <DateInput
              v-model="effectiveUntilDate"
              class="tw:block tw:w-full"
              clearable
              :error="form.errors.effective_until"
              label="Effective Until"
            />
          </div>

          <div class="tw:text-xs tw:text-gray-500 tw:mt-2">
            Leave dates empty for the activity to always be active.
          </div>
        </form>
      </template>

      <template #footer>
        <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>

        <PrimaryButton
          class="tw:ml-3 tw:capitalize"
          :disabled="form.processing"
          :loading="form.processing"
          @click="submit"
        >
          {{ isEditing ? 'Update Schedule' : 'Add Schedule' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>

<style lang="scss" scoped></style>
