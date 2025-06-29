<script setup>
  import AppLayout from '@/layouts/AppLayout';
  import DataTableServer from '@/components/DataTableServer';
  import { mdiEyeOutline } from '@mdi/js';

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Parish', value: 'parish' },
    { title: 'City/Town', value: 'city' },
    { title: 'District/State', value: 'district' },
    { title: 'Contact Email', value: 'contact_email' },
    { title: '', value: 'actions' },
  ];
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw:text-xl tw:font-semibold tw:leading-tight tw:text-gray-800">Sub Parishes</h2>
    </template>
    <v-container>
      <v-row class="tw:py-12">
        <v-col class="tw:flex tw:justify-between" cols="12">
          <p class="tw:mt-2 tw:text-base tw:text-gray-700">A list of all the sub parishes.</p>
        </v-col>
        <v-col cols="12">
          <DataTableServer :headers="headers" item-value="id" url="/api/v1/sub-parishes">
            <template #item.parish="{ item }">
              {{ item?.parish?.name || parish?.name }}
            </template>
            <template #item.actions="{ item }">
              <v-btn
                class="tw:mr-2"
                color="primary"
                :icon="mdiEyeOutline"
                title="View SubParish Details"
                :to="{ name: 'sub-parishes.show', params: { subParishId: item.id } }"
                variant="text"
              ></v-btn>
            </template>
          </DataTableServer>
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
