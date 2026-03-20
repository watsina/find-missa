<script setup>
  import { ref, watch } from 'vue';
  import { http } from '@/plugins';
  import { notify, useDayjs } from '@/utils';
  import { useDate } from 'vuetify';
  import {
    mdiPlus,
    mdiDeleteOutline,
    mdiPencilOutline,
    mdiClockOutline,
    mdiWeatherSunny,
    mdiCancel,
  } from '@mdi/js';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import SelectInput from '@/components/SelectInput.vue';
  import TimeInput from '@/components/TimeInput.vue';
  import DateInput from '@/components/DateInput.vue';

  const props = defineProps({
    churchId: { type: [String, Number], required: true },
    schedules: { type: Array, default: () => [] },
  });

  const showModal = defineModel('showModal', { type: Boolean });
  const emit = defineEmits(['updated']);

  const parser = useDate();
  const processing = ref(false);
  const localSchedules = ref([]);
  const errors = ref({});

  // Schedule entry being edited inline
  const showEntryForm = ref(false);
  const editingIndex = ref(null);

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

  const defaultEntry = () => ({
    day_pattern: 'daily',
    days_of_week: [],
    opens_at: null,
    closes_at: null,
    is_open_24_hours: false,
    is_closed: false,
    effective_from: null,
    effective_until: null,
    day_details: [],
  });

  const entryForm = ref(defaultEntry());
  const effectiveFromDate = ref(null);
  const effectiveUntilDate = ref(null);

  watch(
    () => props.schedules,
    (newValue) => {
      localSchedules.value = (newValue || []).map((s) => ({ ...s }));
    },
    { immediate: true, deep: true },
  );

  watch(showModal, (newValue) => {
    if (!newValue) {
      resetForm();
    } else {
      localSchedules.value = (props.schedules || []).map((s) => ({ ...s }));
    }
  });

  const resetForm = () => {
    showEntryForm.value = false;
    editingIndex.value = null;
    entryForm.value = defaultEntry();
    effectiveFromDate.value = null;
    effectiveUntilDate.value = null;
    errors.value = {};
  };

  const openAddForm = () => {
    entryForm.value = defaultEntry();
    effectiveFromDate.value = null;
    effectiveUntilDate.value = null;
    editingIndex.value = null;
    showEntryForm.value = true;
  };

  const openEditForm = (index) => {
    const schedule = localSchedules.value[index];
    entryForm.value = {
      day_pattern: schedule.day_pattern,
      days_of_week: schedule.days_of_week || [],
      opens_at: schedule.opens_at,
      closes_at: schedule.closes_at,
      is_open_24_hours: !!schedule.is_open_24_hours,
      is_closed: !!schedule.is_closed,
      effective_from: schedule.effective_from,
      effective_until: schedule.effective_until,
      day_details: schedule.day_details || [],
    };
    effectiveFromDate.value = schedule.effective_from ? parser.date(schedule.effective_from) : null;
    effectiveUntilDate.value = schedule.effective_until
      ? parser.date(schedule.effective_until)
      : null;
    editingIndex.value = index;
    showEntryForm.value = true;
  };

  const saveEntry = () => {
    const entry = { ...entryForm.value };

    // Format dates
    if (effectiveFromDate.value) {
      entry.effective_from = useDayjs(effectiveFromDate.value).format('YYYY-MM-DD');
    } else {
      entry.effective_from = null;
    }
    if (effectiveUntilDate.value) {
      entry.effective_until = useDayjs(effectiveUntilDate.value).format('YYYY-MM-DD');
    } else {
      entry.effective_until = null;
    }

    // Clear times when 24h or closed
    if (entry.is_open_24_hours) {
      entry.opens_at = null;
      entry.closes_at = null;
      entry.is_closed = false;
    }
    if (entry.is_closed) {
      entry.opens_at = null;
      entry.closes_at = null;
      entry.is_open_24_hours = false;
    }

    // Clear days_of_week if not specific
    if (entry.day_pattern !== 'specific') {
      entry.days_of_week = [];
    }

    if (editingIndex.value !== null) {
      localSchedules.value[editingIndex.value] = entry;
    } else {
      localSchedules.value.push(entry);
    }

    showEntryForm.value = false;
    editingIndex.value = null;
    entryForm.value = defaultEntry();
    effectiveFromDate.value = null;
    effectiveUntilDate.value = null;
  };

  const removeEntry = (index) => {
    localSchedules.value.splice(index, 1);
  };

  const cancelEntry = () => {
    showEntryForm.value = false;
    editingIndex.value = null;
    entryForm.value = defaultEntry();
    effectiveFromDate.value = null;
    effectiveUntilDate.value = null;
  };

  const formatPatternLabel = (schedule) => {
    if (schedule?.day_pattern === 'specific' && schedule?.days_of_week?.length > 0) {
      return `Every ${schedule.days_of_week.join(', ')}`;
    }
    if (schedule?.day_pattern === 'weekdays') return 'Every Monday\u2013Friday';
    if (schedule?.day_pattern === 'daily') return 'Every day';
    if (schedule?.day_pattern === 'saturday') return 'Every Saturday';
    if (schedule?.day_pattern === 'sunday') return 'Every Sunday';
    return schedule?.day_pattern || 'Schedule';
  };

  const formatTimeDisplay = (schedule) => {
    if (schedule.is_closed) return 'Closed';
    if (schedule.is_open_24_hours) return 'Open 24 hours';
    const open = schedule.opens_at || '—';
    const close = schedule.closes_at || '—';
    return `${open} – ${close}`;
  };

  const submit = async () => {
    processing.value = true;
    errors.value = {};

    try {
      const payload = {
        schedules: localSchedules.value.map((s) => ({
          day_pattern: s.day_pattern,
          days_of_week: s.days_of_week?.length ? s.days_of_week : null,
          opens_at: s.opens_at || null,
          closes_at: s.closes_at || null,
          is_open_24_hours: !!s.is_open_24_hours,
          is_closed: !!s.is_closed,
          effective_from: s.effective_from || null,
          effective_until: s.effective_until || null,
          day_details: s.day_details || [],
        })),
      };

      const response = await http.put(
        `/api/v1/churches/${props.churchId}/adoration-chapel`,
        payload,
      );

      notify.success('Adoration Chapel schedules updated successfully.');
      emit('updated', response.data.data);
      showModal.value = false;
    } catch (error) {
      const responseErrors = error?.response?.data?.errors;
      if (responseErrors) {
        errors.value = responseErrors;
      }
      notify.error('There was an error saving the schedules.');
    } finally {
      processing.value = false;
    }
  };

  const closeModal = () => {
    resetForm();
    showModal.value = false;
  };
