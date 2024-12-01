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

  const showingModal = ref(false);
  const church = defineModel('church', { type: Object, required: true });
  const churchToEdit = ref(null);

  const fields = computed(() => {
    const data = church.value;

    return [
      { icon: mdiHomeCity, text: 'Parish', value: data?.parish?.name },
      { icon: mdiCity, text: 'City/Town', value: data?.city },
      { icon: mdiTownHall, text: 'District/State', value: data?.district },
      { icon: mdiMapMarker, text: 'Coordinates', value: data?.coordinates },
      { icon: mdiEmailFast, text: 'Contact Email', value: data?.contact_email },
      { icon: mdiWeb, text: 'Website', value: data?.website_url },
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
      @updated="(church = $event), (churchToEdit = null)"
    />
    <v-card-title class="tw-py-2">
      <div class="tw-mx-2 tw-flex tw-justify-between">
        <h5 class="tw-text-font-bold tw-text-lg">
          {{ `${church?.name} - ${church?.city}` }}
        </h5>
        <PrimaryButton
          v-if="church?.parish?.id && church?.parishable_type"
          variant="outlined"
          @click="(churchToEdit = church), (showingModal = true)"
        >
          Update Church
        </PrimaryButton>
      </div>
    </v-card-title>
    <v-card class="tw-mt-2">
      <v-card-text>
        <v-row dense no-gutters>
          <v-col v-for="field in fields" :key="field.text" class="tw-m-0 tw-p-0" md="4">
            <v-list>
              <v-list-item dense>
                <template #prepend>
                  <v-icon :color="field.color || ''">{{ field.icon }}</v-icon>
                </template>
                <v-list-item-title>
                  {{ field.text }}
                </v-list-item-title>
                <v-list-item-subtitle>
                  {{ field.value || '....' }}
                </v-list-item-subtitle>
              </v-list-item>
            </v-list>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>
  </div>
</template>
