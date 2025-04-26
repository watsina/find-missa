<script setup>
  import ActionMessage from '@/components/ActionMessage.vue';
  import FormSection from '@/components/FormSection.vue';
  import PrimaryButton from '@/components/PrimaryButton.vue';
  import TextInput from '@/components/TextInput.vue';
  import { useForm } from '@/plugins';
  import { notify } from '@/utils';

  const props = defineProps({
    team: { type: Object, required: true },
    canUpdateTeam: { type: Boolean, required: true },
  });

  const form = useForm({
    name: props.team.name,
  });

  const updateTeamName = () => {
    form.put(`/api/v1/teams/${props.team?.id}`, {
      onSuccess: () => {
        notify.success('Team updated successfully.');
        window.location.reload();
      },
      onError: () => notify.error('Something went wrong'),
    });
  };
</script>

<template>
  <FormSection @submitted="updateTeamName">
    <template #title> Team Name </template>

    <template #description> The team's name. </template>

    <template #form>
      <v-row>
        <v-col cols="12">
          <TextInput
            v-model="form.name"
            class="tw:mt-1 tw:block tw:w-full"
            :disabled="!canUpdateTeam"
            :error="form.errors.name"
            label="Team Name"
            type="text"
            variant="outlined"
          />
        </v-col>
      </v-row>
    </template>

    <template v-if="canUpdateTeam" #actions>
      <ActionMessage class="tw:me-3" :on="form.recentlySuccessful"> Saved. </ActionMessage>

      <PrimaryButton :disabled="form.processing" :loading="form.processing" type="submit">
        Save
      </PrimaryButton>
    </template>
  </FormSection>
</template>
