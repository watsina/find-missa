<script setup>
  import { ref, onMounted } from 'vue';
  import { mdiPencilOutline } from '@mdi/js';
  import { http } from '@/plugins';
  import TextInput from '@/components/TextInput.vue';
  import DataTable from '@/components/DataTable.vue';
  import AppLayout from '@/layouts/AppLayout';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import DioceseModal from './DioceseModal.vue';

  const dioceses = ref([]);
  const search = ref('');
  const isFetching = ref(false);
  const showingModal = ref(false);
  const editDiocese = ref(null);

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Type', value: 'type' },
    { title: 'Contact Email', value: 'contact_email' },
    { title: 'Website', value: 'website_url' },
    { title: 'Office Address', value: 'office_address' },
    { title: 'Postal Address', value: 'postal_address' },
    { title: '', value: 'actions' },
  ];

  onMounted(() => {
    fetchDioceses();
  });

  const fetchDioceses = () => {
    isFetching.value = true;
    http
      .get('/api/v1/diocese')
      .then(({ data }) => {
        dioceses.value = data?.data;
      })
      .finally(() => (isFetching.value = false));
  };

  function updateDioceses(updated) {
    dioceses.value = dioceses.value?.map((diocese) => {
      if (diocese?.id === updated?.id) {
        return updated;
      }
      return diocese;
    });
  }

  const showModal = (diocese = null) => {
    editDiocese.value = diocese;

    showingModal.value = true;
  };
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw-text-xl tw-font-semibold tw-leading-tight tw-text-gray-800">Dioceses</h2>
    </template>
    <diocese-modal
      v-model:show-modal="showingModal"
      :diocese="editDiocese"
      @created="dioceses.push($event)"
      @updated="updateDioceses"
    />
    <v-container>
      <v-row class="tw-py-12">
        <v-col class="tw-flex tw-justify-between" cols="12">
          <p class="tw-mt-2 tw-text-base tw-text-gray-700">A list of all the dioceses.</p>
          <PrimaryButton @click="showModal"> Add a diocese </PrimaryButton>
        </v-col>
        <v-col cols="12">
          <data-table
            :headers="headers"
            :items="dioceses || []"
            :loading="isFetching"
            no-data-text="No dioceses have been added yet..."
            :search="search"
          >
            <template #top>
              <TextInput
                id="search"
                v-model="search"
                autofocus
                class="tw-block tw-mb-2 tw-mx-2"
                error=""
                label="Search"
                placeholder="Search dioceses..."
                required
                type="text"
              />
            </template>
            <template #item.name="{ item }" class="tw-capitalize">
              {{ item.name }}
            </template>
            <template #item.actions="{ item }">
              <v-icon
                class="tw-cursor-pointer"
                color="primary"
                :icon="mdiPencilOutline"
                title="Edit Diocese"
                @click="showModal(item)"
              ></v-icon>
            </template>
          </data-table>
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
