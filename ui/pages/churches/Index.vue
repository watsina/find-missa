<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import { useDisplay } from 'vuetify';
  import { mdiPencilOutline } from '@mdi/js';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import AppLayout from '@/layouts/AppLayout';
  import ChurchModal from './ChurchModal.vue';

  const { smAndDown } = useDisplay();
  const churches = ref([]);
  const search = ref('');
  const isFetching = ref(false);
  const showingModal = ref(false);
  const churchToEdit = ref(null);

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Coordinates', value: 'coordinates' },
    { title: 'Contact Email', value: 'contact_email' },
    { title: 'Postal Address', value: 'postal_address' },
    { title: 'Physical Address', value: 'physical_address' },
    { title: 'Parish', value: 'parish' },
    { title: '', value: 'actions' },
  ];

  onMounted(() => {
    fetchChurches();
  });

  const fetchChurches = () => {
    isFetching.value = true;
    http.get('/api/v1/church').then(({ data }) => {
      console.info({ data });
      churches.value = data?.data;
    });
  };

  function showModal(data = null) {
    churchToEdit.value = data;
    showingModal.value = true;
  }

  function updateChurch(updates) {
    churches.value = churches.value?.map((church) => {
      if (church?.id === updates?.id) return updates;
      return church;
    });
  }
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw-text-xl tw-font-semibold tw-leading-tight tw-text-gray-800">Churches</h2>
    </template>
    <church-modal
      v-model:show-modal="showingModal"
      :church="churchToEdit"
      @created="churches.push($event)"
      @updated="updateChurch"
    />
    <v-container>
      <v-row class="tw-py-12">
        <v-col class="tw-flex tw-justify-between" cols="12">
          <p class="tw-mt-2 tw-text-sm tw-text-gray-700">A list of all the churches.</p>
          <PrimaryButton @click="showModal"> Add Church</PrimaryButton>
        </v-col>
        <v-col cols="12">
          <v-data-table
            :headers="headers"
            :items="churches || []"
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
                placeholder="Search churches..."
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
