<script setup>
  import { ref, onMounted, computed } from 'vue';
  import { http } from '@/plugins';
  import {
    mdiCity,
    mdiEmailFast,
    mdiEmailNewsletter,
    mdiHomeCity,
    mdiOfficeBuildingMarker,
    mdiPhoneClassic,
    mdiTownHall,
    mdiWeb,
  } from '@mdi/js';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SubParishModal from './SubParishModal.vue';
  import ChurchesTable from '@/pages/churches/ChurchesTable.vue';
  import AppLayout from '@/layouts/AppLayout';
  import { notify } from '@/utils';
  import EntityDetails from '@/components/EntityDetails.vue';

  const props = defineProps({
    subParishId: { type: [String, Number], required: true },
  });

  const subParish = ref(null);
  const isFetching = ref(false);
  const showingModal = ref(false);
  const subParishToEdit = ref(null);

  onMounted(() => {
    fetchSubParishDetails();
  });

  const fields = computed(() => {
    const data = subParish.value;

    return [
      { icon: mdiHomeCity, text: 'SubParish Name', value: data?.name },
      { icon: mdiCity, text: 'Parish Name', value: data?.parish?.name },
      { icon: mdiCity, text: 'City/Town', value: data?.city },
      { icon: mdiTownHall, text: 'District/State', value: data?.district },
      { icon: mdiWeb, text: 'Website', value: data?.website_url, is_link: true },
      { icon: mdiEmailFast, text: 'Contact Email', value: data?.contact_email },
      { icon: mdiPhoneClassic, text: 'Contact Phone', value: data?.contact_phone },
      { icon: mdiOfficeBuildingMarker, text: 'Office Address', value: data?.office_address },
      { icon: mdiEmailNewsletter, text: 'Postal Address', value: data?.postal_address },
    ];
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
      <h2>SubParish Details</h2>
    </template>
    <v-container>
      <v-skeleton-loader v-if="isFetching" type="card"></v-skeleton-loader>
      <v-card v-else-if="!subParish">
        <v-card-title> No parish found</v-card-title>
      </v-card>
      <v-row v-else class="tw-py-4">
        <SubParishModal
          v-if="subParish?.parish"
          v-model:show-modal="showingModal"
          :parish="subParish?.parish"
          :sub-parish="subParishToEdit"
          @updated="(subParish = { ...subParish, ...$event }), (subParishToEdit = null)"
        ></SubParishModal>
        <v-col cols="12">
          <EntityDetails :fields="fields" :title="subParish?.name">
            <template #action>
              <PrimaryButton
                variant="outlined"
                @click="(subParishToEdit = subParish), (showingModal = true)"
              >
                Update SubParish
              </PrimaryButton>
            </template>
          </EntityDetails>
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
