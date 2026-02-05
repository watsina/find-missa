<script setup>
  import { computed } from 'vue';
  import { useActivitySchedule } from '@/composables';

  const props = defineProps({
    /** V2 activity schedule data with weekdays, saturday, sunday, daily, specific, monthly_nth */
    schedule: { type: Object, default: null },
    /** Title for the section (e.g., "Mass Schedule") */
    title: { type: String, required: true },
    /** Color for chips */
    color: { type: String, default: 'primary' },
    /** Activity type name (e.g., "Mass", "Confession") */
    activityName: { type: String, required: true },
    /** Whether to show end time in activity descriptions */
    showEndTime: { type: Boolean, default: false },
  });

  const { daysOfWeek, formatActivityDescription, hasActivityTypeV2 } = useActivitySchedule();

  const hasData = computed(() => hasActivityTypeV2(props.schedule));
  const hasMonthlyNth = computed(() => (props.schedule?.monthly_nth || []).length > 0);

  /**
   * Build weekly object by expanding v2 patterns into individual days.
   * Same logic as ActivityScheduleSection but simplified for period display.
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
            weekly[day].push(s);
          }
        });
      } else {
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

  // Get only days that have activities
  const daysWithActivities = computed(() =>
    daysOfWeek.filter((day) => weeklySchedule.value[day]?.length > 0),
  );
</script>

<template>
  <div v-if="hasData" class="tw:mb-4">
    <h3 class="tw:font-medium tw:mb-2">{{ title }}</h3>

    <!-- Weekly Schedule (compact format for periods) -->
    <div v-if="hasWeeklyData">
      <div v-for="day in daysWithActivities" :key="day" class="tw:mb-2">
        <span class="tw:font-medium">{{ day }}:</span>
        <span v-for="activity in weeklySchedule[day]" :key="activity.id" class="tw:ml-2">
          <v-chip :color="color" size="small" variant="tonal">
            {{ formatActivityDescription(activity, showEndTime) }}
          </v-chip>
        </span>
      </div>
    </div>

    <!-- Monthly Schedule -->
    <div v-if="hasMonthlyNth" class="tw:mt-2">
      <div v-for="group in schedule.monthly_nth" :key="group.label">
        <span class="tw:font-medium">{{ group.label }}:</span>
        <v-chip
          v-for="s in group.schedules"
          :key="s.id"
          class="tw:ml-2"
          color="warning"
          size="small"
          variant="tonal"
        >
          {{ formatActivityDescription(s, showEndTime) }}
        </v-chip>
      </div>
    </div>
  </div>
</template>
