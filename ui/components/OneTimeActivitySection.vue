<script setup>
  import { computed } from 'vue';
  import { mdiCalendarStar, mdiCross, mdiClock, mdiHandsPray } from '@mdi/js';
  import { useActivitySchedule } from '@/composables';

  const props = defineProps({
    /** One-time activities grouped by type: { mass: [], confession: [], adoration: [] } */
    activities: { type: Object, default: null },
  });

  const { formatDate, formatActivityDescription } = useActivitySchedule();

  /**
   * Group activities by date within a type
   */
  const groupByDate = (activities) => {
    const groups = {};
    (activities || []).forEach((a) => {
      const date = a.date;
      if (!groups[date]) groups[date] = [];
      groups[date].push(a);
    });
    return Object.entries(groups).sort(([a], [b]) => a.localeCompare(b));
  };

  const massGroupedByDate = computed(() => groupByDate(props.activities?.mass));
  const confessionGroupedByDate = computed(() => groupByDate(props.activities?.confession));
  const adorationGroupedByDate = computed(() => groupByDate(props.activities?.adoration));

  const hasMass = computed(() => massGroupedByDate.value.length > 0);
  const hasConfession = computed(() => confessionGroupedByDate.value.length > 0);
  const hasAdoration = computed(() => adorationGroupedByDate.value.length > 0);
</script>

<template>
  <section>
    <h2 class="tw:text-lg tw:font-semibold tw:mb-3 tw:flex tw:items-center tw:gap-2">
      <v-icon color="accent" :icon="mdiCalendarStar" size="small" />
      Upcoming Special Events
    </h2>

    <!-- Mass One-time Events -->
    <div v-if="hasMass" class="tw:mb-4">
      <h3 class="tw:font-medium tw:mb-2 tw:flex tw:items-center tw:gap-1">
        <v-icon color="primary" :icon="mdiCross" size="x-small" />
        Special Masses
      </h3>
      <div v-for="[date, activities] in massGroupedByDate" :key="date" class="tw:mb-2">
        <div class="tw:text-sm tw:text-gray-600">{{ formatDate(date) }}</div>
        <div class="tw:flex tw:flex-wrap tw:gap-2 tw:mt-1">
          <v-chip v-for="a in activities" :key="a.id" color="primary" size="small" variant="tonal">
            {{ formatActivityDescription(a, false) }}
            <span v-if="a.reason" class="tw:ml-1">- {{ a.reason }}</span>
          </v-chip>
        </div>
      </div>
    </div>

    <!-- Confession One-time Events -->
    <div v-if="hasConfession" class="tw:mb-4">
      <h3 class="tw:font-medium tw:mb-2 tw:flex tw:items-center tw:gap-1">
        <v-icon color="secondary" :icon="mdiClock" size="x-small" />
        Special Confession Times
      </h3>
      <div v-for="[date, activities] in confessionGroupedByDate" :key="date" class="tw:mb-2">
        <div class="tw:text-sm tw:text-gray-600">{{ formatDate(date) }}</div>
        <div class="tw:flex tw:flex-wrap tw:gap-2 tw:mt-1">
          <v-chip
            v-for="a in activities"
            :key="a.id"
            color="secondary"
            size="small"
            variant="tonal"
          >
            {{ formatActivityDescription(a, true) }}
            <span v-if="a.reason" class="tw:ml-1">- {{ a.reason }}</span>
          </v-chip>
        </div>
      </div>
    </div>

    <!-- Adoration One-time Events -->
    <div v-if="hasAdoration">
      <h3 class="tw:font-medium tw:mb-2 tw:flex tw:items-center tw:gap-1">
        <v-icon color="info" :icon="mdiHandsPray" size="x-small" />
        Special Adoration Times
      </h3>
      <div v-for="[date, activities] in adorationGroupedByDate" :key="date" class="tw:mb-2">
        <div class="tw:text-sm tw:text-gray-600">{{ formatDate(date) }}</div>
        <div class="tw:flex tw:flex-wrap tw:gap-2 tw:mt-1">
          <v-chip v-for="a in activities" :key="a.id" color="info" size="small" variant="tonal">
            {{ formatActivityDescription(a, true) }}
            <span v-if="a.reason" class="tw:ml-1">- {{ a.reason }}</span>
          </v-chip>
        </div>
      </div>
    </div>
  </section>
</template>
