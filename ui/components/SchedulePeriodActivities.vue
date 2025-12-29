<script setup>
  import { computed } from 'vue';
  import { useActivitySchedule } from '@/composables';

  const props = defineProps({
    /** Activity schedule data with weekly, daily, monthly, one_time */
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

  const { formatActivityDescription, formatDate, hasActivityType } = useActivitySchedule();

  const hasData = computed(() => hasActivityType(props.schedule));

  const hasWeekly = computed(() => Object.keys(props.schedule?.weekly || {}).length > 0);

  const hasDaily = computed(() => (props.schedule?.daily || []).length > 0);

  const hasMonthly = computed(() => (props.schedule?.monthly || []).length > 0);

  const hasOneTime = computed(() => (props.schedule?.one_time || []).length > 0);
</script>

<template>
  <div v-if="hasData" class="tw:mb-4">
    <h3 class="tw:font-medium tw:mb-2">{{ title }}</h3>

    <!-- Weekly -->
    <div v-if="hasWeekly">
      <div v-for="(activities, day) in schedule.weekly" :key="day" class="tw:mb-2">
        <span class="tw:font-medium">{{ day }}:</span>
        <span v-for="activity in activities" :key="activity.id" class="tw:ml-2">
          <v-chip :color="color" size="small" variant="tonal">
            {{ formatActivityDescription(activity, showEndTime) }}
          </v-chip>
        </span>
      </div>
    </div>

    <!-- Daily -->
    <div v-if="hasDaily" class="tw:mt-2">
      <span class="tw:font-medium">Daily:</span>
      <v-chip
        v-for="activity in schedule.daily"
        :key="activity.id"
        class="tw:ml-2"
        color="success"
        size="small"
        variant="tonal"
      >
        {{ formatActivityDescription(activity, showEndTime) }}
      </v-chip>
    </div>

    <!-- Monthly -->
    <div v-if="hasMonthly" class="tw:mt-2">
      <div v-for="monthlyGroup in schedule.monthly" :key="monthlyGroup.label">
        <span class="tw:font-medium">{{ monthlyGroup.label }} of Month:</span>
        <v-chip
          v-for="activity in monthlyGroup.activities"
          :key="activity.id"
          class="tw:ml-2"
          color="warning"
          size="small"
          variant="tonal"
        >
          {{ formatActivityDescription(activity, showEndTime) }}
        </v-chip>
      </div>
    </div>

    <!-- One-time -->
    <div v-if="hasOneTime" class="tw:mt-2">
      <div class="tw:font-medium tw:mb-1">Special/One-time:</div>
      <div v-for="activity in schedule.one_time" :key="activity.id" class="tw:mb-1">
        <v-chip :color="color" size="small" variant="outlined">
          {{ formatDate(activity.date) }} - {{ formatActivityDescription(activity, showEndTime) }}
        </v-chip>
      </div>
    </div>
  </div>
</template>
