<script setup>
  import { ref, onMounted } from 'vue';
  import { http } from '@/plugins';
  import DataTable from '@/components/DataTable.vue';
  import AppUserLayout from '@/layouts/AppUserLayout';
  import ChurchDetails from './ChurchDetailsShow.vue';
  import { notify } from '@/utils';

  const props = defineProps({
    churchId: { type: [String, Number], required: true },
  });

  const church = ref(null);
  const isFetching = ref(false);
  const mass = ref([]);
  const confession = ref([]);
  const adoration = ref([]);

  const headers = [
    { title: 'Start Time', value: 'start_time' },
    { title: 'End Time', value: 'end_time' },
    { title: 'Language', value: 'language' },
    { title: 'Occurrence', value: 'occurrence' },
    { title: 'Day of Week', value: 'day_of_week' },
    { title: 'Date', value: 'date' },
  ];

  onMounted(() => {
    fetchChurchDetails();
  });

  const fetchChurchDetails = () => {
    if (!props.churchId) return;
    isFetching.value = true;
    http
      .get(`/api/v1/church-details/${props.churchId}`)
      .then(({ data }) => {
        church.value = data?.data?.details || {};
        mass.value = data?.data?.mass || [];
        adoration.value = data?.data?.adoration || [];
        confession.value = data?.data?.confession || [];
      })
      .catch(() => notify.error('Something went wrong fetching the church details.'))
      .finally(() => (isFetching.value = false));
  };
</script>

<template>
  <AppUserLayout>
    <template #head>
      <h2>Church Details</h2>
    </template>
    <v-container>
      <v-skeleton-loader v-if="isFetching" type="card"></v-skeleton-loader>
      <v-card v-else-if="!church">
        <v-card-title> No church found</v-card-title>
      </v-card>
      <v-row v-else class="tw-py-12">
        <!-- show church details -->
        <v-col cols="12">
          <ChurchDetails v-model:church="church" />
        </v-col>
        <v-col class="tw-flex tw-justify-between" cols="12">
          <p class="tw-mt-2 tw-text-base tw-text-gray-700">A list of church activities.</p>
        </v-col>
        <v-col cols="12">
          <v-expansion-panels>
            <v-expansion-panel title="Mass">
              <v-expansion-panel-text>
                <data-table class="tw-capitalize" has-search :headers="headers" :items="mass || []">
                </data-table>
              </v-expansion-panel-text>
            </v-expansion-panel>
            <v-expansion-panel title="Confession">
              <v-expansion-panel-text>
                <data-table
                  class="tw-capitalize"
                  has-search
                  :headers="headers"
                  :items="confession || []"
                >
                </data-table>
              </v-expansion-panel-text>
            </v-expansion-panel>
            <v-expansion-panel title="Adoration">
              <v-expansion-panel-text>
                <data-table
                  class="tw-capitalize"
                  has-search
                  :headers="headers"
                  :items="adoration || []"
                >
                </data-table>
              </v-expansion-panel-text>
            </v-expansion-panel>
          </v-expansion-panels>
        </v-col>
      </v-row>
    </v-container>
  </AppUserLayout>
</template>
