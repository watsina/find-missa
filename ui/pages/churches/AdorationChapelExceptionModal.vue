<script setup>
  import { computed, ref, watch } from 'vue';
  import { useForm } from '@/plugins';
  import { notify, useDayjs } from '@/utils';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import DateInput from '@/components/DateInput.vue';
  import SelectInput from '@/components/SelectInput.vue';
  import TimeInput from '@/components/TimeInput.vue';
  import { useDate } from 'vuetify';

  const props = defineProps({
    exception: { type: Object, default: null },
    churchId: { type: [String, Number], required: true },
  });

  const showModal = defineModel('showModal', { type: Boolean });
  const emit = defineEmits(['created', 'updated']);

  const parser = useDate();
  const currentDate = new Date().toLocaleDateString('en-US');
  const start_date = ref(parser.date(props.exception?.start_date || currentDate));
  const end_date = ref(parser.date(props.exception?.end_date || currentDate));

  const dayPatternOptions = [
    { title: 'Daily', value: 'daily' },
    { title: 'Weekdays (Mon-Fri)', value: 'weekdays' },
    { title: 'Saturday', value: 'saturday' },
    { title: 'Sunday', value: 'sunday' },
    { title: 'Specific Days', value: 'specific' },
  ];

  const daysOfWeekOptions = [
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday',
  ];

  const form = useForm({
    reason: props.exception?.reason || '',
    start_date: props.exception?.start_date || null,
    end_date: props.exception?.end_date || null,
    day_pattern: props.exception?.day_pattern || 'daily',
    days_of_week: props.exception?.days_of_week || [],
    opens_at: props.exception?.opens_at || null,
    closes_at: props.exception?.closes_at || null,
    is_open_24_hours: props.exception?.is_open_24_hours || false,
    is_closed: props.exception?.is_closed || false,
  });

  const requiresDaysOfWeek = computed(() => form.day_pattern === 'specific');

  const showTimeFields = computed(() => !form.is_open_24_hours && !form.is_closed);

  const dayPatternLabel = computed(() => {
    if (form.day_pattern === 'specific' && form.days_of_week?.length > 0) {
      return `Every ${form.days_of_week.join(', ')}`;
    }
    if (form.day_pattern === 'weekdays') return 'Every Monday–Friday';
    if (form.day_pattern === 'daily') return 'Every day';
    if (form.day_pattern === 'saturday') return 'Every Saturday';
    if (form.day_pattern === 'sunday') return 'Every Sunday';
    return null;
  });

  watch(
    () => props.exception,
    (newValue) => {
      if (newValue?.id) {
        form.reason = newValue?.reason || '';
        form.start_date = newValue?.start_date || null;
        form.end_date = newValue?.end_date || null;
        form.day_pattern = newValue?.day_pattern || 'daily';
        form.days_of_week = newValue?.days_of_week || [];
        form.opens_at = newValue?.opens_at || null;
        form.closes_at = newValue?.closes_at || null;
        form.is_open_24_hours = !!newValue?.is_open_24_hours;
        form.is_closed = !!newValue?.is_closed;
        start_date.value = parser.date(newValue?.start_date || currentDate);
        end_date.value = parser.date(newValue?.end_date || currentDate);
      }
    },
    { deep: true },
  );

  watch(showModal, (newValue) => {
    if (!newValue) {
      form.reset();
      form.clearErrors();
    }
  });

  // Reset related fields when day pattern changes
  watch(
    () => form.day_pattern,
    (newPattern) => {
      if (newPattern !== 'specific') {
        form.days_of_week = [];
      }
    },
  );

  const isEditing = computed(() => !!props.exception?.id);

  const submit = () => {
    const url = isEditing.value
      ? `/api/v1/churches/${props.churchId}/adoration-chapel/exceptions/${props.exception?.id}`
      : `/api/v1/churches/${props.churchId}/adoration-chapel/exceptions`;

    const method = isEditing.value ? 'put' : 'post';

    form.start_date = useDayjs(start_date.value).format('YYYY-MM-DD');
    form.end_date = useDayjs(end_date.value).format('YYYY-MM-DD');

    return form[method](url, {
      onSuccess: ({ data }) => {
        const message = isEditing.value
          ? 'Chapel Exception was updated successfully.'
          : 'Chapel Exception was added successfully.';
        notify.success(message);
        closeModal(data);
      },
      onError: () => notify.error('There was an error saving the information.'),
    });
  };

  const closeModal = (data = null) => {
    form.reset();
    if (data?.id && !isEditing.value) emit('created', data);
    if (data?.id && isEditing.value) emit('updated', data);

    showModal.value = false;
  };
