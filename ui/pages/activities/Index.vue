<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import { mdiEyeOutline } from '@mdi/js';
  import TextInput from '@/components/TextInput.vue';
  import DataTable from '@/components/DataTable.vue';
  import AppLayout from '@/layouts/AppLayout';

  const activities = ref([]);
  const search = ref('');
  const isFetching = ref(false);

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
    http.get('/api/v1/activity-times').then(({ data }) => {
      activities.value = data?.data;
    });
  };
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw-text-xl tw-font-semibold tw-leading-tight tw-text-gray-800">Activities</h2>
    </template>
    <v-container>
      <v-row class="tw-py-12">
        <v-col cols="12">
          <p class="tw-mt-2 tw-text-base tw-text-gray-700">A list of all the activities.</p>
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
              <v-btn
                color="primary"
                :icon="mdiEyeOutline"
                title="View Activity Details"
                :to="{ name: 'activities.show', params: { activityId: item.id } }"
                variant="text"
              ></v-btn>
            </template>
          </data-table>
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
