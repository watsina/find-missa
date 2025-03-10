<script setup>
  import { onMounted, ref, watch } from 'vue';
  import { useDisplay } from 'vuetify';
  import { http, notify } from '@/plugins';
  import TextInput from './TextInput.vue';
  import { debounce } from 'lodash';

  const props = defineProps({
    headers: { type: Array, required: true },
    url: { type: String, required: true },
    perPageOptions: { type: Array, default: [5, 10, 20, 30] },
    hasSearch: { type: Boolean, default: true },
  });

  const { smAndDown } = useDisplay();

  const items = defineModel('items', { type: Array, default: () => [] });
  const search = ref('');
  const isFetching = ref(false);
  const meta = ref({ per_page: 10, total: 100 });
  const page = ref(1);
  const itemsPerPage = ref(10);

  const fetchData = () => {
    isFetching.value = true;
    http
      .get(props.url, {
        params: {
          page: page.value,
          itemsPerPage: itemsPerPage.value,
          search: search.value,
        },
      })
      .then(({ data }) => {
        items.value = data?.data;
        meta.value = data?.meta;
      })
      .catch(() => notify.error('Failed to fetch data.'))
      .finally(() => (isFetching.value = false));
  };

  onMounted(() => {
    fetchData();
  });

  watch(
    search,
    debounce(() => fetchData(), 300),
  );

  watch([page, itemsPerPage], () => fetchData());

  function refreshData(options) {
    page.value = options.page;
    itemsPerPage.value = options.itemsPerPage;
  }
</script>

<template>
  <v-data-table-server
    v-bind="$attrs"
    :headers="headers"
    :hide-default-header="smAndDown"
    :items-per-page-options="perPageOptions"
    :items="items"
    :mobile="smAndDown"
    mobile-breakpoint="md"
    :search="search"
    :loading="isFetching"
    v-model:items-per-page="itemsPerPage"
    :items-length="meta.total"
    @update:options="refreshData"
  >
    <template v-if="hasSearch" #top>
      <TextInput
        v-model="search"
        class="tw-block tw-mb-2 tw-mx-2"
        clearable
        label="Search"
        placeholder="Search..."
        type="text"
      />
    </template>
    <!-- Expose all slots of the third-party component -->
    <template v-for="(_, name) in $slots" #[name]="slotProps">
      <slot :name="name" v-bind="slotProps || {}"></slot>
    </template>
  </v-data-table-server>
</template>