</script>

<template>
  <div>
    <dialog-modal max-width="800" :show="showModal" @close="closeModal">
      <template #title> Manage Adoration Schedules </template>

      <template #content>
        <!-- Existing schedules list -->
        <div v-if="localSchedules.length" class="tw:space-y-3 tw:mb-4">
          <div
            v-for="(schedule, index) in localSchedules"
            :key="index"
            class="tw:flex tw:items-center tw:justify-between tw:p-3 tw:bg-gray-50 tw:rounded-lg"
          >
            <div class="tw:flex tw:items-center tw:gap-3 tw:flex-1">
              <v-chip color="primary" size="small" variant="tonal">
                {{ formatPatternLabel(schedule) }}
              </v-chip>
              <span class="tw:text-sm tw:text-gray-700">
                <v-icon
                  v-if="schedule.is_closed"
                  class="tw:mr-1"
                  color="error"
                  :icon="mdiCancel"
                  size="x-small"
                />
                <v-icon
                  v-else-if="schedule.is_open_24_hours"
                  class="tw:mr-1"
                  color="success"
                  :icon="mdiWeatherSunny"
                  size="x-small"
                />
                <v-icon v-else class="tw:mr-1" :icon="mdiClockOutline" size="x-small" />
                {{ formatTimeDisplay(schedule) }}
              </span>
              <span
                v-if="schedule.effective_from || schedule.effective_until"
                class="tw:text-xs tw:text-gray-500"
              >
                {{
                  schedule.effective_from
                    ? useDayjs(schedule.effective_from).format('MMM D, YYYY')
                    : ''
                }}
                {{ schedule.effective_from && schedule.effective_until ? '–' : '' }}
                {{
                  schedule.effective_until
                    ? useDayjs(schedule.effective_until).format('MMM D, YYYY')
                    : ''
                }}
              </span>
            </div>
            <div class="tw:flex tw:items-center tw:gap-2">
              <v-icon
                color="primary"
                :icon="mdiPencilOutline"
                size="small"
                @click="openEditForm(index)"
              />
              <v-icon
                color="error"
                :icon="mdiDeleteOutline"
                size="small"
                @click="removeEntry(index)"
              />
            </div>
          </div>
        </div>

        <v-alert v-else class="tw:mb-4" color="info" density="compact" variant="tonal">
          No schedules configured. Add one below.
        </v-alert>

        <!-- Add / Edit entry form -->
        <v-expand-transition>
          <v-card v-if="showEntryForm" class="tw:mb-4" variant="outlined">
            <v-card-text>
              <div class="tw:text-sm tw:font-medium tw:mb-3">
                {{ editingIndex !== null ? 'Edit Schedule Entry' : 'New Schedule Entry' }}
              </div>

              <div class="tw:grid tw:grid-cols-2 tw:gap-4">
                <SelectInput
                  v-model="entryForm.day_pattern"
                  :items="dayPatternOptions"
                  label="Day Pattern"
                />

                <v-select
                  v-if="entryForm.day_pattern === 'specific'"
                  v-model="entryForm.days_of_week"
                  chips
                  :items="daysOfWeekOptions"
                  label="Days of Week"
                  multiple
                  variant="filled"
                />
              </div>

              <div class="tw:flex tw:gap-4 tw:mt-4">
                <v-checkbox
                  v-model="entryForm.is_open_24_hours"
                  density="compact"
                  hide-details
                  label="Open 24 hours"
                  @update:model-value="
                    (val) => {
                      if (val) entryForm.is_closed = false;
                    }
                  "
                />
                <v-checkbox
                  v-model="entryForm.is_closed"
                  density="compact"
                  hide-details
                  label="Closed"
                  @update:model-value="
                    (val) => {
                      if (val) entryForm.is_open_24_hours = false;
                    }
                  "
                />
              </div>

              <div
                v-if="!entryForm.is_open_24_hours && !entryForm.is_closed"
                class="tw:grid tw:grid-cols-2 tw:gap-4 tw:mt-4"
              >
                <TimeInput v-model="entryForm.opens_at" label="Opens At" />
                <TimeInput v-model="entryForm.closes_at" label="Closes At" />
              </div>

              <div class="tw:grid tw:grid-cols-2 tw:gap-4 tw:mt-4">
                <DateInput v-model="effectiveFromDate" label="Effective From" landscape />
                <DateInput v-model="effectiveUntilDate" label="Effective Until" landscape />
              </div>

              <div class="tw:flex tw:justify-end tw:gap-2 tw:mt-4">
                <v-btn size="small" variant="text" @click="cancelEntry"> Cancel </v-btn>
                <v-btn color="primary" size="small" variant="elevated" @click="saveEntry">
                  {{ editingIndex !== null ? 'Update Entry' : 'Add Entry' }}
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-expand-transition>

        <v-btn
          v-if="!showEntryForm"
          color="primary"
          :prepend-icon="mdiPlus"
          size="small"
          variant="outlined"
          @click="openAddForm"
        >
          Add Schedule Entry
        </v-btn>
      </template>

      <template #footer>
        <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>

        <PrimaryButton
          class="tw:ml-3 tw:capitalize"
          :disabled="processing"
          :loading="processing"
          @click="submit"
        >
          Save All Schedules
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>
