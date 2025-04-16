<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import { mdiPencilOutline, mdiEyeOutline, mdiDeleteOutline } from '@mdi/js';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import DataTable from '@/components/DataTable.vue';
  import AppLayout from '@/layouts/AppLayout';
  import ChurchDetails from './ChurchDetails.vue';
  import ActivityTimeModal from '@/pages/activities/ActivityTimeModal.vue';
  import { notify } from '@/utils';
  import { isArray } from 'lodash';
  import useAuthStore from '@/stores/useAuthStore';

  const props = defineProps({
    churchId: { type: [String, Number], required: true },
  });

  const store = useAuthStore();

  const church = ref(null);
  const isFetching = ref(false);
  const showingModal = ref(false);
  const deletingActivity = ref(false);
  const activityToEdit = ref(null);

  const headers = [
    { title: 'Activity Type', value: 'type' },
    { title: 'Activity Description', value: 'activity_type_description' },
    { title: 'Church', value: 'church.name' },
    { title: 'Start Time', value: 'start_time' },
    { title: 'End Time', value: 'end_time' },
    { title: 'Language', value: 'language' },
    { title: 'Occurrence', value: 'occurrence' },
    { title: 'Date', value: 'date' },
    { title: 'Day of Week', value: 'day_of_week' },
    { title: '', value: 'actions' },
  ];

  onMounted(() => {
    fetchChurchDetails();
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

  const deleteActivity = async (activityId) => {
    if (
      !(await notify.confirm('You are about to delete this activity, this action is irreversible.'))
    ) {
      return;
    }

    deletingActivity.value = true;
    http
      .delete(`/api/v1/activity-times/${activityId}`)
      .then(() => {
        const items = church.value.activities;
        church.value.activities = items.filter((activity) => activity.id !== activityId);
        notify.success('Activity removed successfully');
      })
      .catch(() => notify.error('Failed to remove the activity.'))
      .finally(() => (deletingActivity.value = false));
  };

  function showModal(data = null) {
    activityToEdit.value = data;
    showingModal.value = true;
  }

  function updateActivity(updates) {
    church.value.activities = church.value?.activities?.map((activity) => {
      if (activity?.id === updates?.id) return updates;
      return activity;
    });
  }
  function createdActivities(activities) {
    if (isArray(activities)) {
      return church.value.activities.push(...activities);
    }
    church.value.activities.push(activities);
  }
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
      <v-row v-else class="tw-py-4">
        <!-- show church details -->
        <v-col cols="12">
          <ChurchDetails v-model:church="church" />
        </v-col>
        <activity-time-modal
          v-model:show-modal="showingModal"
          :activity="activityToEdit"
          :church="church"
          @created="createdActivities"
          @updated="updateActivity"
        />
        <v-col class="tw-flex tw-justify-between" cols="12">
          <p class="tw-mt-2 tw-text-base tw-text-gray-700">A list of church activities.</p>
          <PrimaryButton @click="showModal"> Add Activity</PrimaryButton>
        </v-col>
        <v-col cols="12">
          <!-- church activities -->
          <data-table
            class="tw-capitalize"
            has-search
            :headers="headers"
            :items="church?.activities || []"
          >
            <template #item.actions="{ item }">
              <v-icon
                v-if="item?.id"
                class="tw-mr-2"
                color="primary"
                :icon="mdiPencilOutline"
                @click="showModal(item)"
              />
              <v-icon
                v-if="item?.id"
                color="primary"
                :icon="mdiEyeOutline"
                title="View Activity Details"
                variant="text"
                @click="$router.push({ name: 'activities.show', params: { activityId: item.id } })"
              />
              <v-icon
                v-if="store.user.is_admin"
                color="error"
                :disabled="deletingActivity"
                :icon="mdiDeleteOutline"
                @click="deleteActivity(item.id)"
              />
            </template>
          </data-table>
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
