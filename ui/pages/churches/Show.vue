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
    mdiPlus,
    mdiCancel,
    mdiChurch,
    mdiClockOutline,
    mdiWeatherSunny,
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
  import DayVariationModal from '@/pages/activities/DayVariationModal.vue';
  import AdorationChapelModal from '@/pages/churches/AdorationChapelModal.vue';
  import AdorationChapelScheduleModal from '@/pages/churches/AdorationChapelScheduleModal.vue';
  import AdorationChapelExceptionModal from '@/pages/churches/AdorationChapelExceptionModal.vue';
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

  // Day variation states
  const showVariationModal = ref(false);
  const variationSchedule = ref(null);
  const variationToEdit = ref(null);
  const savingVariation = ref(false);

  const deletingItem = ref(false);
  const activeTab = ref('schedules');

  // Schedules (V2) data
  const schedules = ref([]);
  const oneTimeActivities = ref([]);
  const overrides = ref([]);
  const rules = ref([]);
  const liturgicalSchedules = ref([]);
  const schedulePeriodsById = ref({});

  // Adoration Chapel data
  const adorationChapel = ref(null);
  const adorationChapelLoading = ref(false);
  const showChapelModal = ref(false);
  const showChapelScheduleModal = ref(false);
  const showChapelExceptionModal = ref(false);
  const chapelExceptionToEdit = ref(null);

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

  const chapelExceptionHeaders = [
    { title: 'Reason', value: 'reason' },
    { title: 'Start Date', value: 'start_date' },
    { title: 'End Date', value: 'end_date' },
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
    fetchAdorationChapel();
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

  const fetchAdorationChapel = () => {
    adorationChapelLoading.value = true;
    http
      .get(`/api/v1/churches/${props.churchId}/adoration-chapel`)
      .then(({ data }) => {
        adorationChapel.value = data?.data;
      })
      .catch((error) => {
        // 404 is expected when no chapel exists
        if (error?.response?.status !== 404) {
          notify.error('Something went wrong fetching the adoration chapel.');
        }
        adorationChapel.value = null;
      })
      .finally(() => (adorationChapelLoading.value = false));
  };

  const onChapelCreated = (chapel) => {
    adorationChapel.value = chapel;
  };

  const onChapelUpdated = (chapel) => {
    adorationChapel.value = chapel;
  };

  const deleteAdorationChapel = async () => {
    if (
      !(await notify.confirm(
        'Delete this adoration chapel? This will remove all schedules and exceptions.',
      ))
    )
      return;
    deletingItem.value = true;
    http
      .delete(`/api/v1/churches/${props.churchId}/adoration-chapel`)
      .then(() => {
        adorationChapel.value = null;
        notify.success('Adoration chapel deleted');
      })
      .catch(() => notify.error('Failed to delete adoration chapel'))
      .finally(() => (deletingItem.value = false));
  };

  const onChapelExceptionCreated = (exception) => {
    if (adorationChapel.value) {
      adorationChapel.value.exceptions = [...(adorationChapel.value.exceptions || []), exception];
    }
  };

  const onChapelExceptionUpdated = (exception) => {
    if (adorationChapel.value) {
      const index = adorationChapel.value.exceptions.findIndex((e) => e.id === exception.id);
      if (index !== -1) adorationChapel.value.exceptions[index] = exception;
    }
  };

  const deleteChapelException = async (id) => {
    if (!(await notify.confirm('Delete this chapel exception?'))) return;
    deletingItem.value = true;
    http
      .delete(`/api/v1/churches/${props.churchId}/adoration-chapel/exceptions/${id}`)
      .then(() => {
        if (adorationChapel.value) {
          adorationChapel.value.exceptions = adorationChapel.value.exceptions.filter(
            (e) => e.id !== id,
          );
        }
        notify.success('Chapel exception deleted');
      })
      .catch(() => notify.error('Failed to delete chapel exception'))
      .finally(() => (deletingItem.value = false));
  };

  const formatChapelPatternLabel = (schedule) => {
    if (schedule?.day_pattern === 'specific' && schedule?.days_of_week?.length > 0) {
      return `Every ${schedule.days_of_week.join(', ')}`;
    }
    if (schedule?.day_pattern === 'weekdays') return 'Every Monday\u2013Friday';
    if (schedule?.day_pattern === 'daily') return 'Every day';
    if (schedule?.day_pattern === 'saturday') return 'Every Saturday';
    if (schedule?.day_pattern === 'sunday') return 'Every Sunday';
    return schedule?.day_pattern || 'Schedule';
  };

  const formatChapelTimeDisplay = (schedule) => {
    if (schedule.is_closed) return 'Closed';
    if (schedule.is_open_24_hours) return 'Open 24 hours';
    const open = schedule.opens_at || '\u2014';
    const close = schedule.closes_at || '\u2014';
    return `${open} \u2013 ${close}`;
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

  const supportsVariations = (pattern) => {
    return ['daily', 'weekdays', 'specific'].includes(pattern);
  };

  const getExistingVariationDays = (schedule) => {
    return (schedule?.day_details || []).map((d) => d.day_of_week);
  };

  const formatVariationChange = (variation, schedule) => {
    if (variation.is_skipped) {
      return 'Skipped';
    }

    const changes = [];
    if (variation.start_time) {
      const baseTime = schedule?.start_time || 'N/A';
      changes.push(`${variation.start_time} (instead of ${baseTime})`);
    }
    if (variation.end_time && variation.end_time !== schedule?.end_time) {
      changes.push(`ends ${variation.end_time}`);
    }
    if (variation.language) {
      const baseLang = schedule?.language || 'N/A';
      changes.push(`${variation.language} (instead of ${baseLang})`);
    }

    return changes.length > 0 ? changes.join(', ') : 'No changes';
  };

  const openVariationModal = (schedule, variation = null) => {
    variationSchedule.value = schedule;
    variationToEdit.value = variation;
    showVariationModal.value = true;
  };

  const saveVariation = async (variationData) => {
    if (!variationSchedule.value) return;

    savingVariation.value = true;
    const schedule = variationSchedule.value;
    const existingDetails = [...(schedule.day_details || [])];

    // If editing, replace the existing variation; otherwise add new
    const existingIndex = existingDetails.findIndex(
      (d) => d.day_of_week === variationData.day_of_week,
    );

    if (existingIndex !== -1) {
      existingDetails[existingIndex] = { ...existingDetails[existingIndex], ...variationData };
    } else {
      existingDetails.push(variationData);
    }

    try {
      const response = await http.put(`/api/v1/activity-schedules/${schedule.id}`, {
        day_details: existingDetails,
        has_day_variations: existingDetails.length > 0,
      });
      onScheduleUpdated(response.data.data);
      notify.success('Day variation saved');
    } catch {
      notify.error('Failed to save day variation');
    } finally {
      savingVariation.value = false;
    }
  };

  const deleteVariation = async (schedule, dayOfWeek) => {
    if (!(await notify.confirm(`Remove the variation for ${dayOfWeek}?`))) return;

    savingVariation.value = true;
    const existingDetails = (schedule.day_details || []).filter((d) => d.day_of_week !== dayOfWeek);

    try {
      const response = await http.put(`/api/v1/activity-schedules/${schedule.id}`, {
        day_details: existingDetails,
        has_day_variations: existingDetails.length > 0,
      });
      onScheduleUpdated(response.data.data);
      notify.success('Day variation removed');
    } catch {
      notify.error('Failed to remove day variation');
    } finally {
      savingVariation.value = false;
    }
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
            <v-tabs v-model="activeTab" bg-color="transparent" color="primary" show-arrows>
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
              <v-tab value="adoration-chapel">
                <v-icon class="tw:mr-2" :icon="mdiChurch" /> Adoration Chapel
              </v-tab>
            </v-tabs>

            <v-window v-model="activeTab" class="tw:mt-4">
              <!-- Regular Schedules Tab -->
              <v-window-item value="schedules">
                <div class="tw:flex tw:justify-between tw:mb-4 tw:px-2">
                  <div>
                    <p class="tw:text-base tw:text-gray-700">
                      Recurring schedules with days, periods, and effective dates shown.
                    </p>
                    <p class="tw:text-sm tw:text-gray-500">
                      Expand a schedule to view or add day-specific variations.
                    </p>
                  </div>
                  <PrimaryButton @click="((scheduleToEdit = null), (showScheduleModal = true))">
                    Add Schedule
                  </PrimaryButton>
                </div>

                <v-expansion-panels v-if="schedules.length" variant="accordion">
                  <v-expansion-panel v-for="schedule in schedules" :key="schedule.id">
                    <v-expansion-panel-title>
                      <div class="tw:flex tw:items-center tw:justify-between tw:w-full tw:pr-4">
                        <div class="tw:flex tw:items-center tw:gap-4 tw:flex-1">
                          <v-chip color="primary" size="small" variant="tonal">
                            {{ schedule.type }}
                          </v-chip>
                          <span class="tw:text-sm tw:capitalize">
                            {{ schedule.activity_type_description }}
                          </span>
                          <span class="tw:text-sm tw:text-gray-600">
                            {{ formatScheduleSummary(schedule) }}
                          </span>
                          <span class="tw:text-sm tw:font-medium">
                            {{ schedule.start_time }}
                          </span>
                          <span class="tw:text-sm tw:text-gray-500 tw:capitalize">
                            {{ schedule.language }}
                          </span>
                          <v-chip
                            v-if="schedule.day_details?.length"
                            color="info"
                            size="x-small"
                            variant="tonal"
                          >
                            {{ schedule.day_details.length }} variation{{
                              schedule.day_details.length > 1 ? 's' : ''
                            }}
                          </v-chip>
                        </div>
                        <div class="tw:flex tw:items-center tw:gap-2" @click.stop>
                          <v-icon
                            color="primary"
                            :icon="mdiPencilOutline"
                            size="small"
                            @click="((scheduleToEdit = schedule), (showScheduleModal = true))"
                          />
                          <v-icon
                            v-if="store.user.is_admin"
                            color="error"
                            :disabled="deletingItem"
                            :icon="mdiDeleteOutline"
                            size="small"
                            @click="deleteSchedule(schedule.id)"
                          />
                        </div>
                      </div>
                    </v-expansion-panel-title>

                    <v-expansion-panel-text>
                      <template v-if="supportsVariations(schedule.day_pattern)">
                        <div class="tw:mb-4">
                          <h4 class="tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2">
                            Day Variations
                          </h4>
                          <p class="tw:text-xs tw:text-gray-500 tw:mb-3">
                            Override the base schedule for specific days of the week.
                          </p>
                        </div>

                        <div v-if="schedule.day_details?.length" class="tw:space-y-2 tw:mb-4">
                          <div
                            v-for="variation in schedule.day_details"
                            :key="variation.day_of_week"
                            class="tw:flex tw:items-center tw:justify-between tw:p-3 tw:bg-gray-50 tw:rounded-lg"
                          >
                            <div class="tw:flex tw:items-center tw:gap-3">
                              <v-chip
                                :color="variation.is_skipped ? 'error' : 'success'"
                                size="small"
                                variant="tonal"
                              >
                                <v-icon
                                  v-if="variation.is_skipped"
                                  :icon="mdiCancel"
                                  size="x-small"
                                  start
                                />
                                {{ variation.day_of_week }}
                              </v-chip>
                              <span class="tw:text-sm tw:text-gray-700">
                                {{ formatVariationChange(variation, schedule) }}
                              </span>
                            </div>
                            <div class="tw:flex tw:items-center tw:gap-2">
                              <v-icon
                                color="primary"
                                :disabled="savingVariation"
                                :icon="mdiPencilOutline"
                                size="small"
                                @click="openVariationModal(schedule, variation)"
                              />
                              <v-icon
                                v-if="store.user.is_admin"
                                color="error"
                                :disabled="savingVariation"
                                :icon="mdiDeleteOutline"
                                size="small"
                                @click="deleteVariation(schedule, variation.day_of_week)"
                              />
                            </div>
                          </div>
                        </div>

                        <div v-else class="tw:text-sm tw:text-gray-500 tw:mb-4">
                          No day variations. All days follow the base schedule.
                        </div>

                        <v-btn
                          color="primary"
                          :disabled="savingVariation"
                          :prepend-icon="mdiPlus"
                          size="small"
                          variant="outlined"
                          @click="openVariationModal(schedule)"
                        >
                          Add Variation
                        </v-btn>
                      </template>

                      <div v-else class="tw:text-sm tw:text-gray-500">
                        This schedule type ({{ schedule.day_pattern }}) doesn't support day
                        variations. Only daily, weekdays, and specific-day patterns can have per-day
                        overrides.
                      </div>
                    </v-expansion-panel-text>
                  </v-expansion-panel>
                </v-expansion-panels>

                <v-alert v-else color="info" density="compact" variant="tonal">
                  No regular schedules configured yet. Click "Add Schedule" to create one.
                </v-alert>
              </v-window-item>

              <!-- One-Time Activities Tab -->
              <v-window-item value="one-time">
                <div class="tw:flex tw:justify-between tw:mb-4 tw:px-2">
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
                <div class="tw:flex tw:justify-between tw:mb-4 tw:px-2">
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
                <div class="tw:flex tw:justify-between tw:mb-4 tw:px-2">
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
                <div class="tw:flex tw:justify-between tw:mb-4 tw:px-2">
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

              <!-- Adoration Chapel Tab -->
              <v-window-item value="adoration-chapel">
                <v-skeleton-loader v-if="adorationChapelLoading" type="card" />

                <!-- No chapel yet -->
                <div v-else-if="!adorationChapel" class="tw:text-center tw:py-8">
                  <v-icon class="tw:mb-4" color="grey" :icon="mdiChurch" size="64" />
                  <p class="tw:text-base tw:text-gray-700 tw:mb-2">
                    No adoration chapel has been set up for this church.
                  </p>
                  <PrimaryButton @click="showChapelModal = true">
                    Set Up Adoration Chapel
                  </PrimaryButton>
                </div>

                <!-- Chapel exists -->
                <div v-else>
                  <!-- Chapel Details -->
                  <div
                    class="tw:flex tw:justify-between tw:items-start tw:mb-6 tw:px-2 tw:pb-4 tw:border-b"
                  >
                    <div>
                      <h3 class="tw:text-lg tw:font-medium">
                        {{ adorationChapel.name || 'Adoration Chapel' }}
                      </h3>
                      <p v-if="adorationChapel.notes" class="tw:text-sm tw:text-gray-600 tw:mt-1">
                        {{ adorationChapel.notes }}
                      </p>
                    </div>
                    <div class="tw:flex tw:gap-2">
                      <v-btn
                        color="primary"
                        :icon="mdiPencilOutline"
                        size="small"
                        variant="text"
                        @click="showChapelModal = true"
                      />
                      <v-btn
                        v-if="store.user.is_admin"
                        color="error"
                        :disabled="deletingItem"
                        :icon="mdiDeleteOutline"
                        size="small"
                        variant="text"
                        @click="deleteAdorationChapel"
                      />
                    </div>
                  </div>

                  <!-- Schedules Section -->
                  <div class="tw:mb-6">
                    <div class="tw:flex tw:justify-between tw:mb-4 tw:px-2">
                      <div>
                        <p class="tw:text-base tw:font-medium tw:text-gray-700">Schedules</p>
                        <p class="tw:text-sm tw:text-gray-500">
                          Hours when the adoration chapel is open.
                        </p>
                      </div>
                      <PrimaryButton @click="showChapelScheduleModal = true">
                        Manage Schedules
                      </PrimaryButton>
                    </div>

                    <div v-if="adorationChapel.schedules?.length" class="tw:space-y-2 tw:px-2">
                      <div
                        v-for="schedule in adorationChapel.schedules"
                        :key="schedule.id"
                        class="tw:flex tw:items-center tw:gap-3 tw:p-3 tw:bg-gray-50 tw:rounded-lg"
                      >
                        <v-chip color="primary" size="small" variant="tonal">
                          {{ formatChapelPatternLabel(schedule) }}
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
                          {{ formatChapelTimeDisplay(schedule) }}
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
                          {{ schedule.effective_from && schedule.effective_until ? '\u2013' : '' }}
                          {{
                            schedule.effective_until
                              ? useDayjs(schedule.effective_until).format('MMM D, YYYY')
                              : ''
                          }}
                        </span>
                        <v-chip
                          v-if="schedule.day_details?.length"
                          color="info"
                          size="x-small"
                          variant="tonal"
                        >
                          {{ schedule.day_details.length }} variation{{
                            schedule.day_details.length > 1 ? 's' : ''
                          }}
                        </v-chip>
                      </div>
                    </div>

                    <v-alert v-else class="tw:mx-2" color="info" density="compact" variant="tonal">
                      No schedules configured. Click "Manage Schedules" to add chapel hours.
                    </v-alert>
                  </div>

                  <!-- Exceptions Section -->
                  <div>
                    <div class="tw:flex tw:justify-between tw:mb-4 tw:px-2">
                      <div>
                        <p class="tw:text-base tw:font-medium tw:text-gray-700">Exceptions</p>
                        <p class="tw:text-sm tw:text-gray-500">
                          Dates when the adoration chapel is closed or has modified hours.
                        </p>
                      </div>
                      <PrimaryButton
                        @click="((chapelExceptionToEdit = null), (showChapelExceptionModal = true))"
                      >
                        Add Exception
                      </PrimaryButton>
                    </div>
                    <data-table
                      class="tw:capitalize"
                      :headers="chapelExceptionHeaders"
                      :items="adorationChapel.exceptions || []"
                    >
                      <template #item.reason="{ item }">
                        {{ item.reason || '\u2014' }}
                      </template>
                      <template #item.actions="{ item }">
                        <v-icon
                          class="tw:mr-2"
                          color="primary"
                          :icon="mdiPencilOutline"
                          @click="
                            ((chapelExceptionToEdit = item), (showChapelExceptionModal = true))
                          "
                        />
                        <v-icon
                          v-if="store.user.is_admin"
                          color="error"
                          :disabled="deletingItem"
                          :icon="mdiDeleteOutline"
                          @click="deleteChapelException(item.id)"
                        />
                      </template>
                    </data-table>
                  </div>
                </div>
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

        <DayVariationModal
          v-model:show-modal="showVariationModal"
          :existing-days="getExistingVariationDays(variationSchedule)"
          :schedule="variationSchedule"
          :variation="variationToEdit"
          @saved="saveVariation"
        />

        <AdorationChapelModal
          v-model:show-modal="showChapelModal"
          :chapel="adorationChapel"
          :church-id="churchId"
          @created="onChapelCreated"
          @updated="onChapelUpdated"
        />

        <AdorationChapelScheduleModal
          v-model:show-modal="showChapelScheduleModal"
          :church-id="churchId"
          :schedules="adorationChapel?.schedules || []"
          @updated="onChapelUpdated"
        />

        <AdorationChapelExceptionModal
          v-model:show-modal="showChapelExceptionModal"
          :church-id="churchId"
          :exception="chapelExceptionToEdit"
          @created="onChapelExceptionCreated"
          @updated="onChapelExceptionUpdated"
        />
      </v-row>
    </v-container>
  </app-layout>
</template>
