<script setup>
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import ActivityTimeModal from './ActivityTimeModal.vue';
  import { ref } from 'vue';

  const showingModal = ref(false);
  const activity = defineModel('activity', { type: Object, required: true });
  const activityToEdit = ref(null);

  function showModal() {
    activityToEdit.value = activity.value;
    showingModal.value = true;
  }

  function updateActivity(updates) {
    activity.value = { ...activity, ...updates };
    activityToEdit.value = null;
  }
</script>

<template>
  <v-row>
    <ActivityTimeModal
      v-model:show-modal="showingModal"
      :activity="activityToEdit"
      :church="activity?.church"
      @updated="updateActivity"
    />
    <v-col cols="12">
      <v-sheet>
        <v-container>
          <div class="tw-px-4 sm:tw-px-0 tw-flex tw-justify-between">
            <h3 class="text-base/7 tw-font-semibold tw-text-gray-900">Activity Information</h3>
            <PrimaryButton variant="outlined" @click="showModal"> Update Activity </PrimaryButton>
          </div>
          <div class="tw-mt-6">
            <dl class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2">
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Activity Type</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2 tw-capitalize">
                  {{ activity?.type }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Church</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2 tw-capitalize">
                  {{ `${activity?.church?.name} - ${activity?.church?.city}` }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Start Time</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ activity?.start_time }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">End Time</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ activity?.end_time }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Language</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ activity?.language }}
                </dd>
              </div>
              <div
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Occurrence</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ activity?.occurrence }}
                </dd>
              </div>
              <div
                v-if="activity?.occurrence === 'weekly'"
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Day of Week</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ activity?.day_of_week }}
                </dd>
              </div>
              <div
                v-if="activity?.occurrence === 'one-time'"
                class="tw-border-t tw-border-gray-100 tw-px-4 tw-py-6 sm:tw-col-span-1 sm:tw-px-0"
              >
                <dt class="text-sm/6 tw-font-medium tw-text-gray-900">Date</dt>
                <dd class="tw-mt-1 text-sm/6 tw-text-gray-700 sm:tw-mt-2">
                  {{ activity?.date }}
                </dd>
              </div>
            </dl>
          </div>
        </v-container>
      </v-sheet>
    </v-col>
  </v-row>
</template>
