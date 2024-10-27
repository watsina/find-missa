<template>
  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <form @submit.prevent="submit">
      <div>
        <TextInput
          v-model="form.email"
          autofocus
          class="tw-block tw-w-full tw-mt-1"
          :error="form.errors.email"
          label="Email"
          required
          type="email"
        />
      </div>

      <div class="tw-mt-4">
        <TextInput
          v-model="form.password"
          autocomplete="new-password"
          class="tw-block tw-w-full tw-mt-1"
          :error="form.errors.password"
          label="Password"
          required
          type="password"
        />
      </div>

      <div class="tw-mt-4">
        <TextInput
          v-model="form.password_confirmation"
          autocomplete="new-password"
          class="tw-block tw-w-full tw-mt-1"
          :error="form.errors.password_confirmation"
          label="Confirm Password"
          required
          type="password"
        />
      </div>

      <div class="tw-flex tw-items-center tw-justify-end tw-mt-4">
        <NavLink class="tw-underline tw-mr-2" :href="{ name: 'auth.login' }">
          Back to Login
        </NavLink>
        <PrimaryButton :disabled="form.processing" :loading="form.processing" type="submit">
          Reset Password
        </PrimaryButton>
      </div>
    </form>
  </AuthenticationCard>
</template>

<script setup>
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';
  import { useRouter } from 'vue-router';
  import AuthenticationCard from '@/components/AuthenticationCard.vue';
  import AuthenticationCardLogo from '@/components/AuthenticationCardLogo.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import NavLink from '@/components/NavLink';

  const router = useRouter();
  const urlParams = new URLSearchParams(window.location.search);
  const email = urlParams.get('email');
  const token = window.location.pathname.split('/')[3];

  const form = useForm({
    token: token,
    email: email,
    password: '',
    password_confirmation: '',
  });

  const submit = () => {
    form.post('/api/v1/reset-password', {
      onSuccess: () => {
        notify.success('Your password has been reset.');
        router.push({ name: 'auth.login' });
      },
      onError: () => notify.error('Something went wrong'),
      onFinish: () => form.reset('password', 'password_confirmation'),
    });
  };
</script>
