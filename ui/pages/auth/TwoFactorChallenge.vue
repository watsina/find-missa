<script setup>
  import { nextTick, ref } from 'vue';
  import { useForm } from '@/plugins';
  import AuthenticationCard from '@/components/AuthenticationCard.vue';
  import AuthenticationCardLogo from '@/components/AuthenticationCardLogo.vue';
  import InputError from '@/components/InputError.vue';
  import InputLabel from '@/components/InputLabel.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';

  const recovery = ref(false);

  const form = useForm({
    code: '',
    recovery_code: '',
  });

  const recoveryCodeInput = ref(null);
  const codeInput = ref(null);

  const toggleRecovery = async () => {
    recovery.value ^= true;

    await nextTick();

    if (recovery.value) {
      recoveryCodeInput.value.focus();
      form.code = '';
    } else {
      codeInput.value.focus();
      form.recovery_code = '';
    }
  };

  const submit = () => {
    form.post('/api/v1/two-factor-challenge', {
      onSuccess: () => window.location.reload(),
    });
  };
</script>

<template>
  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <div class="mb-4 text-sm text-gray-600">
      <template v-if="!recovery">
        Please confirm access to your account by entering the authentication code provided by your
        authenticator application.
      </template>

      <template v-else>
        Please confirm access to your account by entering one of your emergency recovery codes.
      </template>
    </div>

    <form @submit.prevent="submit">
      <div v-if="!recovery">
        <TextInput
          label="Code"
          ref="codeInput"
          v-model="form.code"
          type="text"
          inputmode="numeric"
          class="mt-1 block w-full"
          autofocus
          autocomplete="one-time-code"
          :error="form.errors.code"
        />
      </div>

      <div v-else>
        <InputLabel for="recovery_code" />
        <TextInput
          label="Recovery Code"
          ref="recoveryCodeInput"
          v-model="form.recovery_code"
          type="text"
          class="mt-1 block w-full"
          clearable
          autocomplete="one-time-code"
          :error="form.errors.recovery_code"
        />
      </div>

      <div class="flex items-center justify-end mt-4">
        <button
          type="button"
          class="text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer"
          @click.prevent="toggleRecovery"
        >
          <template v-if="!recovery"> Use a recovery code </template>

          <template v-else> Use an authentication code </template>
        </button>

        <PrimaryButton
          class="ms-4"
          type="submit"
          :loading="form.processing"
          :disabled="form.processing"
          @click="submit"
        >
          Log in
        </PrimaryButton>
      </div>
    </form>
  </AuthenticationCard>
</template>
