<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import {
    mdiPencilOutline,
    mdiDeleteOutline,
    mdiCalendarClock,
    mdiCalendarAlert,
    mdiAlertCircleOutline,
    mdiCalendarMonth,
    mdiCalendarStar,
  } from '@mdi/js';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import DataTable from '@/components/DataTable.vue';
  import AppLayout from '@/layouts/AppLayout';
  import ChurchDetails from './ChurchDetails.vue';
  import ActivityScheduleModal from '@/pages/activities/ActivityScheduleModal.vue';
  import OneTimeActivityModal from '@/pages/activities/OneTimeActivityModal.vue';
  import ScheduleOverrideModal from '@/pages/activities/ScheduleOverrideModal.vue';
  import ScheduleRuleModal from '@/pages/activities/ScheduleRuleModal.vue';
  import LiturgicalScheduleModal from '@/pages/activities/LiturgicalScheduleModal.vue';
  import { notify, useDayjs } from '@/utils';
  import useAuthStore from '@/stores/useAuthStore';

  const props = defineProps({
    churchId: { type: [String, Number], required: true },
  });

  const store = useAuthStore();

  const church = ref(null);
  const isFetching = ref(false);

  // Modal states
  const showScheduleModal = ref(false);
  const showOneTimeModal = ref(false);
  const showOverrideModal = ref(false);
  const showRuleModal = ref(false);
  const showLiturgicalModal = ref(false);

  // Edit states
  const scheduleToEdit = ref(null);
  const activityToEdit = ref(null);
  const overrideToEdit = ref(null);
  const ruleToEdit = ref(null);
  const liturgicalScheduleToEdit = ref(null);

  const deletingItem = ref(false);
  const activeTab = ref('schedules');

  // Schedules (V2) data
  const schedules = ref([]);
  const oneTimeActivities = ref([]);
  const overrides = ref([]);
  const rules = ref([]);
  const liturgicalSchedules = ref([]);
  const schedulePeriodsById = ref({});

  const scheduleHeaders = [
    { title: 'Type', value: 'type' },
    { title: 'Description', value: 'activity_type_description' },
    { title: 'Days & Period', value: 'pattern_summary' },
    { title: 'Start Time', value: 'start_time' },
    { title: 'Language', value: 'language' },
    { title: '', value: 'actions' },
  ];

  const oneTimeHeaders = [
    { title: 'Type', value: 'type' },
    { title: 'Date', value: 'date' },
    { title: 'Start Time', value: 'start_time' },
    { title: 'Language', value: 'language' },
    { title: '', value: 'actions' },
  ];

  const overrideHeaders = [
    { title: 'Name', value: 'name' },
    { title: 'Dates', value: 'date_range' },
    { title: 'Action', value: 'action' },
    { title: 'Target', value: 'target_summary' },
    { title: 'Active', value: 'is_active' },
    { title: '', value: 'actions' },
  ];

  const ruleHeaders = [
    { title: 'Name', value: 'name' },
    { title: 'Condition', value: 'condition_summary' },
    { title: 'Action', value: 'action' },
    { title: 'Active', value: 'is_active' },
    { title: '', value: 'actions' },
  ];

  const liturgicalHeaders = [
    { title: 'Holiday', value: 'holiday_name' },
    { title: 'Year', value: 'year_display' },
    { title: 'Times', value: 'times_count' },
    { title: 'Replaces Regular', value: 'replaces_regular_schedule' },
    { title: '', value: 'actions' },
  ];

  onMounted(() => {
    fetchChurchDetails();
    fetchSchedules();
    fetchOneTimeActivities();
    fetchOverrides();
    fetchRules();
    fetchLiturgicalSchedules();
    fetchSchedulePeriods();
  });

  const fetchChurchDetails = () => {
    if (!props.churchId) return;
    isFetching.value = true;
    http
      .get(`/api/v1/churches/${props.churchId}`)
      .then(({ data }) => {
        church.value = data?.data;
      })
      .catch(() => notify.error('Something went wrong fetching the church details.'))
      .finally(() => (isFetching.value = false));
  };

  const fetchSchedules = () => {
    http
      .get('/api/v1/activity-schedules', { params: { church_id: props.churchId } })
      .then(({ data }) => {
        schedules.value = data.data;
      });
  };

  const fetchSchedulePeriods = () => {
    http
      .get('/api/v1/schedule-periods', { params: { per_page: 100, is_active: true } })
      .then(({ data }) => {
        schedulePeriodsById.value = data.data.reduce((map, period) => {
          map[period.id] = period.name;
          return map;
        }, {});
      });
  };

  const fetchOneTimeActivities = () => {
    http
      .get('/api/v1/one-time-activities', { params: { church_id: props.churchId } })
      .then(({ data }) => {
        oneTimeActivities.value = data.data;
      });
  };

  const fetchOverrides = () => {
    http
      .get('/api/v1/schedule-overrides', {
        params: { scope_type: 'church', scope_id: props.churchId },
      })
      .then(({ data }) => {
        overrides.value = data.data;
      });
  };

  const fetchRules = () => {
    http
      .get('/api/v1/schedule-rules', { params: { scope_type: 'church', scope_id: props.churchId } })
      .then(({ data }) => {
        rules.value = data.data;
      });
  };

  const fetchLiturgicalSchedules = () => {
    http
      .get('/api/v1/liturgical-schedules', { params: { church_id: props.churchId } })
      .then(({ data }) => {
        liturgicalSchedules.value = data.data.map((item) => ({
          ...item,
          holiday_name: item.holiday?.name || 'Unknown Holiday',
          year_display: item.year || 'Every Year',
          times_count: item.times?.length || 0,
        }));
      });
  };

  const weekOfMonthLabels = {
    1: 'First',
    2: 'Second',
    3: 'Third',
    4: 'Fourth',
    5: 'Last',
  };

  const formatPatternLabel = (schedule) => {
    if (schedule?.day_pattern === 'monthly-nth' && schedule?.day_of_week) {
      const weekLabel = schedule.week_of_month_label || weekOfMonthLabels[schedule.week_of_month];
      if (weekLabel) {
        return `${weekLabel} ${schedule.day_of_week} of every month`;
      }
    }

    if (schedule?.day_pattern === 'specific' && schedule?.days_of_week?.length > 0) {
      return `Every ${schedule.days_of_week.join(', ')}`;
    }

    if (schedule?.day_pattern === 'weekdays') {
      return 'Every Monday–Friday';
    }

    if (schedule?.day_pattern === 'daily') {
      return 'Every day';
    }

    if (schedule?.day_pattern === 'saturday') {
      return 'Every Saturday';
    }

    if (schedule?.day_pattern === 'sunday') {
      return 'Every Sunday';
    }

    return schedule?.day_pattern || 'Schedule';
  };

  const formatEffectiveRange = (schedule) => {
    const start = schedule?.effective_from
      ? useDayjs(schedule.effective_from).format('MMM D, YYYY')
      : null;
    const end = schedule?.effective_until
      ? useDayjs(schedule.effective_until).format('MMM D, YYYY')
      : null;

    if (start && end) {
      return `Effective ${start}–${end}`;
    }

    if (start) {
      return `Effective from ${start}`;
    }

    if (end) {
      return `Effective until ${end}`;
    }

    return null;
  };

  const formatSchedulePeriod = (schedule) => {
    if (schedule?.schedule_period?.name) {
      return schedule.schedule_period.name;
    }

    if (schedule?.schedule_period_id) {
      return (
        schedulePeriodsById.value[schedule.schedule_period_id] ||
        `Schedule period #${schedule.schedule_period_id}`
      );
    }

    return null;
  };

  const formatScheduleSummary = (schedule) => {
    const parts = [
      formatPatternLabel(schedule),
      formatSchedulePeriod(schedule),
      formatEffectiveRange(schedule),
    ];
    return parts.filter(Boolean).join(' • ');
  };

  // Delete handlers
  const deleteSchedule = async (id) => {
    if (!(await notify.confirm('Delete this schedule template?'))) return;
    deletingItem.value = true;
    http
      .delete(`/api/v1/activity-schedules/${id}`)
      .then(() => {
        schedules.value = schedules.value.filter((i) => i.id !== id);
        notify.success('Schedule deleted');
      })
      .catch(() => notify.error('Failed to delete schedule'))
      .finally(() => (deletingItem.value = false));
  };

  const deleteOneTimeActivity = async (id) => {
    if (!(await notify.confirm('Delete this one-time activity?'))) return;
    deletingItem.value = true;
    http
      .delete(`/api/v1/one-time-activities/${id}`)
      .then(() => {
        oneTimeActivities.value = oneTimeActivities.value.filter((i) => i.id !== id);
        notify.success('Activity deleted');
      })
      .catch(() => notify.error('Failed to delete activity'))
      .finally(() => (deletingItem.value = false));
  };

  const deleteOverride = async (id) => {
    if (!(await notify.confirm('Delete this schedule exception?'))) return;
    deletingItem.value = true;
    http
      .delete(`/api/v1/schedule-overrides/${id}`)
      .then(() => {
        overrides.value = overrides.value.filter((i) => i.id !== id);
        notify.success('Exception deleted');
      })
      .catch(() => notify.error('Failed to delete exception'))
      .finally(() => (deletingItem.value = false));
  };

  const deleteRule = async (id) => {
    if (!(await notify.confirm('Delete this schedule rule?'))) return;
    deletingItem.value = true;
    http
      .delete(`/api/v1/schedule-rules/${id}`)
      .then(() => {
        rules.value = rules.value.filter((i) => i.id !== id);
        notify.success('Rule deleted');
      })
      .catch(() => notify.error('Failed to delete rule'))
      .finally(() => (deletingItem.value = false));
  };

  const deleteLiturgicalSchedule = async (id) => {
    if (!(await notify.confirm('Delete this liturgical schedule?'))) return;
    deletingItem.value = true;
    http
      .delete(`/api/v1/liturgical-schedules/${id}`)
      .then(() => {
        liturgicalSchedules.value = liturgicalSchedules.value.filter((i) => i.id !== id);
        notify.success('Liturgical schedule deleted');
      })
      .catch(() => notify.error('Failed to delete liturgical schedule'))
      .finally(() => (deletingItem.value = false));
  };

  // Update handlers
  const onScheduleUpdated = (item) => {
    const index = schedules.value.findIndex((s) => s.id === item.id);
    if (index !== -1) schedules.value[index] = item;
    else schedules.value.push(item);
  };

  const onOneTimeActivityUpdated = (item) => {
    const index = oneTimeActivities.value.findIndex((s) => s.id === item.id);
    if (index !== -1) oneTimeActivities.value[index] = item;
    else oneTimeActivities.value.push(item);
  };

  const onOverrideUpdated = (item) => {
    const index = overrides.value.findIndex((s) => s.id === item.id);
    if (index !== -1) overrides.value[index] = item;
    else overrides.value.push(item);
  };

  const onRuleUpdated = (item) => {
    const index = rules.value.findIndex((s) => s.id === item.id);
    if (index !== -1) rules.value[index] = item;
    else rules.value.push(item);
  };

  const onLiturgicalScheduleUpdated = (item) => {
    const transformedItem = {
      ...item,
      holiday_name: item.holiday?.name || 'Unknown Holiday',
      year_display: item.year || 'Every Year',
      times_count: item.times?.length || 0,
    };
    const index = liturgicalSchedules.value.findIndex((s) => s.id === item.id);
    if (index !== -1) liturgicalSchedules.value[index] = transformedItem;
    else liturgicalSchedules.value.push(transformedItem);
  };
