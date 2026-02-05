<script setup>
  import { computed } from 'vue';
  import { mdiInformationOutline } from '@mdi/js';
  import { useActivitySchedule } from '@/composables';

  const props = defineProps({
    /** V2 activity schedule data with weekdays, saturday, sunday, daily, specific, monthly_nth */
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

  const { daysOfWeek, formatActivityDescription, hasActivityTypeV2 } = useActivitySchedule();

  const hasData = computed(() => hasActivityTypeV2(props.schedule));
  const hasMonthlyNth = computed(() => (props.schedule?.monthly_nth || []).length > 0);

  /**
   * Build weekly object by expanding v2 patterns into individual days.
   * Handles: weekdays, saturday, sunday, daily, specific, and day_details variations.
   */
  const weeklySchedule = computed(() => {
    const weekly = {
      Sunday: [],
      Monday: [],
      Tuesday: [],
      Wednesday: [],
      Thursday: [],
      Friday: [],
      Saturday: [],
    };
    const schedule = props.schedule;
    if (!schedule) return weekly;

    // Add Sunday schedules
    (schedule.sunday || []).forEach((s) => weekly.Sunday.push(s));

    // Add Saturday schedules
    (schedule.saturday || []).forEach((s) => weekly.Saturday.push(s));

    // Add Weekday schedules (Mon-Fri)
    const weekdayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    (schedule.weekdays || []).forEach((s) => {
      if (s.has_day_variations && s.day_details?.length) {
        // Expand day variations - each day gets its specific time/language
        const detailsByDay = {};
        s.day_details.forEach((detail) => {
          if (!detailsByDay[detail.day_of_week]) {
            detailsByDay[detail.day_of_week] = [];
          }
          detailsByDay[detail.day_of_week].push(detail);
        });

        weekdayNames.forEach((day) => {
          const dayDetails = detailsByDay[day] || [];
          if (dayDetails.length > 0) {
            dayDetails.forEach((detail) => {
              if (!detail.is_skipped) {
                weekly[day].push({
                  ...s,
                  id: `${s.id}-${day}-${detail.start_time}`,
                  start_time: detail.start_time || s.start_time,
                  end_time: detail.end_time || s.end_time,
                  language: detail.language || s.language,
                });
              }
            });
          } else {
            // No specific detail for this day - use base schedule
            weekly[day].push(s);
          }
        });
      } else {
        // No variations - add to all weekdays
        weekdayNames.forEach((day) => weekly[day].push(s));
      }
    });

    // Add Daily schedules (all days)
    (schedule.daily || []).forEach((s) => {
      Object.keys(weekly).forEach((day) => weekly[day].push(s));
    });

    // Add Specific day schedules
    (schedule.specific || []).forEach((s) => {
      (s.days_of_week || []).forEach((day) => {
        if (weekly[day]) weekly[day].push(s);
      });
    });

    // Sort each day by start_time
    Object.keys(weekly).forEach((day) => {
      weekly[day].sort((a, b) => (a.start_time || '').localeCompare(b.start_time || ''));
    });

    return weekly;
  });

  const hasWeeklyData = computed(() =>
    Object.values(weeklySchedule.value).some((day) => day.length > 0),
  );
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
      <!-- Weekly Schedule Table -->
      <div v-if="hasWeeklyData">
        <v-table density="comfortable">
          <thead>
            <tr>
              <th class="tw:font-bold tw:w-32">Day</th>
              <th class="tw:font-bold">Times</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="day in daysOfWeek" :key="day">
              <tr v-if="showAllDays || weeklySchedule[day]?.length">
                <td class="tw:font-medium">{{ day }}</td>
                <td>
                  <div v-if="weeklySchedule[day]?.length" class="tw:flex tw:flex-wrap tw:gap-2">
                    <v-chip
                      v-for="activity in weeklySchedule[day]"
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

      <!-- Monthly Schedule (separate section) -->
      <div v-if="hasMonthlyNth" class="tw:mt-4">
        <h3 class="tw:font-medium tw:mb-2">Monthly {{ activityName }}</h3>
        <v-list density="compact">
          <v-list-item v-for="group in schedule.monthly_nth" :key="group.label">
            <v-list-item-title class="tw:font-medium">
              {{ group.label }}
            </v-list-item-title>
            <v-list-item-subtitle>
              <div class="tw:flex tw:flex-wrap tw:gap-2 tw:mt-1">
                <v-chip
                  v-for="s in group.schedules"
                  :key="s.id"
                  color="warning"
                  size="small"
                  variant="tonal"
                >
                  {{ formatActivityDescription(s, showEndTime) }}
                </v-chip>
              </div>
            </v-list-item-subtitle>
          </v-list-item>
        </v-list>
      </div>
    </div>
  </section>
</template>
