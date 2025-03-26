<script setup>
  import { nextTick, ref } from 'vue';
  import { useForm } from '@/plugins';
  import AuthenticationCard from '@/components/AuthenticationCard.vue';
  import AuthenticationCardLogo from '@/components/AuthenticationCardLogo.vue';
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
          ref="codeInput"
          v-model="form.code"
          autocomplete="one-time-code"
          autofocus
          class="mt-1 block w-full"
          :error="form.errors.code"
          inputmode="numeric"
          label="Code"
          type="text"
        />
      </div>

      <div v-else>
        <InputLabel for="recovery_code" />
        <TextInput
          ref="recoveryCodeInput"
          v-model="form.recovery_code"
          autocomplete="one-time-code"
          class="mt-1 block w-full"
          clearable
          :error="form.errors.recovery_code"
          label="Recovery Code"
          type="text"
        />
      </div>

      <div class="flex items-center justify-end mt-4">
        <button
          class="text-sm text-gray-600 hover:text-gray-900 underline cursor-pointer"
          type="button"
          @click.prevent="toggleRecovery"
        >
          <template v-if="!recovery"> Use a recovery code </template>

          <template v-else> Use an authentication code </template>
        </button>

        <PrimaryButton
          class="ms-4"
          :disabled="form.processing"
          :loading="form.processing"
          type="submit"
          @click="submit"
        >
          Log in
        </PrimaryButton>
      </div>
    </form>
  </AuthenticationCard>
</template>
