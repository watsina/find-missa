<template>
  <ActionSection>
    <template #title> Browser Sessions </template>

    <template #description>
      Manage and log out your active sessions on other browsers and devices.
    </template>

    <template #content>
      <div class="tw:max-w-xl tw:text-sm tw:text-gray-600">
        If necessary, you may log out of all of your other browser sessions across all of your
        devices. Some of your recent sessions are listed below; however, this list may not be
        exhaustive. If you feel your account has been compromised, you should also update your
        password.
      </div>

      <!-- Other Browser Sessions -->
      <div v-if="sessions.length > 0" class="tw:mt-5 tw:space-y-6">
        <div v-for="(session, i) in sessions" :key="i" class="tw:flex tw:items-center">
          <div>
            <svg
              v-if="session.agent.is_desktop"
              class="tw:w-8 tw:h-8 tw:text-gray-500"
              fill="none"
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path
                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
              />
            </svg>

            <svg
              v-else
              class="tw:w-8 tw:h-8 tw:text-gray-500"
              fill="none"
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path d="M0 0h24v24H0z" stroke="none" />
              <rect height="16" rx="1" width="10" x="7" y="4" />
              <path d="M11 5h2M12 17v.01" />
            </svg>
          </div>

          <div class="tw:ml-3">
            <div class="tw:text-sm tw:text-gray-600">
              {{ session.agent.platform ? session.agent.platform : 'Unknown' }} -
              {{ session.agent.browser ? session.agent.browser : 'Unknown' }}
            </div>

            <div>
              <div class="tw:text-xs tw:text-gray-500">
                {{ session.ip_address }},

                <span v-if="session.is_current_device" class="tw:font-semibold tw:text-green-500"
                  >This device</span
                >
                <span v-else>Last active {{ session.last_active }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="tw:flex tw:items-center tw:mt-5">
        <PrimaryButton @click="confirmLogout"> Log Out Other Browser Sessions </PrimaryButton>

        <ActionMessage class="tw:ml-3" :on="form.recentlySuccessful"> Done. </ActionMessage>
      </div>

      <!-- Log Out Other Devices Confirmation Modal -->
      <DialogModal :show="confirmingLogout" @close="closeModal">
        <template #title> Log Out Other Browser Sessions </template>

        <template #content>
          Please enter your password to confirm you would like to log out of your other browser
          sessions across all of your devices.

          <div class="tw:mt-4">
            <TextInput
              ref="passwordInput"
              v-model="form.password"
              class="tw:block tw:w-3/4 tw:mt-1"
              placeholder="Password"
              type="password"
              @keyup.enter="logoutOtherBrowserSessions"
            />

            <InputError class="tw:mt-2" :message="form.errors.password" />
          </div>
        </template>

        <template #footer>
          <SecondaryButton @click="closeModal"> Cancel </SecondaryButton>

          <PrimaryButton
            class="tw:ml-3"
            :class="{ 'tw:opacity-25': form.processing }"
            :disabled="form.processing"
            @click="logoutOtherBrowserSessions"
          >
            Log Out Other Browser Sessions
          </PrimaryButton>
        </template>
      </DialogModal>
    </template>
  </ActionSection>
</template>

<script setup>
  import { ref, onMounted } from 'vue';
  import { http, useForm } from '@/plugins';
  import ActionMessage from '@/components/ActionMessage.vue';
  import ActionSection from '@/components/ActionSection.vue';
  import DialogModal from '@/components/DialogModal.vue';
  import InputError from '@/components/InputError.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import TextInput from '@/components/TextInput.vue';

  const confirmingLogout = ref(false);
  const passwordInput = ref(null);
  const sessions = ref([]);

  const form = useForm({
    password: '',
  });

  onMounted(() => getSessions());

  const getSessions = () => {
    http.get('/api/v1/user/browser-sessions').then(({ data }) => {
      sessions.value = data || [];
    });
  };

  const confirmLogout = () => {
    confirmingLogout.value = true;

    setTimeout(() => passwordInput.value.focus(), 250);
  };

  const logoutOtherBrowserSessions = () => {
    form.delete('/api/v1/user/other-browser-sessions', {
      preserveScroll: true,
      onSuccess: () => closeModal(),
      onError: () => passwordInput.value.focus(),
      onFinish: () => form.reset(),
    });
  };

  const closeModal = () => {
    confirmingLogout.value = false;

    form.reset();
  };
</script>
