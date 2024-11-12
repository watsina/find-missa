<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import { mdiPencilOutline } from '@mdi/js';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import DataTable from '@/components/DataTable.vue';
  import AppLayout from '@/layouts/AppLayout';
  import ActivityTimeModal from '@/pages/activities/ActivityTimeModal.vue';
  import { notify } from '@/utils';

  const props = defineProps({
    churchId: { type: [String, Number], required: true },
  });

  const church = ref(null);
  const search = ref('');
  const isFetching = ref(false);
  const showingModal = ref(false);
  const activityToEdit = ref(null);

  const headers = [
    { title: 'Activity Type', value: 'type' },
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
      .get(`/api/v1/church/${props.churchId}`)
      .then(({ data }) => {
        church.value = data?.data;
      })
      .catch(() => notify.error('Something went wrong fetching the church details.'))
      .finally(() => (isFetching.value = false));
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
      <v-row v-else class="tw-py-12">
        <activity-time-modal
          v-model:show-modal="showingModal"
          :activity="activityToEdit"
          :church="church"
          @created="church.activities.push($event)"
          @updated="updateActivity"
        />
        <!-- show church details -->
        <v-col cols="12">
          <v-list lines="two">
            <v-list-subheader class="!tw-capitalize !tw-text-lg !tw-font-black">
              {{ `${church?.name} - ${church?.city}` }}
            </v-list-subheader>
            <v-list-item>
              <v-list-item-title>Name</v-list-item-title>
              <v-list-item-subtitle>{{ church?.name }}</v-list-item-subtitle>
              <v-list-item-title>City/Town</v-list-item-title>
              <v-list-item-subtitle>{{ church?.city }}</v-list-item-subtitle>
              <v-list-item-title>Parish</v-list-item-title>
              <v-list-item-subtitle>{{ church?.parish }}</v-list-item-subtitle>
              <v-list-item-title>Coordinates</v-list-item-title>
              <v-list-item-subtitle>{{ church?.coordinates }}</v-list-item-subtitle>
              <v-list-item-title>Contact Email</v-list-item-title>
              <v-list-item-subtitle>{{ church?.contact_email }}</v-list-item-subtitle>
            </v-list-item>
          </v-list>
        </v-col>
        <v-col class="tw-flex tw-justify-between" cols="12">
          <p class="tw-mt-2 tw-text-base tw-text-gray-700">A list of all the activities.</p>
          <PrimaryButton @click="showModal"> Add Activity</PrimaryButton>
        </v-col>
        <v-col cols="12">
          <!-- church activities -->
          <data-table
            class="tw-capitalize"
            :headers="headers"
            :items="church?.activities || []"
            :search="search"
          >
            <template #top>
              <TextInput
                id="search"
                v-model="search"
                autofocus
                class="tw-block tw-mb-2 tw-mx-2"
                error=""
                label="Search"
                placeholder="Search activities..."
                required
                type="text"
              />
            </template>
            <template #item.actions="{ item }">
              <v-icon color="primary" :icon="mdiPencilOutline" @click="showModal(item)"></v-icon>
            </template>
          </data-table>
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
