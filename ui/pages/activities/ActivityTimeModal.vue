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
  import MultiTimeInput from '@/components/MultiTimeInput.vue';
  import DateInput from '@/components/DateInput.vue';
  import { useDate } from 'vuetify/lib/framework.mjs';
  import { http } from '@/plugins';

  const props = defineProps({
    activity: { type: Object, default: null },
    church: { type: Object, required: true },
  });

  const showModal = defineModel('showModal', { type: Boolean });
  const emit = defineEmits(['created', 'updated']);
  const parser = useDate();
  const currentDate = new Date().toLocaleDateString('en-US');
  const date = ref(parser.date(currentDate));
  const isAddingBulk = ref(false);

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
    { title: 'Anticipation Mass', value: 'anticipation-mass' },
    { title: 'Mass of the Lords Supper', value: 'mass-of-the-lords-supper' },
    { title: 'Good Friday Service', value: 'good-friday-service' },
    { title: 'Easter Vigil Mass', value: 'easter-vigil-mass' },
    { title: 'Easter Mass', value: 'easter-mass' },
    { title: 'Easter Monday Mass', value: 'easter-monday-mass' },
    { title: 'Traditional Latin Mass', value: 'traditional-latin-mass' },
    { title: 'Confession', value: 'confession' },
    { title: 'Adoration', value: 'adoration' },
  ];

  const occurrences = [
    { title: 'Weekly', value: 'weekly' },
    { title: 'Daily', value: 'daily' },
    { title: 'One Time', value: 'one-time' },
  ];

  const daysOfWeek = [
    'Sunday',
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday',
  ];

  const form = useForm({
    type: props.activity?.type || '',
    activity_type_description: props.activity?.activity_type_description || '',
    start_time: props.activity?.start_time || '',
    starting_times: [], // for bulk inserts
    end_time: props.activity?.end_time || '',
    language: props.activity?.language || '',
    occurrence: props.activity?.occurrence || '',
    date: props.activity?.date || null,
    day_of_week: props.activity?.day_of_week || '',
    days_of_week: [],
    church_id: props.church?.id || '',
    schedule_period_id: props.activity?.schedule_period_id || null,
    effective_from: props.activity?.effective_from || null,
    effective_until: props.activity?.effective_until || null,
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
    () => props.activity,
    (newValue) => {
      if (newValue?.id) {
        form.type = newValue?.type || '';
        form.activity_type_description = newValue?.activity_type_description || '';
        form.start_time = newValue?.start_time || '';
        form.end_time = newValue?.end_time || '';
        form.language = newValue?.language || '';
        form.occurrence = newValue?.occurrence || '';
        form.date = newValue?.date || null;
        form.day_of_week = newValue?.day_of_week || '';
        form.church_id = newValue?.church_id || '';
        form.schedule_period_id = newValue?.schedule_period_id || null;
        form.effective_from = newValue?.effective_from || null;
        form.effective_until = newValue?.effective_until || null;

        date.value = parser.date(newValue?.date || currentDate);
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

  const isEditing = computed(() => !!props.activity?.id);

  const submit = () => {
    if (!props.church?.id) {
      return notify.error('Church details not provided');
    }

    const url = isEditing.value
      ? `/api/v1/activity-times/${props.activity?.id}`
      : `/api/v1/activity-times`;

    const method = isEditing.value ? 'put' : 'post';

    // some clean up to ensure that the data matches what we expect
    if (form.occurrence === 'weekly') {
      form.date = null;
      if (!isEditing.value) {
        form.day_of_week = null;
      }
    } else if (form.occurrence === 'daily') {
      form.day_of_week = '';
      form.date = null;
    } else if (form.occurrence === 'one-time') {
      form.day_of_week = '';
      form.date = useDayjs(date.value).format('YYYY-MM-DD');
    }

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
          ? 'Activity was updated successfully'
          : 'Activity was added successfully.';
        notify.success(message);
        closeModal(data);
      },
      onError: () => notify.error('There was an error saving the information.'),
    });
  };

  const closeModal = (data = null) => {
    form.reset();
    effectiveFromDate.value = null;
    effectiveUntilDate.value = null;
    if ((data?.id || data?.length) && !isEditing.value) emit('created', data);
    if (data?.id && isEditing.value) emit('updated', data);

    if (isAddingBulk.value) {
      return;
    }
    showModal.value = false;
  };
</script>

<template>
  <div>
    <dialog-modal max-width="600" :show="showModal" @close="closeModal">
      <template #title> {{ isEditing ? 'Update Activity Time' : 'New Activity Time' }} </template>

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
            <SelectInput
              v-model="form.activity_type_description"
              class="tw:block tw:w-full tw:mt-1 tw:capitalize"
              :error="form.errors.activity_type_description"
              :items="activityTypeDescriptions"
              label="Activity Type Description*"
              required
            />
          </div>

          <div class="tw:mt-4">
            <SelectInput
              v-model="form.occurrence"
              class="tw:block tw:w-full tw:mt-1 tw:capitalize"
              :error="form.errors.occurrence"
              :items="occurrences"
              label="occurrence*"
              required
            />
          </div>

          <div v-if="form.occurrence === 'one-time'" class="tw:mt-4">
            <DateInput
              v-model="date"
              class="tw:block tw:w-full tw:mt-1"
              :error="form.errors.date"
              label="Date"
              landscape
              required
            />
          </div>

          <div v-if="form.occurrence === 'weekly'" class="tw:mt-4">
            <SelectInput
              v-if="isEditing"
              v-model="form.day_of_week"
              class="tw:block tw:w-full tw:mt-1"
              :error="form.errors.day_of_week"
              :items="daysOfWeek"
              label="Day of Week"
              placeholder="Sunday"
              required
            />
            <!-- if we are creating an activity we provide an option for adding them in bulk -->
            <SelectInput
              v-else
              v-model="form.days_of_week"
              chips
              class="tw:block tw:w-full tw:mt-1"
              :error="form.errors.days_of_week"
              :items="daysOfWeek"
              label="Days of Week"
              multiple
              placeholder="Sunday"
              required
            />
          </div>

          <div v-if="isAddingBulk" class="tw:mt-4">
            <MultiTimeInput
              v-model="form.starting_times"
              :error="form.errors.starting_times"
              label="Start Times"
              required
            />
          </div>

          <div v-if="!isAddingBulk" class="tw:mt-4">
            <TimeInput
              v-model="form.start_time"
              :error="form.errors.start_time"
              label="Start Time"
              required
            />
          </div>

          <div v-if="!isAddingBulk" class="tw:mt-4">
            <TimeInput
              v-model="form.end_time"
              :error="form.errors.end_time"
              label="End Time"
              required
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
              placeholder="Ruhororo"
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
            Leave dates empty for the activity to always be active. Use dates to specify a custom
            validity period.
          </div>

          <v-divider class="tw:my-6" />

          <div v-if="!isEditing" class="tw:mt-4">
            <v-checkbox
              v-model="isAddingBulk"
              color="primary"
              hide-details="auto"
              label="Add More Activities"
            ></v-checkbox>
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
          {{ isEditing ? 'Update Activity Time' : 'Add Activity Time' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>

<style lang="scss" scoped></style>
