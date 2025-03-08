<script setup>
  import { mdiEyeOutline } from '@mdi/js';
  import AppLayout from '@/layouts/AppLayout';
  import DataTableServer from '@/components/DataTableServer';

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Diocese', value: 'diocese' },
    { title: 'City/Town', value: 'city' },
    { title: 'District/State', value: 'district' },
    { title: 'Number of Churches', value: 'churches_count', align: 'center' },
    { title: 'Number of SubParishes', value: 'sub_parishes_count', align: 'center' },
    { title: '', value: 'actions' },
  ];
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw-text-xl tw-font-semibold tw-leading-tight tw-text-gray-800">Parishes</h2>
    </template>
    <v-container>
      <v-row class="tw-py-12">
        <v-col class="tw-flex tw-justify-between" cols="12">
          <p class="tw-mt-2 tw-text-base tw-text-gray-700">A list of all the parishes.</p>
        </v-col>
        <v-col cols="12">
          <DataTableServer :headers="headers" url="/api/v1/parishes" item-value="id">
            <template #item.diocese="{ item }">
              {{ item?.diocese?.name || diocese?.name }}
            </template>
            <template #item.actions="{ item }">
              <v-btn
                class="tw-mr-2"
                color="primary"
                :icon="mdiEyeOutline"
                title="View Parish Details"
                :to="{ name: 'parishes.show', params: { parishId: item.id } }"
                variant="text"
              ></v-btn>
            </template>
          </DataTableServer>
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
