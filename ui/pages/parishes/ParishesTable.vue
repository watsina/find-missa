<script setup>
  import { ref } from 'vue';
  import { mdiEyeOutline, mdiPencilOutline } from '@mdi/js';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import DataTable from '@/components/DataTable.vue';
  import ParishModal from './ParishModal.vue';

  defineProps({
    isFetching: Boolean,
    diocese: { type: Object, default: null },
  });

  const parishes = defineModel('parishes', { type: Array, default: () => [] });

  const showingModal = ref(false);
  const parishToEdit = ref(null);
  const isFetching = ref(false);

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Diocese', value: 'diocese' },
    { title: 'City/Town', value: 'city' },
    { title: 'District/State', value: 'district' },
    { title: 'Number of Churches', value: 'churches_count', align: 'center' },
    { title: 'Number of SubParishes', value: 'sub_parishes_count', align: 'center' },
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
      @created="parishes.push($event)"
      @updated="updateParish"
    ></parish-modal>

    <v-col class="tw-flex tw-justify-between" cols="12">
      <p class="tw-mt-2 tw-text-base tw-text-gray-700">A list of all the parishes.</p>
      <PrimaryButton v-if="diocese?.id" @click="showModal"> Add Parish</PrimaryButton>
    </v-col>
    <v-col cols="12">
      <DataTable :headers="headers" :items="parishes || []" :loading="isFetching">
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
          <v-btn
            class="tw-mr-2"
            color="primary"
            :icon="mdiEyeOutline"
            title="View Parish Details"
            :to="{ name: 'parishes.show', params: { parishId: item.id } }"
            variant="text"
          ></v-btn>
        </template>
      </DataTable>
    </v-col>
  </v-row>
</template>
