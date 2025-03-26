<script setup>
  import { ref } from 'vue';
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';
  import ActionMessage from '@/components/ActionMessage.vue';
  import FormSection from '@/components/FormSection.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';

  const passwordInput = ref(null);
  const currentPasswordInput = ref(null);

  const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
  });

  const updatePassword = () => {
    form.put('/api/v1/user/password', {
      errorBag: 'updatePassword',
      onSuccess: () => {
        form.reset();
        notify.success('Password saved successfully.');
      },
      onError: () => {
        if (form.errors.password) {
          form.reset('password', 'password_confirmation');
          passwordInput.value.focus();
        }

        if (form.errors.current_password) {
          form.reset('current_password');
          currentPasswordInput.value.focus();
        }

        notify.error('Something went wrong updating your password');
      },
    });
  };
</script>

<template>
  <FormSection @submitted="updatePassword">
    <template #title> Update Password </template>

    <template #description>
      Ensure your account is using a long, random password to stay secure.
    </template>

    <template #form>
      <v-container>
        <v-row>
          <v-col cols="12" md="12" sm="6">
            <TextInput
              ref="currentPasswordInput"
              v-model="form.current_password"
              autocomplete="current-password"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.current_password"
              label="Current Password"
              type="password"
            />
          </v-col>
          <v-col cols="12" md="12" sm="6">
            <TextInput
              ref="passwordInput"
              v-model="form.password"
              autocomplete="new-password"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.password"
              label="New Password"
              type="password"
            />
          </v-col>
          <v-col cols="12" md="12" sm="6">
            <TextInput
              v-model="form.password_confirmation"
              autocomplete="new-password"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.password_confirmation"
              label="Confirm Password"
              type="password"
            />
          </v-col>
        </v-row>
      </v-container>
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
