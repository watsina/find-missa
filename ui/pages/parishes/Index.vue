<script setup>
  import { ref, onMounted, watch } from 'vue';
  import { http, notify } from '@/plugins';
  import { mdiEyeOutline, mdiPencilOutline } from '@mdi/js';
  import { debounce } from 'lodash';
  import AppLayout from '@/layouts/AppLayout';
  import TextInput from '@/components/TextInput';

  const parishes = ref([]);
  const isFetching = ref(false);
  const search = ref('');
  const page = ref(1);
  const itemsPerPage = ref(10);
  const meta = ref({ per_page: 10, total: 100 });

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Diocese', value: 'diocese' },
    { title: 'City/Town', value: 'city' },
    { title: 'District/State', value: 'district' },
    { title: 'Number of Churches', value: 'churches_count', align: 'center' },
    { title: 'Number of SubParishes', value: 'sub_parishes_count', align: 'center' },
    { title: '', value: 'actions' },
  ];

  const fetchParishes = () => {
    isFetching.value = true;
    http
      .get('/api/v1/parishes', {
        params: {
          search: search.value,
          page: page.value,
          itemsPerPage: itemsPerPage.value,
        },
      })
      .then(({ data }) => {
        parishes.value = data?.data;
        meta.value = data?.meta;
      })
      .catch(() => notify.error('Failed to fetch parishes.'))
      .finally(() => (isFetching.value = false));
  };

  onMounted(() => {
    fetchParishes({ page: 1, itemsPerPage: 20 });
  });

  watch(
    search,
    debounce(() => fetchParishes(), 300),
  );

  watch([page, itemsPerPage], () => fetchParishes());

  function refreshData(options) {
    page.value = options.page;
    itemsPerPage.value = itemsPerPage.page;
  }
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
          <v-data-table-server
            v-model:items-per-page="meta.per_page"
            :headers="headers"
            :items="parishes"
            :items-per-page-options="[5, 10, 20, 30]"
            :loading="isFetching"
            :search="search"
            :items-length="meta.total"
            mobile-breakpoint="md"
            item-value="id"
            @update:options="refreshData"
          >
            <template #top>
              <TextInput
                v-model="search"
                class="tw-block tw-mb-2 tw-mx-2"
                clearable
                label="Search"
                placeholder="Search..."
                type="text"
              />
            </template>
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
          </v-data-table-server>
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
