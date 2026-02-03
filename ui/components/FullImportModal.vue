<script setup>
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';
  import { ref } from 'vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import DialogModal from '@/components/DialogModal.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import FileInput from '@/components/FileInput.vue';
  import { mdiDatabaseImportOutline, mdiAlertOutline } from '@mdi/js';

  const showModal = defineModel('showModal', { type: Boolean });
  const emit = defineEmits(['imported']);

  const confirmationChecked = ref(false);

  const form = useForm({
    file: null,
  });

  const submit = async () => {
    if (!confirmationChecked.value) {
      notify.error('Please confirm that you understand this will replace all existing data.');
      return;
    }

    const confirmed = await notify.confirm(
      'This will permanently delete all existing data and replace it with the imported data. This action cannot be undone. Are you absolutely sure?',
      {
        title: 'Confirm Data Import',
        confirmButtonText: 'Yes, Import Data',
        cancelButtonText: 'Cancel',
      },
    );

    if (!confirmed) {
      return;
    }

    return form.post('/api/v1/full-import', {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      onSuccess: (response) => {
        const data = response?.data;
        const totalImported = data?.total_imported || 0;

        closeModal();
        notify.success(`Data imported successfully. ${totalImported} records imported.`);
        emit('imported');

        // Reload to reflect changes
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      },
      onError: (error) => {
        notify.error(error?.message || 'Something went wrong importing the data');
        console.error('Import error:', error);
      },
    });
  };

  const closeModal = () => {
    form.reset();
    confirmationChecked.value = false;
    showModal.value = false;
  };
</script>

<template>
  <div>
    <dialog-modal max-width="600" :show="showModal" @close="closeModal">
      <template #title>
        <div class="tw:flex tw:items-center tw:gap-2">
          <v-icon :icon="mdiDatabaseImportOutline" size="24" />
          Import Full Database
        </div>
      </template>

      <template #content>
        <div class="tw:space-y-4">
          <v-alert
            class="tw:mb-4"
            color="warning"
            density="compact"
            :icon="mdiAlertOutline"
            variant="tonal"
          >
            <div class="tw:font-semibold">Warning: Data Replacement</div>
            <span class="tw:text-sm">
              Importing data will permanently delete all existing domain data for this team and
              replace it with the imported data.
            </span>
          </v-alert>

          <p class="tw:text-gray-600">
            Select a JSON export file from another Find Missa installation. The following data will
            be imported:
          </p>

          <ul class="tw:list-disc tw:list-inside tw:text-gray-600 tw:space-y-1 tw:text-sm">
            <li>Dioceses, Parishes, and Sub-parishes</li>
            <li>Churches and Priests</li>
            <li>Activity Times and Schedules</li>
            <li>Holidays and Liturgical Schedules</li>
            <li>Schedule Rules and Overrides</li>
          </ul>

          <div class="tw:mt-6">
            <FileInput
              id="import_file"
              v-model="form.file"
              accept=".json"
              class="tw:block tw:w-full"
              :disabled="form.processing"
              :error="form.errors.file"
              hint="Only .json files exported from Find Missa are supported"
              label="Select export file"
              :loading="form.processing"
              persistent-hint
              placeholder="Choose a JSON file..."
              required
            />
            <progress
              v-if="form.progress"
              class="tw:w-full tw:mt-2"
              max="100"
              :value="form.progress.percentage"
            >
              {{ form.progress.percentage }}%
            </progress>
          </div>

          <v-checkbox
            v-model="confirmationChecked"
            class="tw:mt-4"
            color="warning"
            density="compact"
            :disabled="form.processing"
            hide-details
          >
            <template #label>
              <span class="tw:text-sm tw:text-gray-700">
                I understand that this will
                <strong>permanently delete all existing data</strong>
                and replace it with the imported data.
              </span>
            </template>
          </v-checkbox>
        </div>
      </template>

      <template #footer>
        <SecondaryButton :disabled="form.processing" @click="closeModal"> Cancel </SecondaryButton>

        <PrimaryButton
          class="tw:ml-3"
          color="warning"
          :disabled="form.processing || !form.file || !confirmationChecked"
          :loading="form.processing"
          @click="submit"
        >
          Import Data
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>
