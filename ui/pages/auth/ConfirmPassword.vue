<template>
  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <div class="tw-mb-4 tw-text-sm tw-text-gray-600">
      This is a secure area of the application. Please confirm your password before continuing.
    </div>

    <form @submit.prevent="submit">
      <div>
        <InputLabel for="password" value="Password" />
        <TextInput
          id="password"
          ref="passwordInput"
          v-model="form.password"
          autocomplete="current-password"
          autofocus
          class="tw-block tw-w-full tw-mt-1"
          required
          type="password"
        />
        <InputError class="tw-mt-2" :message="form.errors.password" />
      </div>

      <div class="tw-flex tw-justify-end tw-mt-4">
        <PrimaryButton
          class="tw-ml-4"
          :class="{ 'tw-opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Confirm
        </PrimaryButton>
      </div>
    </form>
  </AuthenticationCard>
</template>

<script setup>
  import { ref } from 'vue';
  import AuthenticationCard from '@/components/AuthenticationCard.vue';
  import AuthenticationCardLogo from '@/components/AuthenticationCardLogo.vue';
  import InputError from '@/components/InputError.vue';
  import InputLabel from '@/components/InputLabel.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import { useForm } from '@/plugins';

  const form = useForm({
    password: '',
  });

  const passwordInput = ref(null);

  const submit = () => {
    form.post('/api/v1/user/confirm-password', {
      onFinish: () => {
        form.reset();

        passwordInput.value.focus();
      },
    });
  };
</script>
