<script setup>
  import { ref } from 'vue';
  import { mdiEyeOutline, mdiPencilOutline } from '@mdi/js';
  import DataTableServer from '@/components/DataTableServer';
  import AppLayout from '@/layouts/AppLayout';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import DioceseModal from './DioceseModal.vue';

  const dioceses = ref([]);
  const showingModal = ref(false);
  const editDiocese = ref(null);

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Type', value: 'type' },
    { title: 'Contact Email', value: 'contact_email' },
    { title: 'Website', value: 'website_url' },
    { title: 'Office Address', value: 'office_address' },
    { title: 'Number of Parishes', value: 'parishes_count', align: 'center' },
    { title: '', value: 'actions' },
  ];

  function updateDioceses(updated) {
    const index = dioceses.value?.findIndex((diocese) => diocese?.id === updated?.id);
    if (index === -1) {
      dioceses.value.push(updated);
    }
  }

  const showModal = (diocese = null) => {
    editDiocese.value = diocese;

    showingModal.value = true;
  };
</script>

<template>
  <app-layout>
    <template #head>
      <h2 class="tw:text-xl tw:font-semibold tw:leading-tight tw:text-gray-800">Dioceses</h2>
    </template>
    <diocese-modal
      v-model:show-modal="showingModal"
      :diocese="editDiocese"
      @created="dioceses.push($event)"
      @updated="updateDioceses"
    />
    <v-container>
      <v-row class="tw:py-12">
        <v-col class="tw:flex tw:justify-between" cols="12">
          <p class="tw:mt-2 tw:text-base tw:text-gray-700">A list of all the dioceses.</p>
          <PrimaryButton @click="showModal"> Add a diocese </PrimaryButton>
        </v-col>
        <v-col cols="12">
          <data-table-server
            v-model:items="dioceses"
            :headers="headers"
            no-data-text="No dioceses have been added yet..."
            url="/api/v1/dioceses"
          >
            <template #item.name="{ item }">
              <span class="tw:capitalize">
                {{ item.name }}
              </span>
            </template>
            <template #item.actions="{ item }">
              <v-icon
                class="tw:cursor-pointer tw:mr-2"
                color="primary"
                :icon="mdiPencilOutline"
                title="Edit Diocese"
                @click="showModal(item)"
              ></v-icon>
              <v-btn
                color="primary"
                :icon="mdiEyeOutline"
                title="View Diocese Details"
                :to="{ name: 'dioceses.show', params: { dioceseId: item.code_name } }"
                variant="text"
              ></v-btn>
            </template>
          </data-table-server>
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
