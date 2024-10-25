<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import { useDisplay } from 'vuetify';
  import { mdiPencilOutline } from '@mdi/js';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import AppLayout from '@/layouts/AppLayout';
  import SubParishModal from './SubParishModal.vue';

  const { smAndDown } = useDisplay();
  const subParishes = ref([]);
  const search = ref('');
  const isFetching = ref(false);
  const showingModal = ref(false);
  const subParishToEdit = ref(null);

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Parish', value: 'parish' },
    { title: 'Office Address', value: 'office_address' },
    { title: 'Postal Address', value: 'postal_address' },
    { title: '', value: 'actions' },
  ];

  onMounted(() => {
    fetchSubParishes();
  });

  const fetchSubParishes = () => {
    isFetching.value = true;
    http.get('/api/v1/sub-parish').then(({ data }) => {
      subParishes.value = data?.data;
    });
  };

  function showModal(data = null) {
    subParishToEdit.value = data;
    showingModal.value = true;
  }

  function updateSubParish(updates) {
    subParishes.value = subParishes.value?.map((subParish) => {
      if (subParish?.id === updates?.id) return updates;
      return subParish;
    });
  }
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw-text-xl tw-font-semibold tw-leading-tight tw-text-gray-800">Sub Parishes</h2>
    </template>
    <sub-parish-modal
      v-model:show-modal="showingModal"
      :sub-parish="subParishToEdit"
      @created="subParishes.push($event)"
      @updated="updateSubParish"
    />
    <v-container>
      <v-row class="tw-py-12">
        <v-col class="tw-flex tw-justify-between" cols="12">
          <p class="tw-mt-2 tw-text-base tw-text-gray-700">A list of all the SubParishes.</p>
          <PrimaryButton @click="showModal"> Add Sub Parish</PrimaryButton>
        </v-col>
        <v-col cols="12">
          <v-data-table
            :headers="headers"
            :items="subParishes || []"
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
                placeholder="Search subParishes..."
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
