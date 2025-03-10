<script setup>
  import DataTableServer from '@/components/DataTableServer';
  import AppLayout from '@/layouts/AppLayout';

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Email', value: 'email' },
    { title: 'Phone', value: 'phone' },
    { title: 'Parish', value: 'parish' },
    { title: '', value: 'actions' },
  ];
</script>
<template>
  <app-layout>
    <template #head>
      <h2 class="tw-text-xl tw-font-semibold tw-leading-tight tw-text-gray-800">Priests</h2>
    </template>
    <v-container>
      <v-row class="tw-py-12">
        <v-col class="tw-flex tw-justify-between" cols="12">
          <p class="tw-mt-2 tw-text-base tw-text-gray-700">A list of all the priests.</p>
        </v-col>
        <v-col cols="12">
          <data-table-server has-search :headers="headers" url="/api/v1/priests">
            <template #item.parish="{ item }">
              {{ item?.parish?.id ? `${item?.parish?.name} - ${item?.parish?.city}` : `N/A` }}
            </template>
          </data-table-server>
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
