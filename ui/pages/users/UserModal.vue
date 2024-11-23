<script setup>
  import { computed, watch } from 'vue';
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import TextInput from '@/components/TextInput.vue';

  const props = defineProps({
    user: { type: Object, required: true },
  });

  const showModal = defineModel('showModal', { type: Boolean });
  const emit = defineEmits(['updated']);

  const form = useForm({
    name: props.user?.name,
    email: props.user?.email,
  });

  watch(
    () => props.user,
    (newValue) => {
      if (newValue?.id) {
        form.name = props.user?.name;
        form.email = props.user?.email;
      }
    },
  );

  watch(showModal, (newValue) => {
    if (!newValue) {
      form.reset();
      form.clearErrors();
    }
  });

  const isEditing = computed(() => !!props.user?.id);

  const submit = () => {
    const url = isEditing.value ? `/api/v1/users/${props.user?.id}` : `/api/v1/users`;

    const method = isEditing.value ? 'put' : 'post';

    return form[method](url, {
      onSuccess: ({ data }) => {
        const message = isEditing.value
          ? 'User was updated successfully'
          : 'User was added successfully.';
        notify.success(message);
        closeModal(data);
      },
      onError: () => notify.error('There was an error saving the information.'),
    });
  };

  const closeModal = (data = null) => {
    form.reset();
    if (data?.id && isEditing.value) emit('updated', data);

    showModal.value = false;
  };
</script>

<template>
  <div>
    <dialog-modal max-width="600" :show="showModal" @close="closeModal">
      <template #title> Update User </template>

      <template #content>
        <form>
          <div class="tw-mt-4">
            <TextInput
              v-model="form.name"
              autofocus
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.name"
              label="User Name*"
              placeholder="Fr User"
              required
              type="text"
            />
          </div>

          <div class="tw-mt-4">
            <TextInput
              v-model="form.email"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.email"
              label="Email"
              placeholder="user@email.com"
              required
            />
          </div>
        </form>
      </template>

      <template #footer>
        <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>

        <PrimaryButton
          class="tw-ml-3 tw-capitalize"
          :disabled="form.processing"
          :loading="form.processing"
          @click="submit"
        >
          {{ isEditing ? 'Update User' : 'Add User' }}
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>

<style lang="scss" scoped></style>
