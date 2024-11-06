<script setup>
  import { ref } from 'vue';
  import AppLayout from '@/layouts/AppLayout.vue';
  import DeleteTeamForm from './partials/DeleteTeamForm.vue';
  import SectionBorder from '@/components/SectionBorder.vue';
  import TeamMemberManager from './partials/TeamMemberManager.vue';
  import UpdateTeamNameForm from './partials/UpdateTeamNameForm.vue';

  const team = ref('');
  const availableRoles = ref([]);
  const permissions = ref([]);
</script>

<template>
  <AppLayout title="Team Settings">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Team Settings</h2>
    </template>

    <div>
      <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <UpdateTeamNameForm :permissions="permissions" :team="team" />

        <TeamMemberManager
          :available-roles="availableRoles"
          class="mt-10 sm:mt-0"
          :team="team"
          :user-permissions="permissions"
        />

        <template v-if="permissions.canDeleteTeam && !team.personal_team">
          <SectionBorder />

          <DeleteTeamForm class="mt-10 sm:mt-0" :team="team" />
        </template>
      </div>
    </div>
  </AppLayout>
</template>
