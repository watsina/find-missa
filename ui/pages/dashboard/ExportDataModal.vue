<script setup>
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import DialogModal from '@/components/DialogModal.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import SelectInput from '@/components/SelectInput.vue';
  import { ref } from 'vue';

  const isFetching = ref(false);
  const showModal = defineModel('showModal', { type: Boolean });

  const form = useForm({
    type: 'dioceses',
  });

  const exportTypes = [
    { title: 'Dioceses', value: 'dioceses' },
    { title: 'Parishes', value: 'parishes' },
  ];

  const submit = () => {
    return form.post('/api/v1/export-data', {
      responseType: 'blob',
      onSuccess: (data, { headers }) => {
        //get the file details
        const fileName = headers['content-disposition']?.split('=')[1] || 'no-name.xlsx';
        const fileType = headers.getContentType();
        // create the object
        const blob = new Blob([data], { type: fileType });
        // download the file.
        const href = URL.createObjectURL(blob);
        const anchorElement = document.createElement('a');
        anchorElement.href = href;
        anchorElement.download = fileName;
        document.body.appendChild(anchorElement);
        anchorElement.click();
        // clean up
        document.body.removeChild(anchorElement);
        URL.revokeObjectURL(href);

        closeModal();
        notify.success('Data exported successfully.');
      },
      onError: (error) => {
        notify.error('Something went wrong exporting the data');
        console.info({ error });
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
      <template #title> Export Data </template>

      <template #content>
        <form>
          <div class="tw-mt-4">
            <SelectInput
              v-model="form.type"
              class="tw-block tw-w-full tw-mt-1 tw-capitalize"
              :error="form.errors.type"
              :items="exportTypes"
              label="Export Type*"
              required
            />
          </div>
        </form>
      </template>

      <template #footer>
        <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>

        <PrimaryButton
          class="tw-ml-3"
          :disabled="form.processing"
          :loading="form.processing"
          @click="submit"
        >
          Export Data
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>

<style lang="scss" scoped></style>
<style lang="scss" scoped></style>
