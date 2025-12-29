<script setup>
  import { computed } from 'vue';
  import { mdiInformationOutline } from '@mdi/js';
  import { useActivitySchedule } from '@/composables';

  const props = defineProps({
    /** Activity schedule data with weekly, daily, monthly, one_time */
    schedule: { type: Object, default: null },
    /** Title for the section (e.g., "Mass Schedule") */
    title: { type: String, required: true },
    /** Icon to display next to title */
    icon: { type: String, required: true },
    /** Color for the icon and chips */
    color: { type: String, default: 'primary' },
    /** Activity type name for empty state message (e.g., "Mass", "Confession") */
    activityName: { type: String, required: true },
    /** URL for Google Form to submit schedules */
    googleFormUrl: { type: String, default: null },
    /** Whether to show all days in weekly table (true) or only days with activities (false) */
    showAllDays: { type: Boolean, default: true },
    /** Whether to show end time in activity descriptions */
    showEndTime: { type: Boolean, default: false },
  });

  const { daysOfWeek, formatActivityDescription, formatDate, hasActivityType } =
    useActivitySchedule();

  const hasData = computed(() => hasActivityType(props.schedule));

  const hasWeekly = computed(() => Object.keys(props.schedule?.weekly || {}).length > 0);

  const hasDaily = computed(() => (props.schedule?.daily || []).length > 0);

  const hasMonthly = computed(() => (props.schedule?.monthly || []).length > 0);

  const hasOneTime = computed(() => (props.schedule?.one_time || []).length > 0);
</script>

<template>
  <section :aria-label="title">
    <h2 class="tw:text-lg tw:font-semibold tw:mb-3 tw:flex tw:items-center tw:gap-2">
      <v-icon :color="color" :icon="icon" size="small" />
      {{ title }}
    </h2>

    <!-- Empty State -->
    <div v-if="!hasData">
      <v-alert class="tw:mb-0" color="info" :icon="mdiInformationOutline" variant="tonal">
        <p class="tw:mb-2">No {{ activityName }} schedule has been recorded for this church yet.</p>
        <p v-if="googleFormUrl" class="tw:mb-0 tw:text-sm">
          If you know the {{ activityName }} times for this church, please help us by submitting the
          schedule through our
          <a
            class="tw:text-primary tw:font-semibold tw:underline"
            :href="googleFormUrl"
            rel="noopener"
            target="_blank"
          >
            Google Form </a
          >.
        </p>
      </v-alert>
    </div>

    <div v-else>
      <!-- Weekly Schedule -->
      <div v-if="hasWeekly">
        <v-table density="comfortable">
          <thead>
            <tr>
              <th class="tw:font-bold tw:w-32">Day</th>
              <th class="tw:font-bold">Times</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="day in daysOfWeek" :key="day">
              <tr v-if="showAllDays || schedule?.weekly?.[day]">
                <td class="tw:font-medium">{{ day }}</td>
                <td>
                  <div v-if="schedule?.weekly?.[day]" class="tw:flex tw:flex-wrap tw:gap-2">
                    <v-chip
                      v-for="activity in schedule.weekly[day]"
                      :key="activity.id"
                      :color="color"
                      size="small"
                      variant="tonal"
                    >
                      {{ formatActivityDescription(activity, showEndTime) }}
                    </v-chip>
                  </div>
                  <span v-else class="tw:text-gray-400">-</span>
                </td>
              </tr>
            </template>
          </tbody>
        </v-table>
      </div>

      <!-- Daily Schedule -->
      <div v-if="hasDaily" class="tw:mt-4">
        <h3 class="tw:font-medium tw:mb-2">Daily {{ activityName }}</h3>
        <div class="tw:flex tw:flex-wrap tw:gap-2">
          <v-chip
            v-for="activity in schedule.daily"
            :key="activity.id"
            color="success"
            size="small"
            variant="tonal"
          >
            {{ formatActivityDescription(activity, showEndTime) }}
          </v-chip>
        </div>
      </div>

      <!-- Monthly Schedule -->
      <div v-if="hasMonthly" class="tw:mt-4">
        <h3 class="tw:font-medium tw:mb-2">Monthly {{ activityName }}</h3>
        <v-list density="compact">
          <v-list-item v-for="monthlyGroup in schedule.monthly" :key="monthlyGroup.label">
            <v-list-item-title class="tw:font-medium">
              {{ monthlyGroup.label }} of the Month
            </v-list-item-title>
            <v-list-item-subtitle>
              <div class="tw:flex tw:flex-wrap tw:gap-2 tw:mt-1">
                <v-chip
                  v-for="activity in monthlyGroup.activities"
                  :key="activity.id"
                  color="warning"
                  size="small"
                  variant="tonal"
                >
                  {{ formatActivityDescription(activity, showEndTime) }}
                </v-chip>
              </div>
            </v-list-item-subtitle>
          </v-list-item>
        </v-list>
      </div>

      <!-- One-time Schedule -->
      <div v-if="hasOneTime" class="tw:mt-4">
        <h3 class="tw:font-medium tw:mb-2">Special/One-time {{ activityName }}</h3>
        <v-list density="compact">
          <v-list-item v-for="activity in schedule.one_time" :key="activity.id">
            <v-list-item-title>
              {{ formatDate(activity.date) }}
            </v-list-item-title>
            <v-list-item-subtitle>
              {{ formatActivityDescription(activity, showEndTime) }}
            </v-list-item-subtitle>
          </v-list-item>
        </v-list>
      </div>
    </div>
  </section>
</template>
