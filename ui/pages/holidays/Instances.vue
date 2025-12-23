<script setup>
  import {
    mdiCalendarMonth,
    mdiArrowLeft,
    mdiChurch,
    mdiRefresh,
    mdiEarth,
    mdiFlag,
  } from '@mdi/js';
  import AppLayout from '@/layouts/AppLayout';
  import { ref, computed, watch } from 'vue';
  import { http } from '@/plugins';
  import { useRouter } from 'vue-router';

  const router = useRouter();
  const currentYear = new Date().getFullYear();
  const selectedYear = ref(currentYear);
  const instances = ref([]);
  const loading = ref(false);

  // Generate year options (5 years back, 5 years forward)
  const yearOptions = computed(() => {
    const years = [];
    for (let i = currentYear - 5; i <= currentYear + 5; i++) {
      years.push(i);
    }
    return years;
  });

  // Group instances by month
  const instancesByMonth = computed(() => {
    const grouped = {};
    const months = [
      'January',
      'February',
      'March',
      'April',
      'May',
      'June',
      'July',
      'August',
      'September',
      'October',
      'November',
      'December',
    ];

    // Initialize all months
    months.forEach((month, index) => {
      grouped[index + 1] = { name: month, instances: [] };
    });

    // Group instances by month
    instances.value.forEach((instance) => {
      if (instance.date) {
        const month = new Date(instance.date).getMonth() + 1;
        if (grouped[month]) {
          grouped[month].instances.push(instance);
        }
      }
    });

    return grouped;
  });

  // Count of holidays
  const holidayCount = computed(() => instances.value.length);
  const holyDayCount = computed(
    () => instances.value.filter((i) => i.holiday?.is_holy_day_of_obligation).length,
  );
  const universalCount = computed(
    () => instances.value.filter((i) => i.holiday?.is_universal).length,
  );
  const teamSpecificCount = computed(
    () => instances.value.filter((i) => !i.holiday?.is_universal).length,
  );

  async function fetchInstances() {
    loading.value = true;
    try {
      const response = await http.get(`/api/v1/holidays/instances/${selectedYear.value}`);
      instances.value = response.data.data || [];
    } catch {
      instances.value = [];
    } finally {
      loading.value = false;
    }
  }

  function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      weekday: 'long',
      month: 'short',
      day: 'numeric',
    });
  }

  function getLiturgicalColor(color) {
    const colors = {
      white: 'grey-lighten-1',
      red: 'red',
      green: 'green',
      violet: 'purple',
      purple: 'purple',
      rose: 'pink',
      gold: 'amber',
      black: 'grey-darken-3',
    };
    return colors[color?.toLowerCase()] || 'grey-lighten-1';
  }

  function goBack() {
    router.push({ name: 'holidays.index' });
  }

  // Fetch instances when year changes
  watch(selectedYear, fetchInstances, { immediate: true });
</script>

<template>
  <app-layout>
    <template #head>
      <div class="tw:flex tw:items-center tw:gap-4">
        <v-btn :icon="mdiArrowLeft" variant="text" @click="goBack"></v-btn>
        <h2 class="tw:text-xl tw:font-semibold tw:leading-tight tw:text-gray-800">
          Holiday Calendar
        </h2>
      </div>
    </template>
    <v-container>
      <v-row class="tw:py-8">
        <!-- Header with year selector and stats -->
        <v-col cols="12">
          <v-card variant="flat">
            <v-card-text>
              <div class="tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-4">
                <div class="tw:flex tw:items-center tw:gap-4">
                  <v-select
                    v-model="selectedYear"
                    density="compact"
                    hide-details
                    :items="yearOptions"
                    label="Year"
                    :prepend-inner-icon="mdiCalendarMonth"
                    style="max-width: 150px"
                    variant="outlined"
                  />
                  <v-btn
                    color="primary"
                    :icon="mdiRefresh"
                    :loading="loading"
                    variant="text"
                    @click="fetchInstances"
                  />
                </div>
                <div class="tw:flex tw:flex-wrap tw:gap-2">
                  <v-chip color="primary" variant="tonal"> {{ holidayCount }} Holidays </v-chip>
                  <v-chip color="warning" :prepend-icon="mdiChurch" variant="tonal">
                    {{ holyDayCount }} Holy Days of Obligation
                  </v-chip>
                  <v-chip color="info" :prepend-icon="mdiEarth" variant="tonal">
                    {{ universalCount }} Universal
                  </v-chip>
                  <v-chip color="secondary" :prepend-icon="mdiFlag" variant="tonal">
                    {{ teamSpecificCount }} Team Specific
                  </v-chip>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Loading state -->
        <v-col v-if="loading" cols="12">
          <v-skeleton-loader type="article, article, article" />
        </v-col>

        <!-- Empty state -->
        <v-col v-else-if="!holidayCount" cols="12">
          <v-alert :icon="mdiCalendarMonth" type="info" variant="tonal">
            No holidays found for {{ selectedYear }}. Create holidays first to see their computed
            dates.
          </v-alert>
        </v-col>

        <!-- Calendar grid -->
        <template v-else>
          <v-col
            v-for="(month, monthNum) in instancesByMonth"
            :key="monthNum"
            cols="12"
            lg="4"
            md="6"
          >
            <v-card class="tw:h-full" variant="outlined">
              <v-card-title class="tw:bg-gray-50 tw:border-b">
                {{ month.name }}
                <v-chip v-if="month.instances.length" class="tw:ml-2" size="x-small">
                  {{ month.instances.length }}
                </v-chip>
              </v-card-title>
              <v-card-text class="tw:pa-0">
                <v-list v-if="month.instances.length" density="compact" lines="two">
                  <v-list-item
                    v-for="instance in month.instances"
                    :key="instance.id"
                    class="tw:border-b tw:last:border-b-0"
                  >
                    <template #prepend>
                      <v-avatar
                        :color="getLiturgicalColor(instance.holiday?.liturgical_color)"
                        size="32"
                      >
                        <span class="tw:text-xs tw:text-white tw:font-bold">
                          {{ new Date(instance.date).getDate() }}
                        </span>
                      </v-avatar>
                    </template>
                    <v-list-item-title class="tw:font-medium">
                      {{ instance.holiday?.name }}
                    </v-list-item-title>
                    <v-list-item-subtitle>
                      {{ formatDate(instance.date) }}
                    </v-list-item-subtitle>
                    <template #append>
                      <div class="tw:flex tw:items-center tw:gap-1">
                        <v-tooltip
                          v-if="instance.holiday?.is_holy_day_of_obligation"
                          location="top"
                        >
                          <template #activator="{ props }">
                            <v-icon v-bind="props" color="warning" :icon="mdiChurch" size="small" />
                          </template>
                          Holy Day of Obligation
                        </v-tooltip>
                        <v-tooltip location="top">
                          <template #activator="{ props }">
                            <v-icon
                              v-bind="props"
                              :color="instance.holiday?.is_universal ? 'info' : 'secondary'"
                              :icon="instance.holiday?.is_universal ? mdiEarth : mdiFlag"
                              size="small"
                            />
                          </template>
                          {{
                            instance.holiday?.is_universal
                              ? 'Universal Holiday'
                              : 'Team Specific Holiday'
                          }}
                        </v-tooltip>
                      </div>
                    </template>
                  </v-list-item>
                </v-list>
                <div v-else class="tw:p-4 tw:text-center tw:text-gray-400 tw:text-sm">
                  No holidays
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </template>
      </v-row>
    </v-container>
  </app-layout>
</template>
