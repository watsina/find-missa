<script setup>
  import { watch } from 'vue';
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';
  import DialogModal from '@/components/DialogModal.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import TextInput from '@/components/TextInput.vue';

  const showModal = defineModel('showModal', { type: Boolean });
  const emit = defineEmits(['created']);

  const form = useForm({
    name: '',
    email: '',
  });

  watch(showModal.value, (newValue) => {
    if (!newValue) {
      form.reset();
      form.clearErrors();
    }
  });

  const submit = () => {
    return form.post('/api/v1/user-invitations', {
      onSuccess: ({ data }) => {
        const message = 'User invitation was sent successfully';
        notify.success(message);

        closeModal(data);
      },
      onError: () => notify.error('There was an error inviting the user.'),
    });
  };

  const closeModal = () => {
    form.reset();
    emit('created', data);

    showModal.value = false;
  };
</script>

<template>
  <div>
    <dialog-modal max-width="600" :show="showModal" @close="closeModal">
      <template #title> Invite user to collaborate</template>

      <template #content>
        <form>
          <div class="tw-mt-4">
            <TextInput
              v-model="form.name"
              autofocus
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.name"
              label="User Name*"
              placeholder="John Doe"
              required
              type="text"
            />
          </div>

          <div class="tw-mt-4">
            <TextInput
              v-model="form.email"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.email"
              label="User Email*"
              placeholder="john@doe.com"
              required
              type="email"
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
          Send Invite
        </PrimaryButton>
      </template>
    </dialog-modal>
  </div>
</template>

<style lang="scss" scoped></style>
