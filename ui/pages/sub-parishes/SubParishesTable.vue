<script setup>
  import { ref } from 'vue';
  import { mdiPencilOutline } from '@mdi/js';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import DataTable from '@/components/DataTable.vue';
  import SubParishModal from './SubParishModal.vue';

  defineProps({
    isFetching: Boolean,
    parish: { type: Object, default: null },
  });

  const subParishes = defineModel('subParishes', { type: Array, default: () => [] });

  const search = ref('');
  const showingModal = ref(false);
  const subParishToEdit = ref(null);

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Parish', value: 'parish' },
    { title: 'City', value: 'city' },
    { title: 'Contact Email', value: 'contact_email' },
    { title: 'Website', value: 'website_url' },
    { title: 'Office Address', value: 'office_address' },
    { title: '', value: 'actions' },
  ];

  function showModal(data = null) {
    subParishToEdit.value = data;
    showingModal.value = true;
  }

  function updateSubParish(updates) {
    subParishes.value = subParishes.value?.map((parish) => {
      if (parish?.id === updates?.id) return updates;
      return parish;
    });
  }
</script>

<template>
  <v-row>
    <sub-parish-modal
      v-if="parish?.id"
      v-model:show-modal="showingModal"
      :parish="parish"
      :sub-parish="subParishToEdit"
      @created="subParishes.push($event)"
      @updated="updateSubParish"
    />

    <v-col class="tw-flex tw-justify-between" cols="12">
      <p class="tw-mt-2 tw-text-base tw-text-gray-700">A list of all the sub-parishes.</p>
      <PrimaryButton v-if="parish?.id" @click="showModal"> Add SubParish</PrimaryButton>
    </v-col>
    <v-col cols="12">
      <DataTable :headers="headers" :items="subParishes || []" :search="search">
        <template #top>
          <TextInput
            id="search"
            v-model="search"
            autofocus
            class="tw-block tw-mb-2 tw-mx-2"
            error=""
            label="Search"
            placeholder="Search subParishes..."
            required
            type="text"
          />
        </template>
        <template #item.parish="{ item }">
          {{ item?.parish?.name || parish?.name }}
        </template>
        <template #item.actions="{ item }">
          <v-icon
            v-if="parish?.id"
            color="primary"
            :icon="mdiPencilOutline"
            @click="showModal(item)"
          ></v-icon>
        </template>
      </DataTable>
    </v-col>
  </v-row>
</template>
