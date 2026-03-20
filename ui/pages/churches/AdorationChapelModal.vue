<script setup>
  import { computed, watch } from 'vue';
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import TextInput from '@/components/TextInput.vue';

  const props = defineProps({
    chapel: { type: Object, default: null },
    churchId: { type: [String, Number], required: true },
  });

  const showModal = defineModel('showModal', { type: Boolean });
  const emit = defineEmits(['created', 'updated']);

  const form = useForm({
    name: props.chapel?.name || '',
    notes: props.chapel?.notes || '',
  });

  watch(
    () => props.chapel,
    (newValue) => {
      if (newValue?.id) {
        form.name = newValue?.name || '';
        form.notes = newValue?.notes || '';
      }
    },
    { deep: true },
  );

  watch(showModal, (newValue) => {
    if (!newValue) {
      form.reset();
      form.clearErrors();
    }
  });

  const isEditing = computed(() => !!props.chapel?.id);

  const submit = () => {
    const url = `/api/v1/churches/${props.churchId}/adoration-chapel`;
    const method = isEditing.value ? 'put' : 'post';

    return form[method](url, {
      onSuccess: ({ data }) => {
        const message = isEditing.value
          ? 'Adoration Chapel was updated successfully.'
          : 'Adoration Chapel was created successfully.';
        notify.success(message);
        closeModal(data);
      },
      onError: () => notify.error('There was an error saving the information.'),
    });
  };

  const closeModal = (data = null) => {
    form.reset();
    if (data?.id && !isEditing.value) emit('created', data);
    if (data?.id && isEditing.value) emit('updated', data);

    showModal.value = false;
  };
</script>

<template>
  <div>
    <dialog-modal max-width="600" :show="showModal" @close="closeModal">
      <template #title>
        {{ isEditing ? 'Update Adoration Chapel' : 'Set Up Adoration Chapel' }}
      </template>

      <template #content>
        <form>
          <div class="tw:mt-4">
            <TextInput
              v-model="form.name"
              class="tw:block tw:w-full tw:mt-1"
              :error="form.errors.name"
              label="Name"
              placeholder="e.g. Our Lady of Perpetual Adoration Chapel"
            />
          </div>

          <div class="tw:mt-4">
            <v-textarea
              v-model="form.notes"
              class="tw:block tw:w-full tw:mt-1"
              :error-messages="form.errors.notes"
              label="Notes"
              placeholder="Any additional notes about this adoration chapel"
              rows="3"
              variant="filled"
            />
          </div>
        </form>
      </template>

      <template #footer>
        <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>

        <PrimaryButton
          class="tw:ml-3 tw:capitalize"
          :disabled="form.processing"
          :loading="form.processing"
          @click="submit"
        >
          {{ isEditing ? 'Update Chapel' : 'Create Chapel' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>
