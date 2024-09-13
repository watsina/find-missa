<template>
  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <div v-if="status" class="tw-mb-4 tw-text-sm tw-font-medium tw-text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="submit">
      <div class="tw-mt-4">
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
          autocomplete="current-password"
          class="tw-block tw-w-full tw-mt-1"
          :error="form.errors.password"
          label="Password"
          required
          type="password"
        />
      </div>

      <div class="tw-block tw-mt-4">
        <Checkbox v-model="form.remember" label="Remember me" />
      </div>

      <div class="tw-flex tw-items-center tw-justify-end tw-mt-4">
        <!-- <Link -->
        <!--   v-if="canResetPassword" -->
        <!--   class="tw-text-sm tw-text-gray-600 tw-underline hover:tw-text-gray-900" -->
        <!--   :href="route('password.request')" -->
        <!-- > -->
        <!--   Forgot your password? -->
        <!-- </Link> -->

        <PrimaryButton
          class="tw-ml-4"
          :class="{ 'tw-opacity-25': form.processing }"
          :disabled="form.processing"
          type="submit"
        >
          Log in
        </PrimaryButton>
      </div>
    </form>
  </AuthenticationCard>
</template>

<script setup>
  import { useForm } from '@/plugins';
  import AuthenticationCard from '@/Components/AuthenticationCard.vue';
  import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
  import Checkbox from '@/Components/Checkbox.vue';
  import PrimaryButton from '@/Components/PrimaryButton.vue';
  import TextInput from '@/Components/TextInput.vue';

  defineProps({
    canResetPassword: Boolean,
    status: String,
  });

  const form = useForm({
    email: '',
    password: '',
    remember: false,
  });

  const submit = () => {
    form
      .transform((data) => ({
        ...data,
        remember: form.remember ? 'on' : '',
      }))
      .post('/api/v1/login', {
        onFinish: () => form.reset('password'),
      });
  };
</script>
