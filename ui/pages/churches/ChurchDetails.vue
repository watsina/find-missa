<script setup>
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import ChurchModal from './ChurchModal.vue';
  import { computed, ref } from 'vue';
  import {
    mdiCity,
    mdiEmailFast,
    mdiHomeCity,
    mdiMapMarker,
    mdiPhoneClassic,
    mdiTownHall,
    mdiWeb,
  } from '@mdi/js';
  import EntityDetails from '@/components/EntityDetails.vue';

  const church = defineModel('church', { type: Object, required: true });
  const showingModal = ref(false);
  const churchToEdit = ref(null);

  const fields = computed(() => {
    const data = church.value;

    return [
      { icon: mdiHomeCity, text: 'Parish', value: data?.parish?.name },
      { icon: mdiCity, text: 'City/Town', value: data?.city },
      { icon: mdiTownHall, text: 'District/State', value: data?.district },
      { icon: mdiMapMarker, text: 'Coordinates', value: data?.coordinates },
      { icon: mdiMapMarker, text: 'Directions', value: data?.google_maps_link, is_link: true },
      { icon: mdiEmailFast, text: 'Contact Email', value: data?.contact_email },
      { icon: mdiWeb, text: 'Website', value: data?.website_url, is_link: true },
      { icon: mdiPhoneClassic, text: 'Contact Phone', value: data?.contact_phone },
    ];
  });
</script>

<template>
  <div>
    <church-modal
      v-if="church?.parish?.id && church?.parishable_type"
      v-model:show-modal="showingModal"
      :church="churchToEdit"
      :parish="church?.parish"
      :parish-type="church?.parishable_type"
      @updated="((church = $event), (churchToEdit = null))"
    />
    <EntityDetails :fields="fields" :title="`${church?.name} - ${church?.city}`">
      <template #action>
        <PrimaryButton
          v-if="church?.parish?.id && church?.parishable_type"
          variant="outlined"
          @click="((churchToEdit = church), (showingModal = true))"
        >
          Update Church
        </PrimaryButton>
      </template>
    </EntityDetails>
  </div>
</template>
