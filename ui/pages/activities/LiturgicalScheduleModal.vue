<script setup>
  import { computed, onMounted, ref, watch } from 'vue';
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';
  import { http } from '@/plugins';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import SelectInput from '@/components/SelectInput.vue';
  import TimeInput from '@/components/TimeInput.vue';
  import { mdiPlus, mdiDeleteOutline, mdiImport } from '@mdi/js';

  const props = defineProps({
    liturgicalSchedule: { type: Object, default: null },
    church: { type: Object, required: true },
  });

  const showModal = defineModel('showModal', { type: Boolean });
  const emit = defineEmits(['created', 'updated']);

  const holidays = ref([]);
  const loadingHolidays = ref(false);

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
    { title: 'Mass of the Lords Supper', value: 'mass-of-the-lords-supper' },
    { title: 'Good Friday Service', value: 'good-friday-service' },
    { title: 'Easter Vigil Mass', value: 'easter-vigil-mass' },
    { title: 'Easter Mass', value: 'easter-mass' },
    { title: 'Easter Monday Mass', value: 'easter-monday-mass' },
    { title: 'Christmas Vigil Mass', value: 'christmas-vigil-mass' },
    { title: 'Christmas Mass', value: 'christmas-mass' },
  ];

  const currentYear = new Date().getFullYear();
  const yearOptions = computed(() => {
    const years = [{ title: 'Every Year', value: null }];
    for (let i = currentYear; i <= currentYear + 5; i++) {
      years.push({ title: String(i), value: i });
    }
    return years;
  });

  const form = useForm({
    church_id: props.church?.id || '',
    holiday_id: props.liturgicalSchedule?.holiday_id || null,
    year: props.liturgicalSchedule?.year || null,
    replaces_regular_schedule: props.liturgicalSchedule?.replaces_regular_schedule ?? true,
    notes: props.liturgicalSchedule?.notes || '',
    times: props.liturgicalSchedule?.times || [],
  });

  const emptyTimeEntry = () => ({
    type: 'mass',
    activity_type_description: 'mass',
    start_time: '',
    end_time: '',
    language: 'English',
  });

  const addTimeEntry = () => {
    form.times.push(emptyTimeEntry());
  };

  const removeTimeEntry = (index) => {
    form.times.splice(index, 1);
  };

  const daysOfWeek = [
    { title: 'Sunday', value: 'Sunday' },
    { title: 'Monday', value: 'Monday' },
    { title: 'Tuesday', value: 'Tuesday' },
    { title: 'Wednesday', value: 'Wednesday' },
    { title: 'Thursday', value: 'Thursday' },
    { title: 'Friday', value: 'Friday' },
    { title: 'Saturday', value: 'Saturday' },
  ];

  const importDay = ref(null);
  const importingSchedules = ref(false);

  /**
   * Resolve the values for a specific day from an activity schedule,
   * taking day_details variations into account.
   */
  function resolveScheduleForDay(schedule, dayName) {
    const normalizedDay = dayName.toLowerCase();
    const dayDetail = (schedule.day_details || []).find(
      (d) => d.day_of_week?.toLowerCase() === normalizedDay,
    );

    return {
      type: schedule.type || 'mass',
      activity_type_description:
        dayDetail?.activity_type_description || schedule.activity_type_description || schedule.type,
      start_time: dayDetail?.start_time || schedule.start_time || '',
      end_time: dayDetail?.end_time || schedule.end_time || '',
      language: dayDetail?.language || schedule.language || 'English',
      is_skipped: dayDetail?.is_skipped || false,
    };
  }

  function importFromRegularSchedule() {
    if (!importDay.value || !props.church?.id) return;

    importingSchedules.value = true;

    http
      .get('/api/v1/activity-schedules', {
        params: {
          church_id: props.church.id,
          matches_day: importDay.value,
          itemsPerPage: 100,
        },
      })
      .then(({ data }) => {
        const schedules = data.data || [];

        if (schedules.length === 0) {
          notify.warning(`No regular schedules found for ${importDay.value}.`);
          return;
        }

        const importedTimes = schedules
          .map((schedule) => resolveScheduleForDay(schedule, importDay.value))
          .filter((entry) => !entry.is_skipped)
          .map(({ is_skipped, ...rest }) => rest)
          .sort((a, b) => a.start_time.localeCompare(b.start_time));

        if (importedTimes.length === 0) {
          notify.warning(`All ${importDay.value} schedules are skipped.`);
          return;
        }

        form.times = importedTimes;
        notify.success(`Imported ${importedTimes.length} schedule(s) from ${importDay.value}.`);
      })
      .catch((error) => {
        console.error('Failed to import schedules:', error);
        notify.error('Failed to import regular schedules.');
      })
      .finally(() => {
        importingSchedules.value = false;
      });
  }

  function fetchHolidays() {
    loadingHolidays.value = true;
    http
      .get('/api/v1/holidays', { params: { itemsPerPage: 100 } })
      .then(({ data }) => {
        holidays.value = data.data.map((h) => ({
          title: h.name,
          value: h.id,
        }));
      })
      .catch((error) => {
        console.error('Failed to fetch holidays:', error);
      })
      .finally(() => {
        loadingHolidays.value = false;
      });
  }

  onMounted(() => {
    fetchHolidays();
    // Initialize with one time entry if empty
    if (form.times.length === 0) {
      addTimeEntry();
    }
  });

  watch(
    () => props.liturgicalSchedule,
    (newValue) => {
      if (newValue?.id) {
        form.church_id = newValue.church_id || props.church?.id || '';
        form.holiday_id = newValue.holiday_id || null;
        form.year = newValue.year || null;
        form.replaces_regular_schedule = newValue.replaces_regular_schedule ?? true;
        form.notes = newValue.notes || '';
        form.times = (newValue.times || []).map((t) => ({
          type: t.type || 'mass',
          activity_type_description: t.activity_type_description || 'mass',
          start_time: t.start_time || '',
          end_time: t.end_time || '',
          language: t.language || 'English',
        }));
      } else {
        // Reset form for new schedule
        form.church_id = props.church?.id || '';
        form.holiday_id = null;
        form.year = null;
        form.replaces_regular_schedule = true;
        form.notes = '';
        form.times = [emptyTimeEntry()];
      }
    },
    { deep: true },
  );

  watch(showModal, (newValue) => {
    if (!newValue) {
      form.clearErrors();
    } else if (!props.liturgicalSchedule?.id && form.times.length === 0) {
      // Add initial time entry when opening for new schedule
      addTimeEntry();
    }
  });

  const isEditing = computed(() => !!props.liturgicalSchedule?.id);

  const submit = () => {
    if (!props.church?.id) {
      return notify.error('Church details not provided');
    }

    const url = isEditing.value
      ? `/api/v1/liturgical-schedules/${props.liturgicalSchedule?.id}`
      : `/api/v1/liturgical-schedules`;

    const method = isEditing.value ? 'put' : 'post';

    form.church_id = props.church.id;

    return form[method](url, {
      onSuccess: ({ data }) => {
        const message = isEditing.value
          ? 'Liturgical schedule was updated successfully'
          : 'Liturgical schedule was added successfully.';
        notify.success(message);
        closeModal(data);
      },
      onError: () => notify.error('There was an error saving the liturgical schedule.'),
    });
  };

  const closeModal = (data = null) => {
    form.reset();
    form.times = [emptyTimeEntry()];
    if ((data?.id || data?.length) && !isEditing.value) emit('created', data);
    if (data?.id && isEditing.value) emit('updated', data);

    showModal.value = false;
  };
