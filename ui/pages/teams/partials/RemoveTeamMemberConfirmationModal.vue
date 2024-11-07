<script setup>
  import { ref } from 'vue';
  import ConfirmationModal from '@/components/ConfirmationModal.vue';
  import DangerButton from '@/components/DangerButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import { useForm } from '@/plugins';

  defineProps({
    availableRoles: { type: Array, required: true },
  });

  const removeTeamMemberForm = useForm({});

  const teamMemberBeingRemoved = ref(null);

  const removeTeamMember = () => {
    // removeTeamMemberForm.delete(
    //   route('team-members.destroy', [props.team, teamMemberBeingRemoved.value]),
    //   {
    //     errorBag: 'removeTeamMember',
    //     preserveScroll: true,
    //     preserveState: true,
    //     onSuccess: () => (teamMemberBeingRemoved.value = null),
    //   },
    // );
  };
</script>

<template>
  <ConfirmationModal :show="teamMemberBeingRemoved" @close="teamMemberBeingRemoved = null">
    <template #title> Remove Team Member </template>

    <template #content> Are you sure you would like to remove this person from the team? </template>

    <template #footer>
      <SecondaryButton @click="teamMemberBeingRemoved = null"> Cancel </SecondaryButton>

      <DangerButton
        class="ms-3"
        :class="{ 'tw-opacity-25': removeTeamMemberForm.processing }"
        :disabled="removeTeamMemberForm.processing"
        @click="removeTeamMember"
      >
        Remove
      </DangerButton>
    </template>
  </ConfirmationModal>
</template>
