<template>
  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <form @submit.prevent="submit">
      <div>
        <InputLabel for="name" value="Name" />
        <TextInput
          id="name"
          v-model="form.name"
          autocomplete="name"
          autofocus
          class="tw-block tw-w-full tw-mt-1"
          required
          type="text"
        />
        <InputError class="tw-mt-2" :message="form.errors.name" />
      </div>

      <div class="tw-mt-4">
        <InputLabel for="email" value="Email" />
        <TextInput
          id="email"
          v-model="form.email"
          class="tw-block tw-w-full tw-mt-1"
          required
          type="email"
        />
        <InputError class="tw-mt-2" :message="form.errors.email" />
      </div>

      <div class="tw-mt-4">
        <InputLabel for="password" value="Password" />
        <TextInput
          id="password"
          v-model="form.password"
          autocomplete="new-password"
          class="tw-block tw-w-full tw-mt-1"
          required
          type="password"
        />
        <InputError class="tw-mt-2" :message="form.errors.password" />
      </div>

      <div class="tw-mt-4">
        <InputLabel for="password_confirmation" value="Confirm Password" />
        <TextInput
          id="password_confirmation"
          v-model="form.password_confirmation"
          autocomplete="new-password"
          class="tw-block tw-w-full tw-mt-1"
          required
          type="password"
        />
        <InputError class="tw-mt-2" :message="form.errors.password_confirmation" />
      </div>

      <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="tw-mt-4">
        <InputLabel for="terms">
          <div class="tw-flex tw-items-center">
            <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />

            <div class="tw-ml-2">
              I agree to the
              <a
                class="tw-text-sm tw-text-gray-600 tw-underline hover:tw-text-gray-900"
                :href="route('terms.show')"
                target="_blank"
                >Terms of Service</a
              >
              and
              <a
                class="tw-text-sm tw-text-gray-600 tw-underline hover:tw-text-gray-900"
                :href="route('policy.show')"
                target="_blank"
                >Privacy Policy</a
              >
            </div>
          </div>
          <InputError class="tw-mt-2" :message="form.errors.terms" />
        </InputLabel>
      </div>

      <div class="tw-flex tw-items-center tw-justify-end tw-mt-4">
        <a
          class="tw-text-sm tw-text-gray-600 tw-underline hover:tw-text-gray-900"
          :href="route('login')"
        >
          Already registered?
        </a>

        <PrimaryButton
          class="tw-ml-4"
          :class="{ 'tw-opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Register
        </PrimaryButton>
      </div>
    </form>
  </AuthenticationCard>
</template>

<script setup>
  import { useForm } from '@/plugins';
  import AuthenticationCard from '@/components/AuthenticationCard.vue';
  import AuthenticationCardLogo from '@/components/AuthenticationCardLogo.vue';
  import Checkbox from '@/components/Checkbox.vue';
  import InputError from '@/components/InputError.vue';
  import InputLabel from '@/components/InputLabel.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';

  const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
  });

  const submit = () => {
    form.post(route('register'), {
      onFinish: () => form.reset('password', 'password_confirmation'),
    });
  };
</script>
