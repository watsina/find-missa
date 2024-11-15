<script setup>
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import DialogModal from '@/components/DialogModal.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import FileInput from '@/components/FileInput.vue';

  const showModal = defineModel('showModal', { type: Boolean });

  const form = useForm({
    file: null,
  });

  const submit = () => {
    return form.post('/api/v1/import-data', {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      onSuccess: () => {
        closeModal();
        notify.success('Data imported successfully.');

        window.location.reload();
      },
      onError: (error) => {
        notify.error('Something went wrong importing the data');
        console.info({ error });
        // form.errors.file = error;
      },
    });
  };

  const closeModal = () => {
    form.reset();
    showModal.value = false;
  };
</script>
<template>
  <div>
    <dialog-modal :show="showModal" @close="closeModal">
      <template #title> Upload Data </template>

      <template #content>
        <form>
          <div class="tw-mt-4">
            <FileInput
              id="file_upload"
              v-model="form.file"
              accept=".csv,.xls,.xlsx"
              autofocus
              class="tw-block tw-w-full tw-mt-1 tw-border-0 tw-outline-none focus:tw-outline-none file:tw-border-0 file:tw-mr-4 file:tw-py-2 file:tw-px-4 file:tw-rounded-full file:tw-text-sm file:tw-font-semibold file:tw-bg-indigo-50 file:tw-text-indigo-700 hover:file:tw-bg-indigo-100"
              :error="form.errors.file"
              label="Upload data file (.csv,.xls,.xlsx)"
              :loading="form.processing"
              placeholder="Choose a file..."
              required
            />
            <progress v-if="form.progress" max="100" :value="form.progress.percentage">
              {{ form.progress.percentage }}%
            </progress>
          </div>
        </form>
      </template>

      <template #footer>
        <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>

        <PrimaryButton
          class="tw-ml-3"
          :class="{ 'tw-opacity-25 tw-cursor-progress': form.processing }"
          :disabled="form.processing"
          :loading="form.processing"
          @click="submit"
        >
          Upload Data
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>

<style lang="scss" scoped></style>
<style lang="scss" scoped></style>
