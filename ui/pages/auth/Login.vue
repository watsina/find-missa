<template>
  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <!-- <div v-if="status" class="tw-mb-4 tw-text-sm tw-font-medium tw-text-green-600"> -->
    <!--   {{ status }} -->
    <!-- </div> -->

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
        <NavLink class="tw-underline" :href="{ name: 'auth.forgot-password' }">
          Forgot your password?
        </NavLink>

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
  import { useRouter } from 'vue-router';
  import { useForm, http } from '@/plugins';
  import { notify } from '@/utils';
  import NavLink from '@/components/NavLink';
  import AuthenticationCard from '@/components/AuthenticationCard.vue';
  import AuthenticationCardLogo from '@/components/AuthenticationCardLogo.vue';
  import Checkbox from '@/components/Checkbox.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';

  const router = useRouter();

  const form = useForm({
    email: '',
    password: '',
    remember: false,
  });

  const submit = async () => {
    await http.get('/sanctum/csrf-cookie');
    form
      .transform((data) => ({
        ...data,
        remember: form.remember ? 'on' : '',
      }))
      .post('/api/v1/login', {
        onSuccess: (data) => {
          if (data?.two_factor) {
            router.push({ name: 'auth.two-factor-challenge' });
          } else {
            window.location.reload();
          }
        },
        onError: () => {
          notify.error('Something went wrong');
        },
        onFinish: () => form.reset('password'),
      });
  };
</script>
