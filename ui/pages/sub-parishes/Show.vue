<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import ChurchesTable from '@/pages/churches/ChurchesTable.vue';
  import AppLayout from '@/layouts/AppLayout';
  import SubParishDetails from './SubParishDetails.vue';
  import { notify } from '@/utils';

  const props = defineProps({
    subParishId: { type: [String, Number], required: true },
  });

  const subParish = ref(null);
  const isFetching = ref(false);

  onMounted(() => {
    fetchSubParishDetails();
  });

  const fetchSubParishDetails = () => {
    if (!props.subParishId) return;
    isFetching.value = true;
    http
      .get(`/api/v1/sub-parishes/${props.subParishId}`)
      .then(({ data }) => {
        subParish.value = data?.data;
      })
      .catch(() => notify.error('Something went wrong fetching the sub-parish details.'))
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
      <v-card v-else-if="!subParish">
        <v-card-title> No parish found</v-card-title>
      </v-card>
      <v-row v-else class="tw-py-12">
        <v-col cols="12">
          <SubParishDetails v-model:sub-parish="subParish" />
        </v-col>
        <v-col cols="12">
          <ChurchesTable
            :churches="subParish?.churches || []"
            :is-fetching="isFetching"
            :parish="subParish"
            parish-type="sub-parish"
          />
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
