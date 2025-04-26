<template>
  <ActionSection>
    <template #title> Two Factor Authentication </template>

    <template #description>
      Add additional security to your account using two factor authentication.
    </template>

    <template #content>
      <h3 v-if="twoFactorEnabled && !confirming" class="tw:text-lg tw:font-medium tw:text-gray-900">
        You have enabled two factor authentication.
      </h3>

      <h3
        v-else-if="twoFactorEnabled && confirming"
        class="tw:text-lg tw:font-medium tw:text-gray-900"
      >
        Finish enabling two factor authentication.
      </h3>

      <h3 v-else class="tw:text-lg tw:font-medium tw:text-gray-900">
        You have not enabled two factor authentication.
      </h3>

      <div class="tw:max-w-xl tw:mt-3 tw:text-sm tw:text-gray-600">
        <p>
          When two factor authentication is enabled, you will be prompted for a secure, random token
          during authentication. You may retrieve this token from your phone's Google Authenticator
          application.
        </p>
      </div>

      <div v-if="twoFactorEnabled">
        <div v-if="qrCode">
          <div class="tw:max-w-xl tw:mt-4 tw:text-sm tw:text-gray-600">
            <p v-if="confirming" class="tw:font-semibold">
              To finish enabling two factor authentication, scan the following QR code using your
              phone's authenticator application or enter the setup key and provide the generated OTP
              code.
            </p>

            <p v-else>
              Two factor authentication is now enabled. Scan the following QR code using your
              phone's authenticator application or enter the setup key.
            </p>
          </div>

          <div class="tw:mt-4" v-html="qrCode" />

          <div v-if="setupKey" class="tw:max-w-xl tw:mt-4 tw:text-sm tw:text-gray-600">
            <p class="tw:font-semibold">Setup Key: <span v-html="setupKey" /></p>
          </div>

          <div v-if="confirming" class="tw:mt-4">
            <InputLabel for="code" value="Code" />

            <TextInput
              id="code"
              v-model="confirmationForm.code"
              autocomplete="one-time-code"
              autofocus
              class="tw:block tw:w-1/2 tw:mt-1"
              inputmode="numeric"
              name="code"
              type="text"
              @keyup.enter="confirmTwoFactorAuthentication"
            />

            <InputError class="tw:mt-2" :message="confirmationForm.errors.code" />
          </div>
        </div>

        <div v-if="recoveryCodes.length > 0 && !confirming">
          <div class="tw:max-w-xl tw:mt-4 tw:text-sm tw:text-gray-600">
            <p class="tw:font-semibold">
              Store these recovery codes in a secure password manager. They can be used to recover
              access to your account if your two factor authentication device is lost.
            </p>
          </div>

          <div
            class="tw:grid tw:max-w-xl tw:gap-1 tw:px-4 tw:py-4 tw:mt-4 tw:font-mono tw:text-sm tw:bg-gray-100 tw:rounded-lg"
          >
            <div v-for="code in recoveryCodes" :key="code">
              {{ code }}
            </div>
          </div>
        </div>
      </div>

      <div class="tw:mt-5">
        <div v-if="!twoFactorEnabled">
          <ConfirmsPassword @confirmed="enableTwoFactorAuthentication">
            <PrimaryButton
              :class="{ 'tw:opacity-25': enabling }"
              :disabled="enabling"
              type="button"
            >
              Enable
            </PrimaryButton>
          </ConfirmsPassword>
        </div>

        <div v-else>
          <ConfirmsPassword @confirmed="confirmTwoFactorAuthentication">
            <PrimaryButton
              v-if="confirming"
              class="tw:mr-3"
              :class="{ 'tw:opacity-25': enabling }"
              :disabled="enabling"
              type="button"
            >
              Confirm
            </PrimaryButton>
          </ConfirmsPassword>

          <ConfirmsPassword @confirmed="regenerateRecoveryCodes">
            <SecondaryButton v-if="recoveryCodes.length > 0 && !confirming" class="tw:mr-3">
              Regenerate Recovery Codes
            </SecondaryButton>
          </ConfirmsPassword>

          <ConfirmsPassword @confirmed="showRecoveryCodes">
            <SecondaryButton v-if="recoveryCodes.length === 0 && !confirming" class="tw:mr-3">
              Show Recovery Codes
            </SecondaryButton>
          </ConfirmsPassword>

          <ConfirmsPassword @confirmed="disableTwoFactorAuthentication">
            <SecondaryButton
              v-if="confirming"
              :class="{ 'tw:opacity-25': disabling }"
              :disabled="disabling"
            >
              Cancel
            </SecondaryButton>
          </ConfirmsPassword>

          <ConfirmsPassword @confirmed="disableTwoFactorAuthentication">
            <DangerButton
              v-if="!confirming"
              :class="{ 'tw:opacity-25': disabling }"
              :disabled="disabling"
            >
              Disable
            </DangerButton>
          </ConfirmsPassword>
        </div>
      </div>
    </template>
  </ActionSection>
