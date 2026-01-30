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
  } from '@mdi/js';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import DataTable from '@/components/DataTable.vue';
  import AppLayout from '@/layouts/AppLayout';
  import ChurchDetails from './ChurchDetails.vue';
  import ActivityScheduleModal from '@/pages/activities/ActivityScheduleModal.vue';
  import OneTimeActivityModal from '@/pages/activities/OneTimeActivityModal.vue';
  import ScheduleOverrideModal from '@/pages/activities/ScheduleOverrideModal.vue';
  import ScheduleRuleModal from '@/pages/activities/ScheduleRuleModal.vue';
  import { notify } from '@/utils';
  import { isArray } from 'lodash';
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

  // Edit states
  const scheduleToEdit = ref(null);
  const activityToEdit = ref(null);
  const overrideToEdit = ref(null);
  const ruleToEdit = ref(null);

  const deletingItem = ref(false);
  const activeTab = ref('schedules');

  // Schedules (V2) data
  const schedules = ref([]);
  const oneTimeActivities = ref([]);
  const overrides = ref([]);
  const rules = ref([]);

  const scheduleHeaders = [
    { title: 'Type', value: 'type' },
    { title: 'Description', value: 'activity_type_description' },
    { title: 'Pattern', value: 'day_pattern_label' },
    { title: 'Start Time', value: 'start_time' },
    { title: 'Language', value: 'language' },
    { title: '', value: 'actions' },
  ];

  const oneTimeHeaders = [
    { title: 'Type', value: 'type' },
    { title: 'Date', value: 'date' },
    { title: 'Start Time', value: 'start_time' },
    { title: 'Language', value: 'language' },
    { title: 'Details', value: 'details' },
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

  onMounted(() => {
    fetchChurchDetails();
    fetchSchedules();
    fetchOneTimeActivities();
    fetchOverrides();
    fetchRules();
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
                  <p class="tw:text-base tw:text-gray-700">Manage recurring activity schedules.</p>
                  <PrimaryButton @click="((scheduleToEdit = null), (showScheduleModal = true))">
                    Add Schedule
                  </PrimaryButton>
                </div>
                <data-table class="tw:capitalize" :headers="scheduleHeaders" :items="schedules">
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
                  <p class="tw:text-base tw:text-gray-700">
                    Manage single-occurrence events (e.g., weddings, funerals).
                  </p>
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

              <!-- Exceptions Tab -->
              <v-window-item value="overrides">
                <div class="tw:flex tw:justify-between tw:mb-4">
                  <p class="tw:text-base tw:text-gray-700">
                    Manage schedule exceptions (e.g., cancelled mass due to construction).
                  </p>
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
                  <p class="tw:text-base tw:text-gray-700">
                    Manage conditional rules (e.g., no regular mass on Good Friday).
                  </p>
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
      </v-row>
    </v-container>
  </app-layout>
</template>
