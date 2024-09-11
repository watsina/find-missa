<template>
  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <div class="tw-mb-4 tw-text-sm tw-text-gray-600">
      {{
        newAccount && newEmail
          ? `Your account has been successfully created. Please request a password reset to create a
            password
            and start using the account`
          : `Forgot your password ? No problem. Just let us know your email address and we will
            email you a
            password reset link that will allow you to choose a new one.`
      }}
    </div>

    <div v-if="status" class="tw-mb-4 tw-text-sm tw-font-medium tw-text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="submit">
      <div>
        <TextInput
          label="Email"
          id="email"
          v-model="form.email"
          type="email"
          class="tw-block tw-w-full tw-mt-1"
          required
          autofocus
          :error="form.errors.email"
        />
      </div>

      <div class="tw-flex tw-items-center tw-justify-end tw-mt-4">
        <PrimaryButton type="submit" :loading="form.processing" :disabled="form.processing">
          Email Password Reset Link
        </PrimaryButton>
      </div>
    </form>
  </AuthenticationCard>
</template>

<script setup>
  import { useForm } from '@/plugins';
  import AuthenticationCard from '@/Components/AuthenticationCard.vue';
  import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
  import InputError from '@/Components/InputError.vue';
  import InputLabel from '@/Components/InputLabel.vue';
  import PrimaryButton from '@/Components/PrimaryButton.vue';
  import TextInput from '@/Components/TextInput.vue';

  const props = defineProps({
    status: String,
  });

  const urlParams = new URLSearchParams(window.location.search);
  const newAccount = Boolean(urlParams.get('newAccount'));
  const newEmail = urlParams.get('newEmail');

  const form = useForm({
    email: newEmail || '',
  });

  const submit = () => {
    form.post(route('password.email'));
  };
</script>
