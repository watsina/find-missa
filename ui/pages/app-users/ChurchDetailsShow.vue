<script setup>
  const church = defineModel('church', { type: Object, required: true });
</script>

<template>
  <v-row>
    <v-col cols="12">
      <v-sheet>
        <v-container>
          <div class="tw-px-4 sm:tw-px-0 tw-flex tw-justify-between">
            <h3 class="text-base/7 tw-font-semibold tw-text-gray-900">Church Information</h3>
          </div>
          <div class="tw-mt-6">
            <dl class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2">
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Name</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ church?.name }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Parish</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ church?.parish?.name }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">City/Town</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ church?.city }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">District/State</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ church?.district }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Coordinates</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ church?.coordinates }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Contact Email</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ church?.contact_email }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Website URL</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ church?.website_url }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Contact Phone</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ church?.contact_phone }}
                </dd>
              </div>
            </dl>
          </div>
        </v-container>
      </v-sheet>
    </v-col>
  </v-row>
</template>
