<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import { useDisplay } from 'vuetify';
  import { mdiPencilOutline } from '@mdi/js';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import AppLayout from '@/layouts/AppLayout';
  import ParishModal from './ParishModal.vue';

  const { smAndDown } = useDisplay();
  const parishes = ref([]);
  const search = ref('');
  const isFetching = ref(false);
  const showingModal = ref(false);
  const parishToEdit = ref(null);

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Diocese', value: 'diocese' },
    { title: 'Office Address', value: 'office_address' },
    { title: 'District', value: 'district' },
    { title: '', value: 'actions' },
  ];

  onMounted(() => {
    fetchParishes();
  });

  const fetchParishes = () => {
    isFetching.value = true;
    http.get('/api/v1/parish').then(({ data }) => {
      parishes.value = data?.data;
    });
  };

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
  <app-layout>
    <template #head>
      <h2 class="tw-text-xl tw-font-semibold tw-leading-tight tw-text-gray-800">Parishes</h2>
    </template>
    <parish-modal
      v-model:show-modal="showingModal"
      :parish="parishToEdit"
      @created="parishes.push($event)"
      @updated="updateParish"
    ></parish-modal>
    <v-container>
      <v-row class="tw-py-12">
        <v-col class="tw-flex tw-justify-between" cols="12">
          <p class="tw-mt-2 tw-text-base tw-text-gray-700">A list of all the parishes.</p>
          <PrimaryButton @click="showModal"> Add Parish</PrimaryButton>
        </v-col>
        <v-col cols="12">
          <v-data-table
            class="tw-capitalize"
            :headers="headers"
            :items="parishes || []"
            :mobile="smAndDown"
            mobile-breakpoint="md"
          >
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
            <template #item.actions="{ item }">
              <v-icon color="primary" :icon="mdiPencilOutline" @click="showModal(item)"></v-icon>
            </template>
          </v-data-table>
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
