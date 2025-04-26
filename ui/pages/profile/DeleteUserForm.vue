<template>
  <ActionSection>
    <template #title> Delete Account </template>

    <template #description> Permanently delete your account. </template>

    <template #content>
      <div class="tw:max-w-xl tw:text-sm tw:text-gray-600">
        Once your account is deleted, all of its resources and data will be permanently deleted.
        Before deleting your account, please download any data or information that you wish to
        retain.
      </div>

      <div class="tw:mt-5">
        <DangerButton @click="confirmUserDeletion"> Delete Account </DangerButton>
      </div>

      <!-- Delete Account Confirmation Modal -->
      <DialogModal :show="confirmingUserDeletion" @close="closeModal">
        <template #title> Delete Account </template>

        <template #content>
          Are you sure you want to delete your account? Once your account is deleted, all of its
          resources and data will be permanently deleted. Please enter your password to confirm you
          would like to permanently delete your account.

          <div class="tw:mt-4">
            <TextInput
              ref="passwordInput"
              v-model="form.password"
              class="tw:block tw:w-3/4 tw:mt-1"
              placeholder="Password"
              type="password"
              @keyup.enter="deleteUser"
            />

            <InputError class="tw:mt-2" :message="form.errors.password" />
          </div>
        </template>

        <template #footer>
          <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>

          <DangerButton
            class="tw:ml-3"
            :class="{ 'tw:opacity-25': form.processing }"
            :disabled="form.processing"
            @click="deleteUser"
          >
            Delete Account
          </DangerButton>
        </template>
      </DialogModal>
    </template>
  </ActionSection>
</template>

<script setup>
  import { ref } from 'vue';
  import { useForm } from '@/plugins';
  import ActionSection from '@/components/ActionSection.vue';
  import DangerButton from '@/components/DangerButton.vue';
  import DialogModal from '@/components/DialogModal.vue';
  import InputError from '@/components/InputError.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import TextInput from '@/components/TextInput.vue';

  const confirmingUserDeletion = ref(false);
  const passwordInput = ref(null);

  const form = useForm({
    password: '',
  });

  const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    setTimeout(() => passwordInput.value.focus(), 250);
  };

  const deleteUser = () => {
    form.delete('/api/v1/current-user', {
      preserveScroll: true,
      onSuccess: () => closeModal(),
      onError: () => passwordInput.value.focus(),
      onFinish: () => form.reset(),
    });
  };

  const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
  };
</script>