</script>

<template>
  <div>
    <dialog-modal max-width="600" :show="showModal" @close="closeModal">
      <template #title>
        {{ isEditing ? 'Update Chapel Exception' : 'New Chapel Exception' }}
      </template>

      <template #content>
        <form>
          <div class="tw:mt-4">
            <TextInput
              v-model="form.reason"
              class="tw:block tw:w-full tw:mt-1"
              :error="form.errors.reason"
              label="Reason"
              placeholder="e.g. Holiday closure, Easter hours"
            />
          </div>

          <div class="tw:grid tw:grid-cols-2 tw:gap-4 tw:mt-4">
            <DateInput
              v-model="start_date"
              class="tw:block tw:w-full"
              :error="form.errors.start_date"
              label="Start Date*"
              landscape
              required
            />

            <DateInput
              v-model="end_date"
              class="tw:block tw:w-full"
              :error="form.errors.end_date"
              label="End Date*"
              landscape
              required
            />
          </div>

          <!-- Schedule Override Fields -->
          <v-divider class="tw:my-6" />
          <div class="tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">Schedule Override</div>
          <p class="tw:text-xs tw:text-gray-500 tw:mb-4">
            Define the chapel hours during this exception period. This fully overrides the regular
            schedule on matching days.
          </p>

          <div class="tw:mt-4">
            <SelectInput
              v-model="form.day_pattern"
              class="tw:block tw:w-full tw:mt-1"
              :error="form.errors.day_pattern"
              :items="dayPatternOptions"
              label="Day Pattern*"
              required
            />
          </div>

          <!-- Specific Days Selection -->
          <div v-if="requiresDaysOfWeek" class="tw:mt-4">
            <v-select
              v-model="form.days_of_week"
              chips
              class="tw:block tw:w-full tw:mt-1"
              :error-messages="form.errors.days_of_week"
              :items="daysOfWeekOptions"
              label="Days of Week*"
              multiple
              variant="filled"
            />
          </div>

          <!-- Pattern Preview -->
          <div v-if="dayPatternLabel" class="tw:mt-4">
            <v-alert color="info" density="compact" variant="tonal">
              <span class="tw:text-sm">{{ dayPatternLabel }}</span>
            </v-alert>
          </div>

          <div class="tw:flex tw:gap-4 tw:mt-4">
            <v-checkbox
              v-model="form.is_open_24_hours"
              density="compact"
              :error-messages="form.errors.is_open_24_hours"
              hide-details="auto"
              label="Open 24 hours"
              @update:model-value="
                (val) => {
                  if (val) form.is_closed = false;
                }
              "
            />
            <v-checkbox
              v-model="form.is_closed"
              density="compact"
              :error-messages="form.errors.is_closed"
              hide-details="auto"
              label="Closed"
              @update:model-value="
                (val) => {
                  if (val) form.is_open_24_hours = false;
                }
              "
            />
          </div>

          <div v-if="showTimeFields" class="tw:grid tw:grid-cols-2 tw:gap-4 tw:mt-4">
            <TimeInput
              v-model="form.opens_at"
              :error="form.errors.opens_at"
              label="Opens At*"
              required
            />
            <TimeInput
              v-model="form.closes_at"
              :error="form.errors.closes_at"
              label="Closes At*"
              required
            />
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
          {{ isEditing ? 'Update Exception' : 'Add Exception' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>
