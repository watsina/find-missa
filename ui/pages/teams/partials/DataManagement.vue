<script setup>
  import { ref, computed, onMounted, onUnmounted } from 'vue';
  import SectionTitle from '@/components/SectionTitle.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import FullExportModal from '@/components/FullExportModal.vue';
  import FullImportModal from '@/components/FullImportModal.vue';
  import useAuthStore from '@/stores/useAuthStore';
  import { http } from '@/plugins';
  import {
    mdiDatabaseExportOutline,
    mdiDatabaseImportOutline,
    mdiRefresh,
    mdiClose,
    mdiCheckCircle,
    mdiAlertCircle,
    mdiLoading,
  } from '@mdi/js';

  const STORAGE_KEY = 'find-mass-import-status';
  const POLL_INTERVAL_MS = 1000;

  const props = defineProps({
    team: { type: Object, required: true },
  });

  const authStore = useAuthStore();
  const canAccessDataManagement = computed(() =>
    authStore.pennantFeatureIsActive('data_import_export'),
  );

  const showExportModal = ref(false);
  const showImportModal = ref(false);

  // Import status state
  const importId = ref(null);
  const importStatus = ref(null);
  const pollingInterval = ref(null);
  const isDismissed = ref(false);

  // Computed properties
  const isImportInProgress = computed(() => {
    if (!importStatus.value) return false;
    return ['pending', 'processing'].includes(importStatus.value.status);
  });

  const showImportStatus = computed(() => {
    if (isDismissed.value) return false;
    return importId.value && importStatus.value;
  });

  const progressPercentage = computed(() => {
    if (!importStatus.value) return 0;
    const { tables_processed, tables_total } = importStatus.value;
    if (!tables_total || tables_total === 0) return 0;
    return Math.round((tables_processed / tables_total) * 100);
  });

  const progressColor = computed(() => {
    if (!importStatus.value) return 'primary';
    switch (importStatus.value.status) {
      case 'completed':
        return 'success';
      case 'failed':
        return 'error';
      default:
        return 'primary';
    }
  });

  const statusText = computed(() => {
    if (!importStatus.value) return '';
    const { status, stage, current_table } = importStatus.value;

    if (status === 'completed') {
      return 'Import completed successfully';
    }
    if (status === 'failed') {
      return importStatus.value.error || 'Import failed';
    }
    if (status === 'pending' || stage === 'queued') {
      return 'Waiting in queue...';
    }
    if (stage === 'deleting') {
      return `Deleting existing data${current_table ? `: ${current_table}` : ''}...`;
    }
    if (stage === 'importing') {
      return `Importing data${current_table ? `: ${current_table}` : ''}...`;
    }
    return 'Processing...';
  });

  const summaryText = computed(() => {
    if (!importStatus.value) return '';
    const { records_imported, records_deleted, tables_processed } = importStatus.value;
    const parts = [];
    if (tables_processed > 0) parts.push(`${tables_processed} tables`);
    if (records_imported > 0) parts.push(`${records_imported} records imported`);
    if (records_deleted > 0) parts.push(`${records_deleted} records deleted`);
    return parts.join(', ');
  });

  // localStorage functions
  function saveImportState() {
    if (importId.value) {
      localStorage.setItem(
        STORAGE_KEY,
        JSON.stringify({
          importId: importId.value,
          teamId: props.team.id,
        }),
      );
    }
  }

  function loadImportState() {
    try {
      const stored = localStorage.getItem(STORAGE_KEY);
      if (!stored) return null;
      const data = JSON.parse(stored);
      // Only restore if same team
      if (data.teamId === props.team.id) {
        return data;
      }
      // Different team, clear storage
      clearImportState();
      return null;
    } catch {
      clearImportState();
      return null;
    }
  }

  function clearImportState() {
    localStorage.removeItem(STORAGE_KEY);
  }

  // Polling functions
  async function pollStatus() {
    if (!importId.value) return;

    try {
      const response = await http.get(`/api/v1/import-status/${importId.value}`);
      importStatus.value = response.data;

      // Stop polling if import is complete or failed
      if (['completed', 'failed'].includes(response.data.status)) {
        stopPolling();
        clearImportState();
      }
    } catch (error) {
      // 404 means import not found or expired
      if (error.response?.status === 404) {
        stopPolling();
        clearImportState();
        importId.value = null;
        importStatus.value = null;
      }
    }
  }

  function startPolling() {
    if (pollingInterval.value) return;
    // Poll immediately, then set interval
    pollStatus();
    pollingInterval.value = setInterval(pollStatus, POLL_INTERVAL_MS);
  }

  function stopPolling() {
    if (pollingInterval.value) {
      clearInterval(pollingInterval.value);
      pollingInterval.value = null;
    }
  }

  // Event handlers
  function onImportStarted(id) {
    importId.value = id;
    importStatus.value = { status: 'pending', stage: 'queued' };
    isDismissed.value = false;
    saveImportState();
    startPolling();
  }

  function dismissStatus() {
    isDismissed.value = true;
    stopPolling();
    clearImportState();
    importId.value = null;
    importStatus.value = null;
  }

  function refreshPage() {
    window.location.reload();
  }

  // Lifecycle
  onMounted(() => {
    const savedState = loadImportState();
    if (savedState?.importId) {
      importId.value = savedState.importId;
      startPolling();
    }
  });

  onUnmounted(() => {
    stopPolling();
  });
