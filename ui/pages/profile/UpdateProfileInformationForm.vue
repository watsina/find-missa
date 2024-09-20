<template>
  <FormSection @submitted="updateProfileInformation">
    <template #title> Profile Information </template>

    <template #description> Update your account's profile information and email address. </template>

    <template #form>
      <v-container>
        <v-row>
          <!-- Profile Photo -->
          <v-col cols="12" md="12" sm="6">
            <!-- Profile Photo File Input -->
            <input ref="photoInput" class="tw-hidden" type="file" @change="updatePhotoPreview" />

            <InputLabel for="photo" value="Photo" />

            <!-- Current Profile Photo -->
            <div v-show="!photoPreview" class="tw-mt-2">
              <img
                :alt="user.name"
                class="tw-object-cover tw-w-20 tw-h-20 tw-rounded-full"
                :src="user.profile_photo_url"
              />
            </div>

            <!-- New Profile Photo Preview -->
            <div v-show="photoPreview" class="tw-mt-2">
              <span
                class="tw-block tw-w-20 tw-h-20 tw-bg-center tw-bg-no-repeat tw-bg-cover tw-rounded-full"
                :style="'background-image: url(\'' + photoPreview + '\');'"
              />
            </div>

            <SecondaryButton class="tw-mt-2 tw-mr-2" type="button" @click.prevent="selectNewPhoto">
              Select A New Photo
            </SecondaryButton>

            <SecondaryButton
              v-if="user.profile_photo_path"
              class="tw-mt-2"
              type="button"
              @click.prevent="deletePhoto"
            >
              Remove Photo
            </SecondaryButton>

            <InputError class="tw-mt-2" :message="form.errors.photo" />
          </v-col>
          <!--Name -->
          <v-col cols="12" md="12" sm="6">
            <TextInput
              v-model="form.name"
              autocomplete="name"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.name"
              label="Name"
              type="text"
            />
          </v-col>
          <!-- Email -->
          <v-col cols="12" md="12" sm="6">
            <TextInput
              v-model="form.email"
              class="tw-block tw-w-full tw-mt-1"
              :error="form.errors.email"
              label="Email"
              type="email"
            />

            <div v-if="user.email_verified_at === null">
              <p class="tw-mt-2 tw-text-sm">
                Your email address is unverified.

                <a
                  as="button"
                  class="tw-text-gray-600 tw-underline hover:tw-text-gray-900"
                  :href="route('verification.send')"
                  method="post"
                  @click.prevent="sendEmailVerification"
                >
                  Click here to re-send the verification email.
                </a>
              </p>

              <div
                v-show="verificationLinkSent"
                class="tw-mt-2 tw-text-sm tw-font-medium tw-text-green-600"
              >
                A new verification link has been sent to your email address.
              </div>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </template>

    <template #actions>
      <ActionMessage class="tw-mr-3" :on="form.recentlySuccessful"> Saved. </ActionMessage>

      <PrimaryButton
        :class="{ 'tw-opacity-25': form.processing }"
        :disabled="form.processing"
        type="submit"
      >
        Save
      </PrimaryButton>
    </template>
  </FormSection>
</template>

<script setup>
  import { ref } from 'vue';
  import { http, useForm } from '@/plugins';
  import ActionMessage from '@/Components/ActionMessage.vue';
  import FormSection from '@/Components/FormSection.vue';
  import InputError from '@/Components/InputError.vue';
  import InputLabel from '@/Components/InputLabel.vue';
  import PrimaryButton from '@/Components/PrimaryButton.vue';
  import SecondaryButton from '@/Components/SecondaryButton.vue';
  import TextInput from '@/Components/TextInput.vue';

  const props = defineProps({
    user: Object,
  });

  const form = useForm({
    name: props.user.name,
    email: props.user.email,
    photo: null,
  });

  const verificationLinkSent = ref(null);
  const photoPreview = ref(null);
  const photoInput = ref(null);

  const updateProfileInformation = () => {
    if (photoInput.value) {
      form.photo = photoInput.value.files[0];
    }

    form.put('/api/v1/profile', {
      onSuccess: () => clearPhotoFileInput(),
    });
  };

  const sendEmailVerification = () => {
    verificationLinkSent.value = true;
  };

  const selectNewPhoto = () => {
    photoInput.value.click();
  };

  const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (!photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
      photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
  };

  const deletePhoto = () => {
    http.delete(route('current-user-photo.destroy'), {
      preserveScroll: true,
      onSuccess: () => {
        photoPreview.value = null;
        clearPhotoFileInput();
      },
    });
  };

  const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
      photoInput.value.value = null;
    }
  };
</script>
