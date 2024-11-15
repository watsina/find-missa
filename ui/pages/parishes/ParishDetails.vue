<script setup>
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import ParishModal from './ParishModal.vue';
  import { ref } from 'vue';

  const showingModal = ref(false);
  const parish = defineModel('parish', { type: Object, required: true });
  const parishToEdit = ref(null);
</script>

<template>
  <v-row>
    <parish-modal
      v-if="parish?.diocese"
      v-model:show-modal="showingModal"
      :diocese="parish?.diocese"
      :parish="parishToEdit"
      @updated="(parish = { ...parish, ...$event }), (parishToEdit = null)"
    ></parish-modal>
    <v-col cols="12">
      <v-sheet>
        <v-container>
          <div class="tw-px-4 sm:tw-px-0 tw-flex tw-justify-between">
            <h3 class="text-base/7 tw-font-semibold tw-text-gray-900">Parish Information</h3>
            <PrimaryButton
              variant="outlined"
              @click="(parishToEdit = parish), (showingModal = true)"
            >
              Update Parish
            </PrimaryButton>
          </div>
          <div class="tw-mt-6">
            <dl class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2">
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Parish Name</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2 tw-capitalize">
                  {{ parish?.name }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">City</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2 tw-capitalize">
                  {{ parish?.city }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Diocese</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2 tw-capitalize">
                  {{ parish?.diocese?.name }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Contact Email</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ parish?.contact_email }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Website URL</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ parish?.website_url }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Office Address</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ parish?.office_address }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Postal Address</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ parish?.postal_address }}
                </dd>
              </div>
            </dl>
          </div>
        </v-container>
      </v-sheet>
    </v-col>
  </v-row>
</template>
