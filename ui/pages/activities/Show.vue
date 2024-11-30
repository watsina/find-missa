<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import { mdiPencilOutline } from '@mdi/js';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import DataTable from '@/components/DataTable.vue';
  import AppLayout from '@/layouts/AppLayout';
  import ActivityDetails from './ActivityDetails.vue';
  import ActivityTimeExceptionModal from './ActivityTimeExceptionModal.vue';

  import { notify } from '@/utils';

  const props = defineProps({
    activityId: { type: [String, Number], required: true },
  });

  const activity = ref(null);
  const isFetching = ref(false);
  const showingModal = ref(false);
  const exceptionToEdit = ref(null);

  const headers = [
    { title: 'Reason', value: 'reason' },
    { title: 'Start Date', value: 'start_date' },
    { title: 'End Date', value: 'end_date' },
    { title: '', value: 'actions' },
  ];

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

  function showModal(data = null) {
    exceptionToEdit.value = data;
    showingModal.value = true;
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
      <v-row v-else class="tw-py-12">
        <activity-time-exception-modal
          v-model:show-modal="showingModal"
          :activity-id="activityId"
          :exception="exceptionToEdit"
          @created="activity.exceptions?.push($event)"
          @updated="updateActivityException"
        />
        <v-col cols="12">
          <ActivityDetails v-model:activity="activity" />
        </v-col>
        <v-col class="tw-flex tw-justify-between" cols="12">
          <p class="tw-mt-2 tw-text-base tw-text-gray-700">
            A list of all exceptions (Days when the activity will not take place).
          </p>
          <PrimaryButton @click="showModal"> Add Activity Exceptions</PrimaryButton>
        </v-col>
        <v-col cols="12">
          <data-table
            class="tw-capitalize"
            has-search
            :headers="headers"
            :items="activity?.exceptions || []"
          >
            <template #item.actions="{ item }">
              <v-icon color="primary" :icon="mdiPencilOutline" @click="showModal(item)"></v-icon>
            </template>
          </data-table>
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
