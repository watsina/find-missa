<script setup>
  import { ref } from 'vue';
  import { mdiEyeOutline, mdiPencilOutline } from '@mdi/js';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import DataTable from '@/components/DataTable.vue';
  import ChurchModal from './ChurchModal.vue';

  defineProps({
    isFetching: Boolean,
    parish: { type: Object, default: null },
    parishType: { type: String, default: null },
  });

  const churches = defineModel('churches', { type: Array, default: () => [] });

  const showingModal = ref(false);
  const churchToEdit = ref(null);

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Type', value: 'type' },
    { title: 'Parish', value: 'parish' },
    { title: 'City/Town', value: 'city' },
    { title: 'District/State', value: 'district' },
    { title: 'Coordinates', value: 'coordinates' },
    { title: '', value: 'actions' },
  ];

  function showModal(data = null) {
    churchToEdit.value = data;
    showingModal.value = true;
  }

  function updateChurch(updates) {
    churches.value = churches.value?.map((parish) => {
      if (parish?.id === updates?.id) return updates;
      return parish;
    });
  }
</script>

<template>
  <v-row>
    <ChurchModal
      v-if="parish?.id && parishType"
      v-model:show-modal="showingModal"
      :church="churchToEdit"
      :parish="parish"
      :parish-type="parishType"
      @created="churches.push($event)"
      @updated="updateChurch"
    />

    <v-col class="tw-flex tw-justify-between" cols="12">
      <p class="tw-mt-2 tw-text-base tw-text-gray-700">A list of all the churches.</p>
      <PrimaryButton v-if="parishType && parish?.id" @click="showModal"> Add Church</PrimaryButton>
    </v-col>
    <v-col cols="12">
      <data-table has-search :headers="headers" :items="churches || []">
        <template #item.parish="{ item }">
          {{ item?.parish?.id ? `${item?.parish?.name} - ${item?.parish?.city}` : '' }}
        </template>
        <template #item.actions="{ item }">
          <v-icon
            v-if="parish?.id && parishType"
            class="md:tw-mr-2"
            color="primary"
            :icon="mdiPencilOutline"
            title="Edit Church Details"
            @click="showModal(item)"
          ></v-icon>
          <v-btn
            color="primary"
            :icon="mdiEyeOutline"
            title="View Church Details"
            :to="{ name: 'churches.show', params: { churchId: item.id } }"
            variant="text"
          ></v-btn>
        </template>
      </data-table>
    </v-col>
  </v-row>
</template>
