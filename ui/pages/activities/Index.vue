<script setup>
  import { mdiEyeOutline } from '@mdi/js';
  import DataTableServer from '@/components/DataTableServer';
  import AppLayout from '@/layouts/AppLayout';

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
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw:text-xl tw:font-semibold tw:leading-tight tw:text-gray-800">Activities</h2>
    </template>
    <v-container>
      <v-row class="tw:py-12">
        <v-col cols="12">
          <p class="tw:mt-2 tw:text-base tw:text-gray-700">A list of all the activities.</p>
        </v-col>
        <v-col cols="12">
          <DataTableServer :headers="headers" item-value="id" url="/api/v1/activity-times">
            <template #item.actions="{ item }">
              <v-btn
                color="primary"
                :icon="mdiEyeOutline"
                title="View Activity Details"
                :to="{ name: 'activities.show', params: { activityId: item.id } }"
                variant="text"
              ></v-btn>
            </template>
          </DataTableServer>
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