</script>

<template>
  <div v-if="canAccessDataManagement" class="tw:md:grid tw:md:grid-cols-3 tw:md:gap-6">
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
          <!-- Import Status Display -->
          <div v-if="showImportStatus">
            <!-- Processing Status -->
            <v-alert v-if="isImportInProgress" class="tw:mb-0" color="info" variant="tonal">
              <template #prepend>
                <v-icon class="tw:animate-spin" :icon="mdiLoading" />
              </template>
              <div class="tw:font-medium">Import in Progress</div>
              <div class="tw:text-sm tw:mt-1">{{ statusText }}</div>
              <v-progress-linear
                class="tw:mt-3"
                :color="progressColor"
                height="8"
                :model-value="progressPercentage"
                rounded
              />
              <div v-if="summaryText" class="tw:text-xs tw:mt-2 tw:text-gray-600">
                {{ summaryText }}
              </div>
            </v-alert>

            <!-- Completed Status -->
            <v-alert
              v-else-if="importStatus?.status === 'completed'"
              class="tw:mb-0"
              color="success"
              variant="tonal"
            >
              <template #prepend>
                <v-icon :icon="mdiCheckCircle" />
              </template>
              <div class="tw:font-medium">{{ statusText }}</div>
              <div v-if="summaryText" class="tw:text-sm tw:mt-1">{{ summaryText }}</div>
              <div class="tw:flex tw:gap-2 tw:mt-3">
                <v-btn
                  color="success"
                  :prepend-icon="mdiRefresh"
                  size="small"
                  variant="flat"
                  @click="refreshPage"
                >
                  Refresh Page
                </v-btn>
                <v-btn :prepend-icon="mdiClose" size="small" variant="text" @click="dismissStatus">
                  Dismiss
                </v-btn>
              </div>
            </v-alert>

            <!-- Failed Status -->
            <v-alert
              v-else-if="importStatus?.status === 'failed'"
              class="tw:mb-0"
              color="error"
              variant="tonal"
            >
              <template #prepend>
                <v-icon :icon="mdiAlertCircle" />
              </template>
              <div class="tw:font-medium">Import Failed</div>
              <div class="tw:text-sm tw:mt-1">{{ statusText }}</div>
              <div class="tw:flex tw:gap-2 tw:mt-3">
                <v-btn :prepend-icon="mdiClose" size="small" variant="text" @click="dismissStatus">
                  Dismiss
                </v-btn>
              </div>
            </v-alert>

            <v-divider class="tw:my-6" />
          </div>

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
              :disabled="isImportInProgress"
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
    <FullImportModal v-model:show-modal="showImportModal" @started="onImportStarted" />
  </div>
</template>
