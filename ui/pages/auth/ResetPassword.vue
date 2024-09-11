<template>
  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <form @submit.prevent="submit">
      <div>
        <TextInput
          label="Email"
          v-model="form.email"
          :error="form.errors.email"
          type="email"
          class="tw-block tw-w-full tw-mt-1"
          required
          autofocus
        />
      </div>

      <div class="tw-mt-4">
        <TextInput
          label="Password"
          v-model="form.password"
          type="password"
          class="tw-block tw-w-full tw-mt-1"
          required
          autocomplete="new-password"
          :error="form.errors.password"
        />
      </div>

      <div class="tw-mt-4">
        <InputLabel for="password_confirmation" />
        <TextInput
          label="Confirm Password"
          id="password_confirmation"
          v-model="form.password_confirmation"
          type="password"
          class="tw-block tw-w-full tw-mt-1"
          required
          autocomplete="new-password"
          :error="form.errors.password_confirmation"
        />
      </div>

      <div class="tw-flex tw-items-center tw-justify-end tw-mt-4">
        <PrimaryButton type="submit" :loading="form.processing" :disabled="form.processing">
          Reset Password
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
    email: String,
    token: String,
  });

  const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
  });

  const submit = () => {
    form.post(route('password.update'), {
      onFinish: () => form.reset('password', 'password_confirmation'),
    });
  };
</script>
