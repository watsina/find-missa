<script setup>
  import { computed, onMounted, ref, watch } from 'vue';
  import AppLayout from '@/layouts/AppLayout.vue';
  import UpdateTeamNameForm from './partials/UpdateTeamNameForm.vue';
  import TeamMembers from './partials/TeamMembers.vue';
  import AddTeamMember from './partials/AddTeamMember.vue';
  import TeamInvitations from './partials/TeamInvitations.vue';
  import { http } from '@/plugins';
  import { notify } from '@/utils';
  import useAuthStore from '@/stores/useAuthStore';

  const props = defineProps({
    teamId: { type: [String, Number], required: true },
  });

  const authStore = useAuthStore();

  const canUpdateTeam = computed(() => authStore.hasPermission('teams.write'));

  const team = ref('');
  const fetchingTeam = ref(false);
  const fetchingRoles = ref(false);
  const availableRoles = ref([]);

  function fetchRolesAndPermissions() {
    fetchingRoles.value = true;
    http
      .get('/api/v1/roles')
      .then(({ data }) => {
        availableRoles.value = data?.data || [];
      })
      .catch(() => notify.error('Something went wrong fetching roles and permissions.'))
      .finally(() => (fetchingRoles.value = false));
  }

  function fetchTeam() {
    if (!props.teamId) return;
    fetchingTeam.value = true;
    http
      .get(`/api/v1/teams/${props.teamId}`)
      .then(({ data }) => {
        team.value = data?.data;
      })
      .catch(() => notify.error('Something went wrong fetching team details.'))
      .finally(() => (fetchingTeam.value = false));
  }

  onMounted(() => {
    fetchTeam();
    fetchRolesAndPermissions();
  });

  watch(
    () => props.teamId,
    () => fetchTeam(),
  );
</script>

<template>
  <AppLayout title="Team Settings">
    <template #head>
      <h2 class="tw-font-semibold tw-text-xl tw-text-gray-800 tw-leading-tight">Team Settings</h2>
    </template>

    <v-container>
      <v-row class="tw-max-w-7xl tw-mx-auto tw-py-10 sm:tw-px-6 lg:tw-px-8">
        <v-col v-if="fetchingRoles || fetchingTeam" cols="12">
          <v-skeleton-loader type="card-avatar"></v-skeleton-loader>
        </v-col>

        <v-col v-else-if="!teamId || !team" cols="12">
          <v-card>
            <v-card-text> No team found </v-card-text>
          </v-card>
        </v-col>
        <v-col v-else cols="12">
          <UpdateTeamNameForm :can-update-team="canUpdateTeam" :team="team" />

          <AddTeamMember v-model:team="team" :available-roles="availableRoles" class="tw-mt-10" />

          <TeamInvitations v-model:team="team" :available-roles="availableRoles" class="tw-mt-10" />

          <TeamMembers v-model:team="team" :available-roles="availableRoles" class="tw-mt-10" />
        </v-col>
      </v-row>
    </v-container>
  </AppLayout>
</template>
