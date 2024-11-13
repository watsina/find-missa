<script setup>
  import { computed } from 'vue';
  import useAuthStore from '@/stores/useAuthStore';
  import { mdiCheckCircleOutline, mdiUnfoldMoreHorizontal } from '@mdi/js';
  import { http } from '@/plugins';
  import { notify } from '@/utils';
  import { useRouter } from 'vue-router';

  const authStore = useAuthStore();

  const router = useRouter();

  const authUser = computed(() => authStore.user);
  const canCreateTeams = computed(() => authStore.hasPermission('teams.write'));
  const hasManyTeams = computed(() => authStore.all_teams.length > 1);
  const canViewTeam = computed(() => authStore.hasPermission('team.manage'));

  const switchToTeam = (team) => {
    http
      .put('/api/v1/switch-team', { team_id: team.id })
      .then(async () => {
        await router.push({ name: 'dashboard.index' });
        window.location.reload();
      })
      .catch(() => notify.error('Something went wrong while switching teams.'));
  };
</script>

<template>
  <!-- Teams Dropdown -->
  <v-menu offset-y>
    <template #activator="{ props }">
      <v-btn dark v-bind="props">
        {{ authStore.current_team?.name || 'No team selected' }}
        <v-icon v-if="canViewTeam || hasManyTeams" :icon="mdiUnfoldMoreHorizontal"></v-icon>
      </v-btn>
    </template>
    <v-list v-if="canViewTeam || hasManyTeams" density="compact">
      <!-- Team Settings -->
      <template v-if="canViewTeam">
        <v-list-subheader class="!tw-text-gray-500 hover:!tw-text-gray-700">
          Manage Team
        </v-list-subheader>
        <v-list-item
          v-if="authUser?.is_admin"
          class="!tw-text-gray-700"
          :to="{ name: 'acl.index' }"
        >
          Roles and Permissions
        </v-list-item>
        <v-list-item
          v-if="authUser?.current_team_id"
          class="!tw-text-gray-700"
          :to="{ name: 'teams.show', params: { teamId: authUser.current_team_id } }"
        >
          Team Settings
        </v-list-item>
        <v-list-item v-if="canCreateTeams" class="!tw-text-gray-700" :to="{ name: 'teams.create' }">
          Create New Team
        </v-list-item>
      </template>
      <!-- Team Switcher -->
      <template v-if="hasManyTeams">
        <div class="tw-border-t tw-border-gray-200" />
        <v-list-subheader class="tw-text-gray-400">Switch Teams</v-list-subheader>
        <v-list-item v-for="team in authStore.all_teams" :key="team.id" @click="switchToTeam(team)">
          <v-list-item-title class="tw-text-gray-700">
            <v-icon
              v-if="team.id == authUser?.current_team_id"
              class="tw-mr-2"
              color="success"
              :icon="mdiCheckCircleOutline"
            ></v-icon>
            {{ team.name }}
          </v-list-item-title>
        </v-list-item>
      </template>
    </v-list>
  </v-menu>
</template>