</script>

<template>
  <div>
    <dialog-modal max-width="800" :show="showModal" @close="closeModal">
      <template #title>
        {{ isEditing ? 'Update Liturgical Schedule' : 'New Liturgical Schedule' }}
      </template>

      <template #content>
        <form>
          <div class="tw:mt-4">
            <v-text-field
              class="tw:block tw:w-full tw:mt-1 tw:capitalize"
              density="compact"
              :error="form.errors.church_id"
              label="Church*"
              :model-value="`${church?.name} - ${church?.city}`"
              readonly
              variant="solo"
            />
          </div>

          <div class="tw:grid tw:grid-cols-2 tw:gap-4 tw:mt-4">
            <v-autocomplete
              v-model="form.holiday_id"
              class="tw:block tw:w-full"
              clearable
              density="compact"
              :error-messages="form.errors.holiday_id"
              hide-details="auto"
              :items="holidays"
              label="Holiday*"
              :loading="loadingHolidays"
            />
            <SelectInput
              v-model="form.year"
              density="compact"
              :error="form.errors.year"
              :items="yearOptions"
              label="Year"
            />
          </div>

          <div class="tw:mt-4">
            <v-checkbox
              v-model="form.replaces_regular_schedule"
              color="primary"
              density="compact"
              hide-details
              label="Replaces Regular Schedule"
            ></v-checkbox>
            <div class="tw:text-xs tw:text-gray-500 tw:mt-1 tw:ml-10">
              When checked, regular schedules for this day will be replaced by this liturgical
              schedule.
            </div>
          </div>

          <!-- Import from Regular Schedule -->
          <v-divider class="tw:my-6" />
          <div class="tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
            Import from Regular Schedule
          </div>
          <div class="tw:text-xs tw:text-gray-500 tw:mb-3">
            Pre-fill times by importing the church's regular schedule for a specific day of the
            week. This will replace any existing times below.
          </div>
          <div class="tw:flex tw:items-center tw:gap-3">
            <v-select
              v-model="importDay"
              class="tw:max-w-[200px]"
              density="compact"
              hide-details
              :items="daysOfWeek"
              label="Day of Week"
              variant="outlined"
            />
            <v-btn
              color="primary"
              :disabled="!importDay || importingSchedules"
              :loading="importingSchedules"
              :prepend-icon="mdiImport"
              size="small"
              variant="tonal"
              @click="importFromRegularSchedule"
            >
              Import
            </v-btn>
          </div>

          <!-- Times Section -->
          <v-divider class="tw:my-6" />
          <div class="tw:flex tw:justify-between tw:items-center tw:mb-4">
            <div class="tw:text-sm tw:font-medium tw:text-gray-700">
              Mass/Activity Times*
              <span class="tw:text-xs tw:text-gray-500 tw:ml-2">
                ({{ form.times.length }} {{ form.times.length === 1 ? 'time' : 'times' }})
              </span>
            </div>
            <v-btn color="primary" size="small" variant="tonal" @click="addTimeEntry">
              <v-icon :icon="mdiPlus" size="small" />
              Add Time
            </v-btn>
          </div>

          <div v-if="form.errors.times" class="tw:text-red-500 tw:text-sm tw:mb-2">
            {{ form.errors.times }}
          </div>

          <v-card
            v-for="(time, index) in form.times"
            :key="index"
            class="tw:mb-4"
            variant="outlined"
          >
            <div class="tw:flex tw:justify-between tw:items-start tw:m-2">
              <div class="tw:text-sm tw:font-medium tw:text-gray-600">Time #{{ index + 1 }}</div>
              <v-btn
                v-if="form.times.length > 1"
                color="error"
                :icon="mdiDeleteOutline"
                size="x-small"
                variant="text"
                @click="removeTimeEntry(index)"
              />
            </div>
            <v-container>
              <v-row dense>
                <v-col md="6">
                  <SelectInput
                    v-model="time.type"
                    class="tw:block tw:w-full"
                    density="compact"
                    :error="form.errors[`times.${index}.type`]"
                    :items="activityTypes"
                    label="Type*"
                    required
                  />
                </v-col>
                <v-col md="6">
                  <v-autocomplete
                    v-model="time.activity_type_description"
                    class="tw:block tw:w-full"
                    density="compact"
                    :error-messages="form.errors[`times.${index}.activity_type_description`]"
                    hide-details="auto"
                    :items="activityTypeDescriptions"
                    label="Description"
                  />
                </v-col>
                <v-col md="4">
                  <TimeInput
                    v-model="time.start_time"
                    density="compact"
                    :error="form.errors[`times.${index}.start_time`]"
                    label="Start Time*"
                    required
                  />
                </v-col>
                <v-col md="4">
                  <TimeInput
                    v-model="time.end_time"
                    density="compact"
                    :error="form.errors[`times.${index}.end_time`]"
                    label="End Time"
                  />
                </v-col>
                <v-col md="4">
                  <v-text-field
                    v-model="time.language"
                    density="compact"
                    :error-messages="form.errors[`times.${index}.language`]"
                    hide-details="auto"
                    label="Language*"
                    variant="outlined"
                  />
                </v-col>
              </v-row>
            </v-container>
          </v-card>

          <div class="tw:mt-4">
            <v-textarea
              v-model="form.notes"
              auto-grow
              class="tw:mt-1"
              counter
              density="compact"
              :error-messages="form.errors.notes"
              label="Notes (Optional)"
              maxlength="1000"
              rows="2"
              variant="outlined"
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
          {{ isEditing ? 'Update Schedule' : 'Add Schedule' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>

<style lang="scss" scoped></style>
