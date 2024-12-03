<script setup>
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import DioceseModal from './DioceseModal.vue';
  import { computed, ref } from 'vue';
  import {
    mdiCity,
    mdiEmailFast,
    mdiHomeCity,
    mdiPhoneClassic,
    mdiPostageStamp,
    mdiWeb,
  } from '@mdi/js';

  const showingModal = ref(false);
  const diocese = defineModel('diocese', { type: Object, required: true });
  const dioceseToEdit = ref(null);

  const fields = computed(() => {
    const data = diocese.value;

    return [
      { icon: mdiHomeCity, text: 'Type', value: data?.type },
      { icon: mdiWeb, text: 'Website', value: data?.website_url, is_link: true },
      { icon: mdiEmailFast, text: 'Contact Email', value: data?.contact_email },
      { icon: mdiPhoneClassic, text: 'Contact Phone', value: data?.contact_phone },
      { icon: mdiCity, text: 'Office Address', value: data?.office_address },
      { icon: mdiPostageStamp, text: 'Postal Address', value: data?.postal_address },
    ];
  });
</script>

<template>
  <div>
    <DioceseModal
      v-model:show-modal="showingModal"
      :diocese="dioceseToEdit"
      @updated="(diocese = { ...diocese, ...$event }), (dioceseToEdit = null)"
    />
    <v-card-title class="tw-py-2">
      <v-row>
        <v-col cols="12" md="8">
          <h5 class="tw-text-font-bold tw-text-lg">
            {{ `${diocese?.name}` }}
          </h5>
        </v-col>
        <v-col class="tw-text-right" cols="12" md="4">
          <PrimaryButton
            variant="outlined"
            @click="(dioceseToEdit = diocese), (showingModal = true)"
          >
            Update Diocese
          </PrimaryButton>
        </v-col>
      </v-row>
    </v-card-title>
    <v-card class="tw-mt-2">
      <v-card-text>
        <v-row dense no-gutters>
          <v-col v-for="field in fields" :key="field.text" class="tw-m-0 tw-p-0" cols="12" md="4">
            <v-list>
              <v-list-item dense>
                <template #prepend>
                  <v-icon :color="field.color || ''">{{ field.icon }}</v-icon>
                </template>
                <v-list-item-title>
                  {{ field.text }}
                </v-list-item-title>
                <v-list-item-subtitle
                  v-if="field?.is_link && field?.value"
                  class="tw-text-indigo-700"
                  :href="field?.value"
                  tag="a"
                  target="_blank"
                >
                  {{ field.value || '....' }}
                </v-list-item-subtitle>
                <v-list-item-subtitle v-else>
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
