<script setup>
  import { ref, onMounted, computed } from 'vue';
  import { http } from '@/plugins';
  import {
    mdiCity,
    mdiEmailFast,
    mdiEmailNewsletter,
    mdiHomeCity,
    mdiOfficeBuildingMarker,
    mdiTownHall,
    mdiWeb,
  } from '@mdi/js';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SubParishesTable from '@/pages/sub-parishes/SubParishesTable.vue';
  import ChurchesTable from '@/pages/churches/ChurchesTable.vue';
  import AppLayout from '@/layouts/AppLayout';
  import ParishModal from './ParishModal.vue';
  import { notify } from '@/utils';
  import EntityDetails from '@/components/EntityDetails.vue';
  import PriestsTable from '../priests/PriestsTable.vue';

  const props = defineProps({
    parishId: { type: [String, Number], required: true },
  });

  const parish = ref(null);
  const isFetching = ref(false);
  const showingModal = ref(false);
  const parishToEdit = ref(null);

  onMounted(() => {
    fetchParishDetails();
  });
  const fields = computed(() => {
    const data = parish.value;

    return [
      { icon: mdiHomeCity, text: 'Parish Name', value: data?.name },
      { icon: mdiCity, text: 'Diocese', value: data?.diocese?.name },
      { icon: mdiCity, text: 'City/Town', value: data?.city },
      { icon: mdiTownHall, text: 'District/State', value: data?.district },
      { icon: mdiWeb, text: 'Website', value: data?.website_url, is_link: true },
      { icon: mdiEmailFast, text: 'Contact Email', value: data?.contact_email },
      // { icon: mdiPhoneClassic, text: 'Contact Phone', value: data?.contact_phone },
      { icon: mdiOfficeBuildingMarker, text: 'Office Address', value: data?.office_address },
      { icon: mdiEmailNewsletter, text: 'Postal Address', value: data?.postal_address },
    ];
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
      <h2>Parish Details</h2>
    </template>
    <v-container>
      <v-skeleton-loader v-if="isFetching" type="card"></v-skeleton-loader>
      <v-card v-else-if="!parish">
        <v-card-title> No parish found</v-card-title>
      </v-card>
      <v-row v-else class="tw:py-12">
        <parish-modal
          v-if="parish?.diocese"
          v-model:show-modal="showingModal"
          :diocese="parish?.diocese"
          :parish="parishToEdit"
          @updated="((parish = { ...parish, ...$event }), (parishToEdit = null))"
        ></parish-modal>
        <v-col cols="12">
          <EntityDetails :fields="fields" :title="parish?.name">
            <template #action>
              <PrimaryButton
                variant="outlined"
                @click="((parishToEdit = parish), (showingModal = true))"
              >
                Update Parish
              </PrimaryButton>
            </template>
          </EntityDetails>
        </v-col>
        <v-col cols="12">
          <ChurchesTable
            :churches="parish?.churches || []"
            :is-fetching="isFetching"
            :parish="parish"
            parish-type="parish"
          />
        </v-col>
        <v-col cols="12">
          <SubParishesTable
            :is-fetching="isFetching"
            :parish="parish"
            :sub-parishes="parish?.sub_parishes || []"
          />
        </v-col>
        <v-col cols="12">
          <PriestsTable
            :is-fetching="isFetching"
            :parish="parish"
            :priests="parish?.priests || []"
          />
        </v-col>
      </v-row>
    </v-container>
  </app-layout>
</template>
