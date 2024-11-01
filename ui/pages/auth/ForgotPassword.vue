<template>
  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <div class="tw-mb-4 tw-text-sm tw-text-gray-600">
      Forgot your password ? No problem. Just let us know your email address and we will email you a
      password reset link that will allow you to choose a new one.
    </div>

    <form @submit.prevent="submit">
      <div>
        <TextInput
          id="email"
          v-model="form.email"
          autofocus
          class="tw-block tw-w-full tw-mt-1"
          :error="form.errors.email"
          label="Email"
          required
          type="email"
        />
      </div>

      <div class="tw-flex tw-items-center tw-justify-end tw-mt-4">
        <PrimaryButton :disabled="form.processing" :loading="form.processing" type="submit">
          Email Password Reset Link
        </PrimaryButton>
      </div>
    </form>
  </AuthenticationCard>
</template>

<script setup>
  import { useForm, http } from '@/plugins';
  import { notify } from '@/utils';
  import AuthenticationCard from '@/components/AuthenticationCard.vue';
  import AuthenticationCardLogo from '@/components/AuthenticationCardLogo.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';

  const urlParams = new URLSearchParams(window.location.search);
  const email = urlParams.get('email');

  const form = useForm({
    email: email || '',
  });

  const submit = async () => {
    await http.get('/sanctum/csrf-cookie');
    form.post('/api/v1/forgot-password', {
      onSuccess: () => {
        notify.success('Password reset link has been successfully sent to your email.');
      },
      onFailure: () => {
        notify.error('There was an error requesting password reset.');
      },
    });
  };
</script>
