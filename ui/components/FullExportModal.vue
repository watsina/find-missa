<script setup>
  import { http } from '@/plugins';
  import { notify } from '@/utils';
  import { ref } from 'vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import DialogModal from '@/components/DialogModal.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import { mdiDatabaseExportOutline, mdiAlertCircleOutline } from '@mdi/js';

  const showModal = defineModel('showModal', { type: Boolean });

  const processing = ref(false);

  const submit = async () => {
    processing.value = true;

    try {
      const response = await http.get('/api/v1/full-export', {
        responseType: 'blob',
      });

      // Extract filename from content-disposition header
      const contentDisposition = response.headers['content-disposition'];
      const fileName = contentDisposition?.split('=')[1]?.replace(/"/g, '') || 'full-export.json';

      // Create blob and trigger download
      const blob = new Blob([response.data], { type: 'application/json' });
      const href = URL.createObjectURL(blob);
      const anchorElement = document.createElement('a');
      anchorElement.href = href;
      anchorElement.download = fileName;
      document.body.appendChild(anchorElement);
      anchorElement.click();

      // Clean up
      document.body.removeChild(anchorElement);
      URL.revokeObjectURL(href);

      closeModal();
      notify.success('Data exported successfully.');
    } catch (error) {
      notify.error('Something went wrong exporting the data');
      console.error('Export error:', error);
    } finally {
      processing.value = false;
    }
  };

  const closeModal = () => {
    showModal.value = false;
  };
</script>

<template>
  <div>
    <dialog-modal max-width="550" :show="showModal" @close="closeModal">
      <template #title>
        <div class="tw:flex tw:items-center tw:gap-2">
          <v-icon :icon="mdiDatabaseExportOutline" size="24" />
          Export Full Database
        </div>
      </template>

      <template #content>
        <div class="tw:space-y-4">
          <p class="tw:text-gray-600">
            This will export all domain data for your current team as a JSON file. The export
            includes:
          </p>

          <ul class="tw:list-disc tw:list-inside tw:text-gray-600 tw:space-y-1">
            <li>Dioceses, Parishes, and Sub-parishes</li>
            <li>Churches and Priests</li>
            <li>Activity Times and Schedules</li>
            <li>Holidays and Liturgical Schedules</li>
            <li>Schedule Rules and Overrides</li>
          </ul>

          <v-alert
            class="tw:mt-4"
            color="info"
            density="compact"
            :icon="mdiAlertCircleOutline"
            variant="tonal"
          >
            <span class="tw:text-sm">
              User accounts and permissions are not included in this export.
            </span>
          </v-alert>
        </div>
      </template>

      <template #footer>
        <SecondaryButton :disabled="processing" @click="closeModal"> Cancel </SecondaryButton>

        <PrimaryButton class="tw:ml-3" :disabled="processing" :loading="processing" @click="submit">
          Download Export
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>
