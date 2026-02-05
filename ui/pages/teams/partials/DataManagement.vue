<script setup>
  import { ref, computed } from 'vue';
  import SectionTitle from '@/components/SectionTitle.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import FullExportModal from '@/components/FullExportModal.vue';
  import FullImportModal from '@/components/FullImportModal.vue';
  import useAuthStore from '@/stores/useAuthStore';
  import { mdiDatabaseExportOutline, mdiDatabaseImportOutline } from '@mdi/js';

  defineProps({
    team: { type: Object, required: true },
  });

  const authStore = useAuthStore();
  const isAdmin = computed(() => authStore.user?.is_admin);

  const showExportModal = ref(false);
  const showImportModal = ref(false);
</script>

<template>
  <div class="tw:md:grid tw:md:grid-cols-3 tw:md:gap-6">
    <SectionTitle>
      <template #title> Data Management </template>
      <template #description>
        Export and import your team's data. Use this to backup your data or migrate to a new
        installation.
      </template>
    </SectionTitle>

    <div class="tw:mt-5 tw:md:mt-0 tw:md:col-span-2">
      <div class="tw:px-4 tw:py-5 tw:bg-white tw:shadow tw:sm:p-6 tw:sm:rounded-md">
        <div class="tw:space-y-6">
          <!-- Export Section -->
          <div class="tw:flex tw:items-start tw:justify-between">
            <div>
              <h4 class="tw:font-medium tw:text-gray-900">Export Data</h4>
              <p class="tw:text-sm tw:text-gray-500 tw:mt-1">
                Download all your team's data as a JSON file. This includes dioceses, parishes,
                churches, schedules, and more.
              </p>
            </div>
            <PrimaryButton :prepend-icon="mdiDatabaseExportOutline" @click="showExportModal = true">
              Export
            </PrimaryButton>
          </div>

          <v-divider />

          <!-- Import Section -->
          <div class="tw:flex tw:items-start tw:justify-between">
            <div>
              <h4 class="tw:font-medium tw:text-gray-900">Import Data</h4>
              <p class="tw:text-sm tw:text-gray-500 tw:mt-1">
                Import data from a JSON export file. This will replace all existing data for this
                team.
              </p>
              <p class="tw:text-xs tw:text-orange-600 tw:mt-1">
                Warning: This operation cannot be undone.
              </p>
            </div>
            <SecondaryButton
              color="warning"
              :prepend-icon="mdiDatabaseImportOutline"
              @click="showImportModal = true"
            >
              Import
            </SecondaryButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <FullExportModal v-model:show-modal="showExportModal" />
    <FullImportModal v-model:show-modal="showImportModal" />
  </div>
</template>