</template>

<script setup>
  import { ref, computed, watch } from 'vue';
  import { http, useForm } from '@/plugins';
  import ActionSection from '@/components/ActionSection.vue';
  import ConfirmsPassword from '@/components/ConfirmsPassword.vue';
  import DangerButton from '@/components/DangerButton.vue';
  import InputError from '@/components/InputError.vue';
  import InputLabel from '@/components/InputLabel.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import useAuthStore from '@/stores/useAuthStore';

  const store = useAuthStore();

  const requiresConfirmation = computed(
    () =>
      store.featureIsEnabled('fortify', 'two-factor-authentication') &&
      store.featureOptionIsEnabled('fortify', 'two-factor-authentication', 'confirm'),
  );

  const enabling = ref(false);
  const confirming = ref(false);
  const disabling = ref(false);
  const qrCode = ref(null);
  const setupKey = ref(null);
  const recoveryCodes = ref([]);

  const confirmationForm = useForm({
    code: '',
  });

  const twoFactorEnabled = computed(() => !enabling.value && store.user?.two_factor_enabled);

  watch(twoFactorEnabled, () => {
    if (!twoFactorEnabled.value) {
      confirmationForm.reset();
      confirmationForm.clearErrors();
    }
  });

  const enableTwoFactorAuthentication = () => {
    enabling.value = true;

    http
      .post('/api/v1/user/two-factor-authentication')
      .then(async () => {
        store.updateUser({ ...store.user, two_factor_enabled: true });
        Promise.all([showQrCode(), showSetupKey(), showRecoveryCodes()]);
      })
      .finally(() => {
        enabling.value = false;
        confirming.value = requiresConfirmation.value;
      });
  };

  const showQrCode = () => {
    return http.get('/api/v1/user/two-factor-qr-code').then((response) => {
      qrCode.value = response.data.svg;
    });
  };

  const showSetupKey = () => {
    return http.get('/api/v1/user/two-factor-secret-key').then((response) => {
      setupKey.value = response.data.secretKey;
    });
  };

  const showRecoveryCodes = () => {
    return http.get('/api/v1/user/two-factor-recovery-codes').then((response) => {
      recoveryCodes.value = response.data;
    });
  };

  const confirmTwoFactorAuthentication = () => {
    confirmationForm.post('/api/v1/user/confirmed-two-factor-authentication', {
      errorBag: 'confirmTwoFactorAuthentication',
      onSuccess: () => {
        confirming.value = false;
        qrCode.value = null;
        setupKey.value = null;
      },
    });
  };

  const regenerateRecoveryCodes = () => {
    http.post('/api/v1/user/two-factor-recovery-codes').then(() => showRecoveryCodes());
  };

  const disableTwoFactorAuthentication = () => {
    disabling.value = true;

    http
      .delete('/api/v1/user/two-factor-authentication')
      .then(() => window.location.reload())
      .finally(() => {
        disabling.value = false;
        confirming.value = false;
      });
  };
</script>
