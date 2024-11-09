<script setup>
  import { ref } from 'vue';
  import ConfirmationModal from '@/components/ConfirmationModal.vue';
  import DangerButton from '@/components/DangerButton.vue';
  import SecondaryButton from '@/components/SecondaryButton.vue';
  import { useForm } from '@/plugins';

  defineProps({
    availableRoles: { type: Array, required: true },
  });

  const leaveTeamForm = useForm({});

  const confirmingLeavingTeam = ref(false);

  const leaveTeam = () => {
    // leaveTeamForm.delete(route('team-members.destroy', [props.team, authUser]));
  };
</script>

<template>
  <ConfirmationModal :show="confirmingLeavingTeam" @close="confirmingLeavingTeam = false">
    <template #title> Leave Team </template>

    <template #content> Are you sure you would like to leave this team? </template>

    <template #footer>
      <SecondaryButton @click="confirmingLeavingTeam = false"> Cancel </SecondaryButton>

      <DangerButton
        class="ms-3"
        :class="{ 'tw-opacity-25': leaveTeamForm.processing }"
        :disabled="leaveTeamForm.processing"
        @click="leaveTeam"
      >
        Leave
      </DangerButton>
    </template>
  </ConfirmationModal>
</template>
