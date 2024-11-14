<script setup>
  import { ref } from 'vue';
  import { mdiPencilOutline } from '@mdi/js';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import DataTable from '@/components/DataTable.vue';
  import ParishModal from './ParishModal.vue';

  defineProps({
    isFetching: Boolean,
    diocese: { type: Object, default: null },
  });

  const parishes = defineModel('parishes', { type: Array, default: () => [] });

  const search = ref('');
  const showingModal = ref(false);
  const parishToEdit = ref(null);

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Diocese', value: 'diocese' },
    { title: 'City', value: 'city' },
    { title: 'Contact Email', value: 'contact_email' },
    { title: 'Website', value: 'website_url' },
    { title: 'Office Address', value: 'office_address' },
    { title: '', value: 'actions' },
  ];

  function showModal(data = null) {
    parishToEdit.value = data;
    showingModal.value = true;
  }

  function updateParish(updates) {
    parishes.value = parishes.value?.map((parish) => {
      if (parish?.id === updates?.id) return updates;
      return parish;
    });
  }
</script>

<template>
  <v-row>
    <parish-modal
      v-if="diocese?.id"
      v-model:show-modal="showingModal"
      :diocese="diocese"
      :parish="parishToEdit"
      :search="search"
      @created="parishes.push($event)"
      @updated="updateParish"
    ></parish-modal>

    <v-col class="tw-flex tw-justify-between" cols="12">
      <p class="tw-mt-2 tw-text-base tw-text-gray-700">A list of all the parishes.</p>
      <PrimaryButton v-if="diocese?.id" @click="showModal"> Add Parish</PrimaryButton>
    </v-col>
    <v-col cols="12">
      <DataTable :headers="headers" :items="parishes || []">
        <template #top>
          <TextInput
            id="search"
            v-model="search"
            autofocus
            class="tw-block tw-mb-2 tw-mx-2"
            error=""
            label="Search"
            placeholder="Search parishes..."
            required
            type="text"
          />
        </template>
        <template #item.diocese="{ item }">
          {{ item?.diocese?.name || diocese?.name }}
        </template>
        <template #item.actions="{ item }">
          <v-icon
            v-if="diocese?.id"
            color="primary"
            :icon="mdiPencilOutline"
            @click="showModal(item)"
          ></v-icon>
        </template>
      </DataTable>
    </v-col>
  </v-row>
</template>
