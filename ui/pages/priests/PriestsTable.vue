<script setup>
  import { ref } from 'vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import DataTable from '@/components/DataTable.vue';
  import PriestModal from './PriestModal.vue';
  import { mdiPencilOutline } from '@mdi/js';

  defineProps({
    isFetching: Boolean,
    parish: { type: Object, default: null },
  });

  const priests = defineModel('priests', { type: Array, default: () => [] });

  const showingModal = ref(false);
  const priestToEdit = ref(null);

  const headers = [
    { title: 'Name', value: 'name' },
    { title: 'Email', value: 'email' },
    { title: 'Phone', value: 'phone' },
    { title: 'Parish', value: 'parish' },
    { title: '', value: 'actions' },
  ];

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
  <v-row>
    <PriestModal
      v-if="parish?.id"
      v-model:show-modal="showingModal"
      :parish="parish"
      :priest="priestToEdit"
      @created="priests.push($event)"
      @updated="updatePriest"
    />

    <v-col class="tw:flex tw:justify-between" cols="12">
      <p class="tw:mt-2 tw:text-base tw:text-gray-700">A list of all the priests.</p>
      <PrimaryButton v-if="parish?.id" @click="showModal"> Add Priest</PrimaryButton>
    </v-col>
    <v-col cols="12">
      <data-table has-search :headers="headers" :items="priests || []">
        <template #item.parish="{ item }">
          {{
            item?.parish?.id
              ? `${item?.parish?.name} - ${item?.parish?.city}`
              : `${parish?.name} - ${parish?.city}`
          }}
        </template>
        <template #item.actions="{ item }">
          <v-icon
            v-if="parish?.id"
            color="primary"
            :icon="mdiPencilOutline"
            @click="showModal(item)"
          ></v-icon>
        </template>
      </data-table>
    </v-col>
  </v-row>
</template>
