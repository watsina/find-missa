<script setup>
  import { ref, computed } from 'vue';
  import useAuthStore from '@/stores/useAuthStore';
  import Dropdown from '@/components/Dropdown.vue';
  import DropdownLink from '@/components/DropdownLink.vue';

  const authUser = computed(() => useAuthStore()?.user);
  const canCreateTeams = ref(false);
  //
</script>

<template>
  <!-- Teams Dropdown -->
  <Dropdown align="right" width="60">
    <template #trigger>
      <span class="inline-flex rounded-md">
        <button
          class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150"
          type="button"
        >
          {{ authUser.current_team.name }}

          <svg
            class="ms-2 -me-0.5 h-4 w-4"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </button>
      </span>
    </template>

    <template #content>
      <div class="w-60">
        <!-- Team Management -->
        <div class="block px-4 py-2 text-xs text-gray-400">Manage Team</div>

        <!-- Team Settings -->
        <DropdownLink :href="route('teams.show', authUser.current_team)">
          Team Settings
        </DropdownLink>

        <DropdownLink v-if="canCreateTeams" :href="route('teams.create')">
          Create New Team
        </DropdownLink>

        <!-- Team Switcher -->
        <template v-if="authUser.all_teams.length > 1">
          <div class="border-t border-gray-200" />

          <div class="block px-4 py-2 text-xs text-gray-400">Switch Teams</div>

          <template v-for="team in authUser.all_teams" :key="team.id">
            <form @submit.prevent="switchToTeam(team)">
              <DropdownLink as="button">
                <div class="flex items-center">
                  <svg
                    v-if="team.id == authUser.current_team_id"
                    class="me-2 h-5 w-5 text-green-400"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>

                  <div>{{ team.name }}</div>
                </div>
              </DropdownLink>
            </form>
          </template>
        </template>
      </div>
    </template>
  </Dropdown>
</template>
