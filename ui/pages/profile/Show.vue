<template>
  <AppLayout title="Profile">
    <template #header>
      <h2 class="tw-text-xl tw-font-semibold tw-leading-tight tw-text-gray-800">Profile</h2>
    </template>
    <div class="tw-py-12">
      <div class="tw-mx-auto tw-max-w-7xl sm:tw-px-6 lg:tw-px-8">
        <div class="sm:tw-rounded-lg">
          <!-- start -->
          <div class="tw-pb-10 tw-max-w-7xl sm:tw-px-6 lg:tw-px-8">
            <div v-if="$page.props.jetstream.canUpdateProfileInformation">
              <UpdateProfileInformationForm :user="$page.props.auth.user" />

              <SectionBorder />
            </div>

            <div v-if="$page.props.jetstream.canUpdatePassword">
              <UpdatePasswordForm class="tw-mt-10 sm:tw-mt-0" />

              <SectionBorder />
            </div>

            <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication">
              <TwoFactorAuthenticationForm
                :requires-confirmation="confirmsTwoFactorAuthentication"
                class="tw-mt-10 sm:tw-mt-0"
              />

              <SectionBorder />
            </div>

            <LogoutOtherBrowserSessionsForm :sessions="sessions" class="tw-mt-10 sm:tw-mt-0" />

            <template v-if="$page.props.jetstream.hasAccountDeletionFeatures">
              <SectionBorder />

              <DeleteUserForm class="tw-mt-10 sm:tw-mt-0" />
            </template>
          </div>
          <!-- end -->
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
  import AppLayout from '@/Layouts/AppLayout.vue';
  import DeleteUserForm from './DeleteUserForm.vue';
  import LogoutOtherBrowserSessionsForm from './LogoutOtherBrowserSessionsForm.vue';
  import SectionBorder from '@/Components/SectionBorder.vue';
  import TwoFactorAuthenticationForm from './TwoFactorAuthenticationForm.vue';
  import UpdatePasswordForm from './UpdatePasswordForm.vue';
  import UpdateProfileInformationForm from './UpdateProfileInformationForm.vue';

  defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
  });
</script>
