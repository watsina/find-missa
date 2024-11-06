<script setup>
  import { useForm } from '@inertiajs/vue3';
  import ActionMessage from '@/components/ActionMessage.vue';
  import FormSection from '@/components/FormSection.vue';
  import InputError from '@/components/InputError.vue';
  import InputLabel from '@/components/InputLabel.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import { notify } from '@/utils';

  const props = defineProps({
    team: { type: Object, required: true },
    permissions: { type: Object, required: true },
  });

  const form = useForm({
    name: props.team.name,
  });

  const updateTeamName = () => {
    form.put(`/api/v1/teams/${props.team?.id}`, {
      onSuccess: () => notify.success('Team updated successfully.'),
      onError: () => notify.success('Something went wrong'),
    });
  };
</script>

<template>
  <FormSection @submitted="updateTeamName">
    <template #title> Team Name </template>

    <template #description> The team's name and owner information. </template>

    <template #form>
      <!-- Team Owner Information -->
      <div class="col-span-6">
        <InputLabel value="Team Owner" />

        <div class="flex items-center mt-2">
          <img
            :alt="team.owner.name"
            class="w-12 h-12 rounded-full object-cover"
            :src="team.owner.profile_photo_url"
          />

          <div class="ms-4 leading-tight">
            <div class="text-gray-900">{{ team.owner.name }}</div>
            <div class="text-gray-700 text-sm">
              {{ team.owner.email }}
            </div>
          </div>
        </div>
      </div>

      <!-- Team Name -->
      <div class="col-span-6 sm:col-span-4">
        <InputLabel for="name" value="Team Name" />

        <TextInput
          id="name"
          v-model="form.name"
          class="mt-1 block w-full"
          :disabled="!permissions.canUpdateTeam"
          type="text"
        />

        <InputError class="mt-2" :message="form.errors.name" />
      </div>
    </template>

    <template v-if="permissions.canUpdateTeam" #actions>
      <ActionMessage class="me-3" :on="form.recentlySuccessful"> Saved. </ActionMessage>

      <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
        Save
      </PrimaryButton>
    </template>
  </FormSection>
</template>
