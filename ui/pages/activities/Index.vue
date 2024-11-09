<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import { mdiPencilOutline } from '@mdi/js';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import DataTable from '@/components/DataTable.vue';
  import AppLayout from '@/layouts/AppLayout';
  import ActivityTimeModal from './ActivityTimeModal.vue';

  const activities = ref([]);
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
    fetchActivities();
  });

  const fetchActivities = () => {
    isFetching.value = true;
    http.get('/api/v1/activity-time').then(({ data }) => {
      activities.value = data?.data;
    });
  };

  function showModal(data = null) {
    activityToEdit.value = data;
    showingModal.value = true;
  }

  function updateActivity(updates) {
    activities.value = activities.value?.map((activity) => {
      if (activity?.id === updates?.id) return updates;
      return activity;
    });
  }
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw-text-xl tw-font-semibold tw-leading-tight tw-text-gray-800">Activities</h2>
    </template>
    <activity-time-modal
      v-model:show-modal="showingModal"
      :activity="activityToEdit"
      @created="activities.push($event)"
      @updated="updateActivity"
    />
    <v-container>
      <v-row class="tw-py-12">
        <v-col class="tw-flex tw-justify-between" cols="12">
          <p class="tw-mt-2 tw-text-base tw-text-gray-700">A list of all the activities.</p>
          <PrimaryButton @click="showModal"> Add Activity</PrimaryButton>
        </v-col>
        <v-col cols="12">
          <data-table
            class="tw-capitalize"
            :headers="headers"
            :items="activities || []"
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
