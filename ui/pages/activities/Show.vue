<script setup>
  import { ref, onMounted, computed } from 'vue';
  import { http } from '@/plugins';
  import {
    mdiPencilOutline,
    mdiCity,
    mdiClockTimeOne,
    mdiClockTimeNine,
    mdiTranslateVariant,
    mdiRepeatVariant,
    mdiCalendarToday,
  } from '@mdi/js';
  import ActivityTimeModal from './ActivityTimeModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import DataTable from '@/components/DataTable.vue';
  import AppLayout from '@/layouts/AppLayout';
  import ActivityTimeExceptionModal from './ActivityTimeExceptionModal.vue';
  import EntityDetails from '@/components/EntityDetails.vue';

  import { notify } from '@/utils';

  const props = defineProps({
    activityId: { type: [String, Number], required: true },
  });

  const activity = ref(null);
  const isFetching = ref(false);
  const showingExceptionModal = ref(false);
  const showingActivityModal = ref(false);
  const activityToEdit = ref(null);
  const exceptionToEdit = ref(null);

  const headers = [
    { title: 'Reason', value: 'reason' },
    { title: 'Start Date', value: 'start_date' },
    { title: 'End Date', value: 'end_date' },
    { title: '', value: 'actions' },
  ];

  const fields = computed(() => {
    const data = activity.value;

    return [
      { icon: mdiCity, text: 'Church', value: `${data?.church?.name} - ${data?.church?.city}` },
      { icon: mdiClockTimeOne, text: 'Start Time', value: data?.start_time },
      { icon: mdiClockTimeNine, text: 'End Time', value: data?.end_time },
      { icon: mdiTranslateVariant, text: 'Language', value: data?.language },
      { icon: mdiRepeatVariant, text: 'Occurrence', value: data?.occurrence },
      data?.occurrence === 'weekly' && {
        icon: mdiCalendarToday,
        text: 'Day of Week',
        value: data?.day_of_week,
      },
      data?.occurrence === 'one-time' && {
        icon: mdiCalendarToday,
        text: 'Date',
        value: data?.date,
      },
    ].filter((item) => !!item);
  });

  onMounted(() => {
    fetchActivityDetails();
  });

  const fetchActivityDetails = () => {
    if (!props.activityId) return;
    isFetching.value = true;
    http
      .get(`/api/v1/activity-times/${props.activityId}`)
      .then(({ data }) => {
        activity.value = data?.data;
      })
      .catch(() => notify.error('Something went wrong fetching the activity details.'))
      .finally(() => (isFetching.value = false));
  };

  function showExceptionModal(data = null) {
    exceptionToEdit.value = data;
    showingExceptionModal.value = true;
  }

  function updateActivityException(updates) {
    activity.value.exceptions = activity.value?.exceptions?.map((exception) => {
      if (exception?.id === updates?.id) return updates;
      return exception;
    });
  }
</script>

<template>
  <app-layout>
    <template #head>
      <h2>Activity Details</h2>
    </template>
    <v-container>
      <v-skeleton-loader v-if="isFetching" type="card"></v-skeleton-loader>
      <v-card v-else-if="!activity">
        <v-card-title> No activity found</v-card-title>
      </v-card>
      <v-row v-else class="tw-py-4">
        <ActivityTimeModal
          v-model:show-modal="showingActivityModal"
          :activity="activityToEdit"
          :church="activity?.church"
          @updated="(activity = { ...activity, ...$event }), (activityToEdit = null)"
        />
        <v-col cols="12">
          <EntityDetails :fields="fields" :title="activity?.type">
            <template #action>
              <PrimaryButton
                variant="outlined"
                @click="(activityToEdit = activity), (showingActivityModal = true)"
              >
                Update Activity
              </PrimaryButton>
            </template>
          </EntityDetails>
        </v-col>
        <v-col class="tw-flex tw-justify-between" cols="12">
          <p class="tw-mt-2 tw-text-base tw-text-gray-700">
            A list of all exceptions (Days when the activity will not take place).
          </p>
          <PrimaryButton @click="showExceptionModal"> Add Activity Exceptions</PrimaryButton>
        </v-col>
        <v-col cols="12">
          <activity-time-exception-modal
            v-model:show-modal="showingExceptionModal"
            :activity-id="activityId"
            :exception="exceptionToEdit"
            @created="activity.exceptions?.push($event)"
            @updated="updateActivityException"
          />
          <data-table
            class="tw-capitalize"
            has-search
            :headers="headers"
            :items="activity?.exceptions || []"
          >
            <template #item.actions="{ item }">
              <v-icon
                color="primary"
                :icon="mdiPencilOutline"
                @click="showExceptionModal(item)"
              ></v-icon>
            </template>
          </data-table>
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