</script>

<template>
  <app-layout>
    <template #head>
      <h2>Church Details</h2>
    </template>
    <v-container>
      <v-skeleton-loader v-if="isFetching" type="card"></v-skeleton-loader>
      <v-card v-else-if="!church">
        <v-card-title> No church found</v-card-title>
      </v-card>
      <v-row v-else class="tw:py-4">
        <!-- show church details -->
        <v-col cols="12">
          <ChurchDetails v-model:church="church" />
        </v-col>

        <!-- Tabs for Schedule Management -->
        <v-col cols="12">
          <v-card variant="flat">
            <v-tabs v-model="activeTab" bg-color="transparent" color="primary">
              <v-tab value="schedules">
                <v-icon class="tw:mr-2" :icon="mdiCalendarClock" /> Regular Schedules
              </v-tab>
              <v-tab value="one-time">
                <v-icon class="tw:mr-2" :icon="mdiCalendarMonth" /> One-Time Activities
              </v-tab>
              <v-tab value="liturgical">
                <v-icon class="tw:mr-2" :icon="mdiCalendarStar" /> Liturgical
              </v-tab>
              <v-tab value="overrides">
                <v-icon class="tw:mr-2" :icon="mdiCalendarAlert" /> Exceptions
              </v-tab>
              <v-tab value="rules">
                <v-icon class="tw:mr-2" :icon="mdiAlertCircleOutline" /> Rules
              </v-tab>
            </v-tabs>

            <v-window v-model="activeTab" class="tw:mt-4">
              <!-- Regular Schedules Tab -->
              <v-window-item value="schedules">
                <div class="tw:flex tw:justify-between tw:mb-4">
                  <div>
                    <p class="tw:text-base tw:text-gray-700">
                      Recurring schedules with days, periods, and effective dates shown.
                    </p>
                    <p class="tw:text-sm tw:text-gray-500">
                      Each entry lists the weekly pattern, schedule period, and date range (if set).
                    </p>
                  </div>
                  <PrimaryButton @click="((scheduleToEdit = null), (showScheduleModal = true))">
                    Add Schedule
                  </PrimaryButton>
                </div>
                <data-table class="tw:capitalize" :headers="scheduleHeaders" :items="schedules">
                  <template #item.pattern_summary="{ item }">
                    <span class="tw:text-sm tw:text-gray-800">
                      {{ formatScheduleSummary(item) }}
                    </span>
                  </template>
                  <template #item.actions="{ item }">
                    <v-icon
                      class="tw:mr-2"
                      color="primary"
                      :icon="mdiPencilOutline"
                      @click="((scheduleToEdit = item), (showScheduleModal = true))"
                    />
                    <v-icon
                      v-if="store.user.is_admin"
                      color="error"
                      :disabled="deletingItem"
                      :icon="mdiDeleteOutline"
                      @click="deleteSchedule(item.id)"
                    />
                  </template>
                </data-table>
              </v-window-item>

              <!-- One-Time Activities Tab -->
              <v-window-item value="one-time">
                <div class="tw:flex tw:justify-between tw:mb-4">
                  <div>
                    <p class="tw:text-base tw:text-gray-700">
                      Single-occurrence activities with exact dates and times.
                    </p>
                    <p class="tw:text-sm tw:text-gray-500">
                      Use this section for events that do not repeat.
                    </p>
                  </div>
                  <PrimaryButton @click="((activityToEdit = null), (showOneTimeModal = true))">
                    Add One-Time Activity
                  </PrimaryButton>
                </div>
                <data-table
                  class="tw:capitalize"
                  :headers="oneTimeHeaders"
                  :items="oneTimeActivities"
                >
                  <template #item.actions="{ item }">
                    <v-icon
                      class="tw:mr-2"
                      color="primary"
                      :icon="mdiPencilOutline"
                      @click="((activityToEdit = item), (showOneTimeModal = true))"
                    />
                    <v-icon
                      v-if="store.user.is_admin"
                      color="error"
                      :disabled="deletingItem"
                      :icon="mdiDeleteOutline"
                      @click="deleteOneTimeActivity(item.id)"
                    />
                  </template>
                </data-table>
              </v-window-item>

              <!-- Liturgical Schedules Tab -->
              <v-window-item value="liturgical">
                <div class="tw:flex tw:justify-between tw:mb-4">
                  <div>
                    <p class="tw:text-base tw:text-gray-700">
                      Holiday-specific schedules tied to liturgical dates.
                    </p>
                    <p class="tw:text-sm tw:text-gray-500">
                      Shows the holiday, year, and number of special times configured.
                    </p>
                  </div>
                  <PrimaryButton
                    @click="((liturgicalScheduleToEdit = null), (showLiturgicalModal = true))"
                  >
                    Add Liturgical Schedule
                  </PrimaryButton>
                </div>
                <data-table
                  class="tw:capitalize"
                  :headers="liturgicalHeaders"
                  :items="liturgicalSchedules"
                >
                  <template #item.replaces_regular_schedule="{ item }">
                    <v-chip
                      :color="item.replaces_regular_schedule ? 'warning' : 'grey'"
                      size="small"
                      variant="tonal"
                    >
                      {{ item.replaces_regular_schedule ? 'Yes' : 'No' }}
                    </v-chip>
                  </template>
                  <template #item.actions="{ item }">
                    <v-icon
                      class="tw:mr-2"
                      color="primary"
                      :icon="mdiPencilOutline"
                      @click="((liturgicalScheduleToEdit = item), (showLiturgicalModal = true))"
                    />
                    <v-icon
                      v-if="store.user.is_admin"
                      color="error"
                      :disabled="deletingItem"
                      :icon="mdiDeleteOutline"
                      @click="deleteLiturgicalSchedule(item.id)"
                    />
                  </template>
                </data-table>
              </v-window-item>

              <!-- Exceptions Tab -->
              <v-window-item value="overrides">
                <div class="tw:flex tw:justify-between tw:mb-4">
                  <div>
                    <p class="tw:text-base tw:text-gray-700">
                      Exceptions that override regular schedules over date ranges.
                    </p>
                    <p class="tw:text-sm tw:text-gray-500">
                      Each entry lists a target activity and the affected dates.
                    </p>
                  </div>
                  <PrimaryButton @click="((overrideToEdit = null), (showOverrideModal = true))">
                    Add Exception
                  </PrimaryButton>
                </div>
                <data-table class="tw:capitalize" :headers="overrideHeaders" :items="overrides">
                  <template #item.target_summary="{ item }">
                    {{ item.target_summary || 'All Activities' }}
                  </template>
                  <template #item.date_range="{ item }">
                    {{ item.start_date }} - {{ item.end_date }}
                  </template>
                  <template #item.is_active="{ item }">
                    <v-chip
                      :color="item.is_active ? 'success' : 'grey'"
                      size="small"
                      variant="tonal"
                    >
                      {{ item.is_active ? 'Active' : 'Inactive' }}
                    </v-chip>
                  </template>
                  <template #item.actions="{ item }">
                    <v-icon
                      class="tw:mr-2"
                      color="primary"
                      :icon="mdiPencilOutline"
                      @click="((overrideToEdit = item), (showOverrideModal = true))"
                    />
                    <v-icon
                      v-if="store.user.is_admin"
                      color="error"
                      :disabled="deletingItem"
                      :icon="mdiDeleteOutline"
                      @click="deleteOverride(item.id)"
                    />
                  </template>
                </data-table>
              </v-window-item>

              <!-- Rules Tab -->
              <v-window-item value="rules">
                <div class="tw:flex tw:justify-between tw:mb-4">
                  <div>
                    <p class="tw:text-base tw:text-gray-700">
                      Conditional rules that automate changes to regular schedules.
                    </p>
                    <p class="tw:text-sm tw:text-gray-500">
                      Each rule summarizes its condition and action.
                    </p>
                  </div>
                  <PrimaryButton @click="((ruleToEdit = null), (showRuleModal = true))">
                    Add Rule
                  </PrimaryButton>
                </div>
                <data-table class="tw:capitalize" :headers="ruleHeaders" :items="rules">
                  <template #item.condition_summary="{ item }">
                    {{ item.condition_summary }}
                  </template>
                  <template #item.is_active="{ item }">
                    <v-chip
                      :color="item.is_active ? 'success' : 'grey'"
                      size="small"
                      variant="tonal"
                    >
                      {{ item.is_active ? 'Active' : 'Inactive' }}
                    </v-chip>
                  </template>
                  <template #item.actions="{ item }">
                    <v-icon
                      class="tw:mr-2"
                      color="primary"
                      :icon="mdiPencilOutline"
                      @click="((ruleToEdit = item), (showRuleModal = true))"
                    />
                    <v-icon
                      v-if="store.user.is_admin"
                      color="error"
                      :disabled="deletingItem"
                      :icon="mdiDeleteOutline"
                      @click="deleteRule(item.id)"
                    />
                  </template>
                </data-table>
              </v-window-item>
            </v-window>
          </v-card>
        </v-col>

        <!-- Modals -->
        <ActivityScheduleModal
          v-model:show-modal="showScheduleModal"
          :church="church"
          :schedule="scheduleToEdit"
          @created="onScheduleUpdated"
          @updated="onScheduleUpdated"
        />

        <OneTimeActivityModal
          v-model:show-modal="showOneTimeModal"
          :activity="activityToEdit"
          :church="church"
          @created="onOneTimeActivityUpdated"
          @updated="onOneTimeActivityUpdated"
        />

        <ScheduleOverrideModal
          v-model:show-modal="showOverrideModal"
          :church-id="churchId"
          :override="overrideToEdit"
          @created="onOverrideUpdated"
          @updated="onOverrideUpdated"
        />

        <ScheduleRuleModal
          v-model:show-modal="showRuleModal"
          :church-id="churchId"
          :rule="ruleToEdit"
          @created="onRuleUpdated"
          @updated="onRuleUpdated"
        />

        <LiturgicalScheduleModal
          v-model:show-modal="showLiturgicalModal"
          :church="church"
          :liturgical-schedule="liturgicalScheduleToEdit"
          @created="onLiturgicalScheduleUpdated"
          @updated="onLiturgicalScheduleUpdated"
        />
      </v-row>
    </v-container>
  </app-layout>
</template>
