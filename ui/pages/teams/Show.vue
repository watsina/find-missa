<script setup>
  import { computed, onMounted, ref, watch } from 'vue';
  import AppLayout from '@/layouts/AppLayout.vue';
  import UpdateTeamNameForm from './partials/UpdateTeamNameForm.vue';
  import { http } from '@/plugins';
  import { notify } from '@/utils';
  import useAuthStore from '@/stores/useAuthStore';

  const props = defineProps({
    teamId: { type: [String, Number], required: true },
  });

  const authStore = useAuthStore();

  const canUpdateTeam = computed(() => authStore.hasPermission('teams.write'));
  const canDeleteTeam = computed(() => authStore.hasPermission('teams.write'));

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
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Team Settings</h2>
    </template>

    <v-container>
      <v-skeleton-loader
        v-if="fetchingRoles || fetchingTeam"
        type="card-avatar"
      ></v-skeleton-loader>

      <v-card v-else-if="!teamId || !team">
        <v-card-text> No team found </v-card-text>
      </v-card>

      <div v-else>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
          <UpdateTeamNameForm :can-update-team="canUpdateTeam" :team="team" />

          <!-- <TeamMemberManager -->
          <!--   :available-roles="availableRoles" -->
          <!--   class="mt-10 sm:mt-0" -->
          <!--   :team="team" -->
          <!--   :user-permissions="permissions" -->
          <!-- /> -->
        </div>
      </div>
    </v-container>
  </AppLayout>
</template>
