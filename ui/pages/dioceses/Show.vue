<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import ParishesTable from '@/pages/parishes/ParishesTable.vue';
  import AppLayout from '@/layouts/AppLayout';
  import DioceseDetails from './DioceseDetails.vue';
  import { notify } from '@/utils';

  const props = defineProps({
    dioceseId: { type: [String, Number], required: true },
  });

  const diocese = ref(null);
  const isFetching = ref(false);

  onMounted(() => {
    fetchDioceseDetails();
  });

  const fetchDioceseDetails = () => {
    if (!props.dioceseId) return;
    isFetching.value = true;
    http
      .get(`/api/v1/dioceses/${props.dioceseId}`)
      .then(({ data }) => {
        diocese.value = data?.data;
      })
      .catch(() => notify.error('Something went wrong fetching the diocese details.'))
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
      <v-card v-else-if="!diocese">
        <v-card-title> No diocese found</v-card-title>
      </v-card>
      <v-row v-else class="tw-py-12">
        <v-col cols="12">
          <DioceseDetails v-model:diocese="diocese" />
        </v-col>
        <v-col cols="12">
          <parishes-table
            :diocese="diocese"
            :is-fetching="isFetching"
            :parishes="diocese?.parishes || []"
          />
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
