<script setup>
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import DioceseModal from './DioceseModal.vue';
  import { ref } from 'vue';

  const showingModal = ref(false);
  const diocese = defineModel('diocese', { type: Object, required: true });
  const dioceseToEdit = ref(null);
</script>

<template>
  <v-row>
    <DioceseModal
      v-model:show-modal="showingModal"
      :diocese="dioceseToEdit"
      @updated="(diocese = { ...diocese, ...$event }), (dioceseToEdit = null)"
    />
    <v-col cols="12">
      <v-sheet>
        <v-container>
          <div class="tw-px-4 sm:tw-px-0 tw-flex tw-justify-between">
            <h3 class="text-base/7 tw-font-semibold tw-text-gray-900">Diocese Information</h3>
            <PrimaryButton
              variant="outlined"
              @click="(dioceseToEdit = diocese), (showingModal = true)"
            >
              Update Diocese
            </PrimaryButton>
          </div>
          <div class="tw-mt-6">
            <dl class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2">
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Diocese Name</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2 tw-capitalize">
                  {{ diocese?.name }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Diocese Type</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2 tw-capitalize">
                  {{ diocese?.type }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Contact Email</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ diocese?.contact_email }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Website URL</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ diocese?.website_url }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Office Address</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ diocese?.office_address }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Postal Address</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ diocese?.postal_address }}
                </dd>
              </div>
            </dl>
          </div>
        </v-container>
      </v-sheet>
    </v-col>
  </v-row>
</template>
