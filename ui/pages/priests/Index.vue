<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import { mdiPencilOutline } from '@mdi/js';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import DataTable from '@/components/DataTable.vue';
  import TextInput from '@/components/TextInput.vue';
  import AppLayout from '@/layouts/AppLayout';
  import PriestModal from './PriestModal.vue';

  const priests = ref([]);
  const search = ref('');
  const isFetching = ref(false);
  const showingModal = ref(false);
  const priestToEdit = ref(null);

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Email', value: 'email' },
    { title: 'Phone', value: 'phone' },
    { title: 'Parish', value: 'parish' },
    { title: '', value: 'actions' },
  ];

  onMounted(() => {
    fetchPriests();
  });

  const fetchPriests = () => {
    isFetching.value = true;
    http.get('/api/v1/priest').then(({ data }) => {
      priests.value = data?.data;
    });
  };

  function showModal(data = null) {
    priestToEdit.value = data;
    showingModal.value = true;
  }

  function updatePriest(updates) {
    priests.value = priests.value?.map((priest) => {
      if (priest?.id === updates?.id) return updates;
      return priest;
    });
  }
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw-text-xl tw-font-semibold tw-leading-tight tw-text-gray-800">Priests</h2>
    </template>
    <priest-modal
      v-model:show-modal="showingModal"
      :priest="priestToEdit"
      @created="priests.push($event)"
      @updated="updatePriest"
    />
    <v-container>
      <v-row class="tw-py-12">
        <v-col class="tw-flex tw-justify-between" cols="12">
          <p class="tw-mt-2 tw-text-base tw-text-gray-700">A list of all the priests.</p>
          <PrimaryButton @click="showModal"> Add Priest</PrimaryButton>
        </v-col>
        <v-col cols="12">
          <data-table :headers="headers" :items="priests || []" :search="search">
            <template #top>
              <TextInput
                id="search"
                v-model="search"
                autofocus
                class="tw-block tw-mb-2 tw-mx-2"
                error=""
                label="Search"
                placeholder="Search priests..."
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
