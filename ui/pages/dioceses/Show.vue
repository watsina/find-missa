<script setup>
  import { ref, onMounted, computed } from 'vue';
  import { notify, http } from '@/plugins';
  import useAuthStore from '@/stores/useAuthStore';
  import ParishesTable from '@/pages/parishes/ParishesTable.vue';
  import AppLayout from '@/layouts/AppLayout';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  // import DioceseDetails from './DioceseDetails.vue';
  import ImportDataModal from '@/components/ImportDataModal';
  import DioceseModal from './DioceseModal.vue';
  import EntityDetails from '@/components/EntityDetails.vue';
  import { mdiCity, mdiEmailFast, mdiHomeCity, mdiPostageStamp, mdiWeb } from '@mdi/js';

  const showingModal = ref(false);

  const props = defineProps({
    dioceseId: { type: [String, Number], required: true },
  });

  const store = useAuthStore();

  const user = computed(() => store?.user);

  const diocese = ref(null);
  const isFetching = ref(false);
  const showUploadModal = ref(false);

  onMounted(() => {
    fetchDioceseDetails();
  });
  const dioceseToEdit = ref(null);

  const fields = computed(() => {
    const data = diocese.value;

    return [
      { icon: mdiHomeCity, text: 'Type', value: data?.type },
      { icon: mdiWeb, text: 'Website', value: data?.website_url, is_link: true },
      { icon: mdiEmailFast, text: 'Contact Email', value: data?.contact_email },
      // { icon: mdiPhoneClassic, text: 'Contact Phone', value: data?.contact_phone },
      { icon: mdiCity, text: 'Office Address', value: data?.office_address },
      { icon: mdiPostageStamp, text: 'Postal Address', value: data?.postal_address },
    ];
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
      <v-row v-else class="tw-py-4">
        <DioceseModal
          v-model:show-modal="showingModal"
          :diocese="dioceseToEdit"
          @updated="((diocese = { ...diocese, ...$event }), (dioceseToEdit = null))"
        />

        <import-data-modal
          v-if="user.is_admin"
          v-model:show-modal="showUploadModal"
          :import-url="`/api/v1/import-churches/${props.dioceseId}`"
        ></import-data-modal>

        <v-col cols="12">
          <EntityDetails :fields="fields" :title="diocese?.name">
            <template #action>
              <PrimaryButton v-if="user.is_admin" class="tw-mr-2" @click="showUploadModal = true">
                Upload data
              </PrimaryButton>
              <PrimaryButton
                variant="outlined"
                @click="((dioceseToEdit = diocese), (showingModal = true))"
              >
                Update Diocese
              </PrimaryButton>
            </template>
          </EntityDetails>
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
