<script setup>
  import { mdiCalendarHeart, mdiInformationOutline } from '@mdi/js';
  import { useActivitySchedule } from '@/composables';

  defineProps({
    /** Array of liturgical/holiday schedules */
    schedules: { type: Array, default: () => [] },
  });

  const { formatActivityDescription } = useActivitySchedule();
</script>

<template>
  <section>
    <h2 class="tw:text-lg tw:font-semibold tw:mb-3 tw:flex tw:items-center tw:gap-2">
      <v-icon color="error" :icon="mdiCalendarHeart" size="small" />
      Holiday Schedules
    </h2>

    <v-expansion-panels v-if="schedules.length">
      <v-expansion-panel v-for="schedule in schedules" :key="schedule.id">
        <v-expansion-panel-title>
          <div class="tw:flex tw:items-center tw:gap-2">
            <span class="tw:font-semibold">{{ schedule.holiday?.name }}</span>
            <v-chip v-if="schedule.replaces_regular_schedule" color="warning" size="x-small">
              Replaces Regular
            </v-chip>
          </div>
        </v-expansion-panel-title>
        <v-expansion-panel-text>
          <p v-if="schedule.notes" class="tw:text-sm tw:text-gray-600 tw:mb-2">
            {{ schedule.notes }}
          </p>
          <div class="tw:flex tw:flex-wrap tw:gap-2">
            <v-chip
              v-for="time in schedule.times"
              :key="time.id"
              color="primary"
              size="small"
              variant="tonal"
            >
              {{ formatActivityDescription(time, false) }}
            </v-chip>
          </div>
        </v-expansion-panel-text>
      </v-expansion-panel>
    </v-expansion-panels>

    <v-alert v-else color="info" :icon="mdiInformationOutline" variant="tonal">
      No holiday schedules have been recorded for this church yet.
    </v-alert>
  </section>
</template>
