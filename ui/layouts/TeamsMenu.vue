<script setup>
  import { computed } from 'vue';
  import useAuthStore from '@/stores/useAuthStore';
  import { mdiCheckCircleOutline, mdiUnfoldMoreHorizontal } from '@mdi/js';

  const authStore = useAuthStore();

  const authUser = computed(() => authStore.user);
  const canCreateTeams = computed(() => authStore.hasPermission('teams.write'));
  //
</script>

<template>
  <!-- Teams Dropdown -->
  <v-menu offset-y>
    <template #activator="{ props }">
      <v-btn dark x-small v-bind="props">
        {{ authStore.current_team.name }}
        <v-icon :icon="mdiUnfoldMoreHorizontal"></v-icon>
      </v-btn>
    </template>
    <v-list density="compact">
      <!-- Team Settings -->
      <template v-if="authStore.hasPermission('team.manage')">
        <v-list-subheader class="!tw-text-gray-500 hover:!tw-text-gray-700">
          Manage Team
        </v-list-subheader>
        <v-list-item
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
      <template v-if="authStore.all_teams.length > 1">
        <div class="tw-border-t tw-border-gray-200" />
        <v-list-subheader class="tw-text-gray-400">Switch Teams</v-list-subheader>
        <v-list-item v-for="team in authStore.all_teams" :key="team.id">
          <v-list-item-title class="tw-text-gray-700">
            <v-icon
              v-if="team.id == authUser.current_team_id"
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
