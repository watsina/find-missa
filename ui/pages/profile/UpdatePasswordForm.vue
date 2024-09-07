<template>
  <FormSection @submitted="updatePassword">
    <template #title> Update Password </template>

    <template #description>
      Ensure your account is using a long, random password to stay secure.
    </template>

    <template #form>
      <div class="tw-col-span-6 sm:tw-col-span-4">
        <TextInput
          ref="currentPasswordInput"
          v-model="form.current_password"
          autocomplete="current-password"
          class="tw-block tw-w-full tw-mt-1"
          :error="form.errors.current_password"
          label="Current Password"
          type="password"
        />
      </div>

      <div class="tw-col-span-6 sm:tw-col-span-4">
        <InputLabel for="password" />
        <TextInput
          ref="passwordInput"
          v-model="form.password"
          autocomplete="new-password"
          class="tw-block tw-w-full tw-mt-1"
          :error="form.errors.password"
          label="New Password"
          type="password"
        />
      </div>

      <div class="tw-col-span-6 sm:tw-col-span-4">
        <TextInput
          v-model="form.password_confirmation"
          autocomplete="new-password"
          class="tw-block tw-w-full tw-mt-1"
          :error="form.errors.password_confirmation"
          label="Confirm Password"
          type="password"
        />
      </div>
    </template>

    <template #actions>
      <ActionMessage class="tw-mr-3" :on="form.recentlySuccessful"> Saved. </ActionMessage>

      <PrimaryButton
        :class="{ 'tw-opacity-25': form.processing }"
        :disabled="form.processing"
        type="submit"
      >
        Save
      </PrimaryButton>
    </template>
  </FormSection>
</template>

<script setup>
  import { ref } from 'vue';
  import { useForm } from '@inertiajs/vue3';
  import ActionMessage from '@/Components/ActionMessage.vue';
  import FormSection from '@/Components/FormSection.vue';
  import InputLabel from '@/Components/InputLabel.vue';
  import PrimaryButton from '@/Components/PrimaryButton.vue';
  import TextInput from '@/Components/TextInput.vue';

  const passwordInput = ref(null);
  const currentPasswordInput = ref(null);

  const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
  });

  const updatePassword = () => {
    form.put(route('user-password.update'), {
      errorBag: 'updatePassword',
      preserveScroll: true,
      onSuccess: () => form.reset(),
      onError: () => {
        if (form.errors.password) {
          form.reset('password', 'password_confirmation');
          passwordInput.value.focus();
        }

        if (form.errors.current_password) {
          form.reset('current_password');
          currentPasswordInput.value.focus();
        }
      },
    });
  };
</script>
