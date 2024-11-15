<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import SubParishesTable from '@/pages/sub-parishes/SubParishesTable.vue';
  import AppLayout from '@/layouts/AppLayout';
  import ParishDetails from './ParishDetails.vue';
  import { notify } from '@/utils';

  const props = defineProps({
    parishId: { type: [String, Number], required: true },
  });

  const parish = ref(null);
  const isFetching = ref(false);

  onMounted(() => {
    fetchParishDetails();
  });

  const fetchParishDetails = () => {
    if (!props.parishId) return;
    isFetching.value = true;
    http
      .get(`/api/v1/parishes/${props.parishId}`)
      .then(({ data }) => {
        parish.value = data?.data;
      })
      .catch(() => notify.error('Something went wrong fetching the parish details.'))
      .finally(() => (isFetching.value = false));
  };
</script>

<template>
  <app-layout>
    <template #head>
      <h2>Diocose Details</h2>
    </template>
    <v-container>
      <v-skeleton-loader v-if="isFetching" type="card"></v-skeleton-loader>
      <v-card v-else-if="!parish">
        <v-card-title> No parish found</v-card-title>
      </v-card>
      <v-row v-else class="tw-py-12">
        <v-col cols="12">
          <ParishDetails v-model:parish="parish" />
        </v-col>
        <v-col cols="12">
          <SubParishesTable
            :is-fetching="isFetching"
            :parish="parish"
            :sub-parishes="parish?.sub_parishes || []"
          />
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
