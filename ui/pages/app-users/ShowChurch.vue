<script setup>
  import { ref, onMounted, computed } from 'vue';
  import { http } from '@/plugins';
  import AppUserLayout from '@/layouts/AppUserLayout';
  import { notify } from '@/utils';
  import EntityDetails from '@/components/EntityDetails.vue';
  import {
    mdiCalendarClock,
    mdiCalendarMonth,
    mdiChurch,
    mdiCity,
    mdiClock,
    mdiCross,
    mdiDirections,
    mdiEmailFast,
    mdiHandsPray,
    mdiHomeCity,
    mdiInformationOutline,
    mdiPhoneClassic,
    mdiTownHall,
    mdiWeb,
  } from '@mdi/js';
  import { useSeoMeta, useHead } from '@unhead/vue';

  const props = defineProps({
    churchId: { type: [String, Number], required: true },
  });

  const church = ref(null);
  const regularSchedule = ref(null);
  const schedulePeriods = ref([]);

  // SEO: Dynamic page title
  const pageTitle = computed(() => {
    if (!church.value?.id) return 'Church Details | Find Missa';
    const name = church.value.name;
    const city = church.value.city || church.value.district || '';
    return `${name}${city ? ` - ${city}` : ''} | Mass Times, Confession & Adoration Schedule`;
  });

  // SEO: Dynamic meta description
  const pageDescription = computed(() => {
    if (!church.value?.id) {
      return 'Find Catholic Mass times, Confession schedule and Adoration hours at churches in Uganda.';
    }
    const name = church.value.name;
    const city = church.value.city || '';
    const district = church.value.district || '';
    const location = [city, district].filter(Boolean).join(', ');

    return `Mass times, Confession schedule and Adoration hours at ${name}${location ? ` in ${location}` : ''}, Uganda. Find weekly schedules, special liturgical seasons and more.`;
  });

  // SEO: Structured keywords
  const pageKeywords = computed(() => {
    if (!church.value?.id) return 'Catholic Mass, Confession, Adoration, Uganda';
    const name = church.value.name;
    const city = church.value.city || '';
    const district = church.value.district || '';
    return `${name}, Mass times, Confession schedule, Adoration hours, Catholic church${city ? `, ${city}` : ''}${district ? `, ${district}` : ''}, Uganda`;
  });

  // SEO meta tags
  useSeoMeta({
    title: () => pageTitle.value,
    description: () => pageDescription.value,
    keywords: () => pageKeywords.value,
    ogTitle: () => pageTitle.value,
    ogDescription: () => pageDescription.value,
    ogType: 'website',
    ogUrl: () => `https://findmissa.com/church-details/${props.churchId}`,
    ogSiteName: 'Find Missa',
    twitterCard: 'summary',
    twitterTitle: () => pageTitle.value,
    twitterDescription: () => pageDescription.value,
  });

  // SEO: Additional head elements
  useHead({
    htmlAttrs: { lang: 'en' },
    link: [{ rel: 'canonical', href: `https://findmissa.com/church-details/${props.churchId}` }],
  });

  const isFetching = ref(false);
  const activeTab = ref('regular');

  const daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

  const fields = computed(() => {
    const data = church.value;

    return [
      { icon: mdiHomeCity, text: 'Parish', value: data?.parish?.name },
      { icon: mdiCity, text: 'City/Town', value: data?.city },
      { icon: mdiTownHall, text: 'District/State', value: data?.district },
      { icon: mdiEmailFast, text: 'Contact Email', value: data?.contact_email },
      { icon: mdiWeb, text: 'Website', value: data?.website_url, is_link: true },
      { icon: mdiPhoneClassic, text: 'Contact Phone', value: data?.contact_phone },
    ];
  });

  // Computed: Check if there are any schedule periods with activities
  const hasSchedulePeriods = computed(() =>
    schedulePeriods.value.some((p) => hasActivities(p.activities)),
  );

  // Computed: Get current schedule periods
  const currentSchedulePeriods = computed(() => schedulePeriods.value.filter((p) => p.is_current));

  // Computed: Check if a schedule has any activities
  const hasActivities = (schedule) => {
    if (!schedule) return false;
    return (
      hasActivityType(schedule.mass) ||
      hasActivityType(schedule.confession) ||
      hasActivityType(schedule.adoration)
    );
  };

  // Check if an activity type has any activities
  const hasActivityType = (activityType) => {
    if (!activityType) return false;
    return (
      Object.keys(activityType.weekly || {}).length > 0 ||
      (activityType.daily || []).length > 0 ||
      (activityType.monthly || []).length > 0 ||
      (activityType.one_time || []).length > 0
    );
  };

  // Format time display
  const formatTime = (activity) => {
    let time = activity.start_time;
    // if (activity.end_time) {
    //   time += ` - ${activity.end_time}`;
    // }
    return time;
  };

  // Format activity description
  const formatActivityDescription = (activity) => {
    const parts = [];
    parts.push(formatTime(activity));
    if (activity.language) {
      parts.push(`(${activity.language})`);
    }
    if (
      activity.activity_type_description &&
      activity.activity_type_description !== activity.type
    ) {
      parts.push(`- ${formatTypeDescription(activity.activity_type_description)}`);
    }
    return parts.join(' ');
  };

  // Format type description to human readable
  const formatTypeDescription = (type) => {
    const typeMap = {
      mass: 'Mass',
      'anticipation-mass': 'Anticipation Mass',
      'traditional-latin-mass': 'Traditional Latin Mass',
      confession: 'Confession',
      adoration: 'Adoration',
      benediction: 'Benediction',
      'mass-of-the-lords-supper': "Mass of the Lord's Supper",
      'good-friday-service': 'Good Friday Service',
      'easter-vigil-mass': 'Easter Vigil Mass',
      'easter-mass': 'Easter Mass',
      'easter-monday-mass': 'Easter Monday Mass',
      'christmas-mass': 'Christmas Mass',
      'christmas-vigil-mass': 'Christmas Vigil Mass',
    };
    return typeMap[type] || type;
  };

  // Format date for one-time events
  const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  onMounted(() => {
    fetchChurchDetails();
  });

  const fetchChurchDetails = () => {
    if (!props.churchId) return;
    isFetching.value = true;
    http
      .get(`/api/v1/church-details/${props.churchId}`)
      .then(({ data }) => {
        church.value = data?.data?.details || {};
        regularSchedule.value = data?.data?.regular_schedule || null;
        schedulePeriods.value = data?.data?.schedule_periods || [];
      })
      .catch(() => notify.error('Something went wrong fetching the church details.'))
      .finally(() => (isFetching.value = false));
  };

  // Google Form URL for submitting schedule updates
  const googleFormUrl = 'https://forms.gle/kGiX1A36dW3is1BJA';
</script>

<template>
  <AppUserLayout>
    <template #head>
      <h1 class="tw:flex tw:items-center tw:gap-2 tw:text-xl tw:font-bold">
        <v-icon :icon="mdiChurch" size="small" />
        Church Details
      </h1>
    </template>
    <v-container>
      <v-skeleton-loader v-if="isFetching" type="card, article, article"></v-skeleton-loader>

      <v-card v-else-if="!church?.id">
        <v-card-title>No church found</v-card-title>
        <v-card-text>
          The church you are looking for could not be found. Please check the URL and try again.
        </v-card-text>
      </v-card>

      <article v-else itemscope itemtype="https://schema.org/Church">
        <!-- Church Header & Details -->
        <header>
          <v-row class="tw:mb-4">
            <v-col cols="12">
              <EntityDetails :fields="fields" :title="church?.name">
                <template #subtitle>
                  <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-2">
                    <meta :content="church?.name" itemprop="name" />
                    <meta v-if="church?.city" :content="church?.city" itemprop="addressLocality" />
                    <meta
                      v-if="church?.district"
                      :content="church?.district"
                      itemprop="addressRegion"
                    />
                    <meta content="Uganda" itemprop="addressCountry" />
                    <v-chip v-if="church?.city" color="primary" size="small" variant="outlined">
                      <span
                        itemprop="address"
                        itemscope
                        itemtype="https://schema.org/PostalAddress"
                      >
                        <span itemprop="addressLocality">{{ church?.city }}</span>
                      </span>
                    </v-chip>
                    <v-btn
                      v-if="church?.google_maps_link"
                      class="tw:text-sm"
                      color="primary"
                      :href="church?.google_maps_link"
                      itemprop="hasMap"
                      :ripple="false"
                      size="small"
                      slim
                      target="_blank"
                      variant="plain"
                    >
                      Get Directions
                      <v-icon end :icon="mdiDirections" size="large"></v-icon>
                    </v-btn>
                  </div>
                </template>
              </EntityDetails>
            </v-col>
          </v-row>
        </header>

        <!-- Current Schedule Period Alert -->
        <v-alert
          v-if="currentSchedulePeriods.length > 0"
          class="tw:mb-4 tw:mt-2"
          color="info"
          :icon="mdiCalendarMonth"
          variant="tonal"
        >
          <div class="tw:font-semibold">Current Special Schedule</div>
          <div v-for="period in currentSchedulePeriods" :key="period.id" class="tw:mt-1">
            <strong>{{ period.name }}</strong>
            <span class="tw:text-sm tw:ml-2">({{ period.formatted_date_range }})</span>
            <p v-if="period.description" class="tw:text-sm tw:mt-1 tw:mb-0">
              {{ period.description }}
            </p>
          </div>
        </v-alert>

        <!-- Schedule Content -->
        <section aria-label="Church Schedule">
          <v-card>
            <v-tabs v-model="activeTab" color="primary" grow>
              <v-tab value="regular">
                <v-icon :icon="mdiCalendarClock" start />
                Regular Schedule
              </v-tab>
              <v-tab v-if="hasSchedulePeriods" value="special">
                <v-icon :icon="mdiCalendarMonth" start />
                Special Schedules
              </v-tab>
            </v-tabs>

            <v-tabs-window v-model="activeTab">
              <!-- Regular Schedule Tab -->
              <v-tabs-window-item value="regular">
                <v-card-text>
                  <!-- Mass Schedule Section -->
                  <section aria-label="Mass Schedule" class="tw:mb-6">
                    <h2
                      class="tw:text-lg tw:font-semibold tw:mb-3 tw:flex tw:items-center tw:gap-2"
                    >
                      <v-icon color="primary" :icon="mdiCross" size="small" />
                      Mass Schedule
                    </h2>

                    <div v-if="!hasActivityType(regularSchedule?.mass)">
                      <v-alert
                        class="tw:mb-0"
                        color="info"
                        :icon="mdiInformationOutline"
                        variant="tonal"
                      >
                        <p class="tw:mb-2">
                          No Mass schedule has been recorded for this church yet.
                        </p>
                        <p class="tw:mb-0 tw:text-sm">
                          If you know the Mass times for this church, please help us by submitting
                          the schedule through our
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
                      <!-- Weekly Mass -->
                      <div v-if="Object.keys(regularSchedule?.mass?.weekly || {}).length > 0">
                        <v-table density="comfortable">
                          <thead>
                            <tr>
                              <th class="tw:font-bold tw:w-32">Day</th>
                              <th class="tw:font-bold">Times</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="day in daysOfWeek" :key="day">
                              <td class="tw:font-medium">{{ day }}</td>
                              <td>
                                <div
                                  v-if="regularSchedule?.mass?.weekly?.[day]"
                                  class="tw:flex tw:flex-wrap tw:gap-2"
                                >
                                  <v-chip
                                    v-for="activity in regularSchedule.mass.weekly[day]"
                                    :key="activity.id"
                                    color="primary"
                                    size="small"
                                    variant="tonal"
                                  >
                                    {{ formatActivityDescription(activity) }}
                                  </v-chip>
                                </div>
                                <span v-else class="tw:text-gray-400">-</span>
                              </td>
                            </tr>
                          </tbody>
                        </v-table>
                      </div>

                      <!-- Daily Mass -->
                      <div v-if="regularSchedule?.mass?.daily?.length > 0" class="tw:mt-4">
                        <h3 class="tw:font-medium tw:mb-2">Daily Mass</h3>
                        <div class="tw:flex tw:flex-wrap tw:gap-2">
                          <v-chip
                            v-for="activity in regularSchedule.mass.daily"
                            :key="activity.id"
                            color="success"
                            size="small"
                            variant="tonal"
                          >
                            {{ formatActivityDescription(activity) }}
                          </v-chip>
                        </div>
                      </div>

                      <!-- Monthly Mass -->
                      <div v-if="regularSchedule?.mass?.monthly?.length > 0" class="tw:mt-4">
                        <h3 class="tw:font-medium tw:mb-2">Monthly Mass</h3>
                        <v-list density="compact">
                          <v-list-item
                            v-for="monthlyGroup in regularSchedule.mass.monthly"
                            :key="monthlyGroup.label"
                          >
                            <v-list-item-title class="tw:font-medium">
                              {{ monthlyGroup.label }} of the Month
                            </v-list-item-title>
                            <v-list-item-subtitle>
                              <div class="tw:flex tw:flex-wrap tw:gap-2 tw:mt-1">
                                <v-chip
                                  v-for="activity in monthlyGroup.activities"
                                  :key="activity.id"
                                  class="tw:!font-bold"
                                  color="warning"
                                  size="small"
                                  variant="outlined"
                                >
                                  {{ formatActivityDescription(activity) }}
                                </v-chip>
                              </div>
                            </v-list-item-subtitle>
                          </v-list-item>
                        </v-list>
                      </div>

                      <!-- One-time Mass -->
                      <div v-if="regularSchedule?.mass?.one_time?.length > 0" class="tw:mt-4">
                        <h3 class="tw:font-medium tw:mb-2">Special/One-time Mass</h3>
                        <v-list density="compact">
                          <v-list-item
                            v-for="activity in regularSchedule.mass.one_time"
                            :key="activity.id"
                          >
                            <v-list-item-title>
                              {{ formatDate(activity.date) }}
                            </v-list-item-title>
                            <v-list-item-subtitle>
                              {{ formatActivityDescription(activity) }}
                            </v-list-item-subtitle>
                          </v-list-item>
                        </v-list>
                      </div>
                    </div>
                  </section>

                  <v-divider class="tw:my-4" />

                  <!-- Confession Schedule Section -->
                  <section aria-label="Confession Schedule" class="tw:mb-6">
                    <h2
                      class="tw:text-lg tw:font-semibold tw:mb-3 tw:flex tw:items-center tw:gap-2"
                    >
                      <v-icon color="secondary" :icon="mdiClock" size="small" />
                      Confession Schedule
                    </h2>

                    <div v-if="!hasActivityType(regularSchedule?.confession)">
                      <v-alert
                        class="tw:mb-0"
                        color="info"
                        :icon="mdiInformationOutline"
                        variant="tonal"
                      >
                        <p class="tw:mb-2">
                          No Confession schedule has been recorded for this church yet.
                        </p>
                        <p class="tw:mb-0 tw:text-sm">
                          If you know the Confession times for this church, please help us by
                          submitting the schedule through our
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
                      <!-- Weekly Confession -->
                      <div v-if="Object.keys(regularSchedule?.confession?.weekly || {}).length > 0">
                        <v-table density="comfortable">
                          <thead>
                            <tr>
                              <th class="tw:font-bold tw:w-32">Day</th>
                              <th class="tw:font-bold">Times</th>
                            </tr>
                          </thead>
                          <tbody>
                            <template v-for="day in daysOfWeek" :key="day">
                              <tr v-if="regularSchedule?.confession?.weekly?.[day]">
                                <td class="tw:font-medium">{{ day }}</td>
                                <td>
                                  <div class="tw:flex tw:flex-wrap tw:gap-2">
                                    <v-chip
                                      v-for="activity in regularSchedule.confession.weekly[day]"
                                      :key="activity.id"
                                      color="secondary"
                                      size="small"
                                      variant="tonal"
                                    >
                                      {{ formatActivityDescription(activity) }}
                                    </v-chip>
                                  </div>
                                </td>
                              </tr>
                            </template>
                          </tbody>
                        </v-table>
                      </div>

                      <!-- Daily Confession -->
                      <div v-if="regularSchedule?.confession?.daily?.length > 0" class="tw:mt-4">
                        <h3 class="tw:font-medium tw:mb-2">Daily Confession</h3>
                        <div class="tw:flex tw:flex-wrap tw:gap-2">
                          <v-chip
                            v-for="activity in regularSchedule.confession.daily"
                            :key="activity.id"
                            color="success"
                            size="small"
                            variant="tonal"
                          >
                            {{ formatActivityDescription(activity) }}
                          </v-chip>
                        </div>
                      </div>

                      <!-- Monthly Confession -->
                      <div v-if="regularSchedule?.confession?.monthly?.length > 0" class="tw:mt-4">
                        <h3 class="tw:font-medium tw:mb-2">Monthly Confession</h3>
                        <v-list density="compact">
                          <v-list-item
                            v-for="monthlyGroup in regularSchedule.confession.monthly"
                            :key="monthlyGroup.label"
                          >
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
                                  {{ formatActivityDescription(activity) }}
                                </v-chip>
                              </div>
                            </v-list-item-subtitle>
                          </v-list-item>
                        </v-list>
                      </div>

                      <!-- One-time Confession -->
                      <div v-if="regularSchedule?.confession?.one_time?.length > 0" class="tw:mt-4">
                        <h3 class="tw:font-medium tw:mb-2">Special/One-time Confession</h3>
                        <v-list density="compact">
                          <v-list-item
                            v-for="activity in regularSchedule.confession.one_time"
                            :key="activity.id"
                          >
                            <v-list-item-title>
                              {{ formatDate(activity.date) }}
                            </v-list-item-title>
                            <v-list-item-subtitle>
                              {{ formatActivityDescription(activity) }}
                            </v-list-item-subtitle>
                          </v-list-item>
                        </v-list>
                      </div>
                    </div>
                  </section>

                  <v-divider class="tw:my-4" />

                  <!-- Adoration Schedule Section -->
                  <section aria-label="Adoration Schedule">
                    <h2
                      class="tw:text-lg tw:font-semibold tw:mb-3 tw:flex tw:items-center tw:gap-2"
                    >
                      <v-icon color="info" :icon="mdiHandsPray" size="small" />
                      Adoration Schedule
                    </h2>

                    <div v-if="!hasActivityType(regularSchedule?.adoration)">
                      <v-alert
                        class="tw:mb-0"
                        color="info"
                        :icon="mdiInformationOutline"
                        variant="tonal"
                      >
                        <p class="tw:mb-2">
                          No Adoration schedule has been recorded for this church yet.
                        </p>
                        <p class="tw:mb-0 tw:text-sm">
                          If you know the Adoration times for this church, please help us by
                          submitting the schedule through our
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
                      <!-- Weekly Adoration -->
                      <div v-if="Object.keys(regularSchedule?.adoration?.weekly || {}).length > 0">
                        <v-table density="comfortable">
                          <thead>
                            <tr>
                              <th class="tw:font-bold tw:w-32">Day</th>
                              <th class="tw:font-bold">Times</th>
                            </tr>
                          </thead>
                          <tbody>
                            <template v-for="day in daysOfWeek" :key="day">
                              <tr v-if="regularSchedule?.adoration?.weekly?.[day]">
                                <td class="tw:font-medium">{{ day }}</td>
                                <td>
                                  <div class="tw:flex tw:flex-wrap tw:gap-2">
                                    <v-chip
                                      v-for="activity in regularSchedule.adoration.weekly[day]"
                                      :key="activity.id"
                                      color="info"
                                      size="small"
                                      variant="tonal"
                                    >
                                      {{ formatActivityDescription(activity) }}
                                    </v-chip>
                                  </div>
                                </td>
                              </tr>
                            </template>
                          </tbody>
                        </v-table>
                      </div>

                      <!-- Daily Adoration -->
                      <div v-if="regularSchedule?.adoration?.daily?.length > 0" class="tw:mt-4">
                        <h3 class="tw:font-medium tw:mb-2">Daily Adoration</h3>
                        <div class="tw:flex tw:flex-wrap tw:gap-2">
                          <v-chip
                            v-for="activity in regularSchedule.adoration.daily"
                            :key="activity.id"
                            color="success"
                            size="small"
                            variant="tonal"
                          >
                            {{ formatActivityDescription(activity) }}
                          </v-chip>
                        </div>
                      </div>

                      <!-- Monthly Adoration -->
                      <div v-if="regularSchedule?.adoration?.monthly?.length > 0" class="tw:mt-4">
                        <h3 class="tw:font-medium tw:mb-2">Monthly Adoration</h3>
                        <v-list density="compact">
                          <v-list-item
                            v-for="monthlyGroup in regularSchedule.adoration.monthly"
                            :key="monthlyGroup.label"
                          >
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
                                  {{ formatActivityDescription(activity) }}
                                </v-chip>
                              </div>
                            </v-list-item-subtitle>
                          </v-list-item>
                        </v-list>
                      </div>

                      <!-- One-time Adoration -->
                      <div v-if="regularSchedule?.adoration?.one_time?.length > 0" class="tw:mt-4">
                        <h3 class="tw:font-medium tw:mb-2">Special/One-time Adoration</h3>
                        <v-list density="compact">
                          <v-list-item
                            v-for="activity in regularSchedule.adoration.one_time"
                            :key="activity.id"
                          >
                            <v-list-item-title>
                              {{ formatDate(activity.date) }}
                            </v-list-item-title>
                            <v-list-item-subtitle>
                              {{ formatActivityDescription(activity) }}
                            </v-list-item-subtitle>
                          </v-list-item>
                        </v-list>
                      </div>
                    </div>
                  </section>
                </v-card-text>
              </v-tabs-window-item>

              <!-- Special Schedules Tab -->
              <v-tabs-window-item v-if="hasSchedulePeriods" value="special">
                <v-card-text>
                  <v-expansion-panels>
                    <v-expansion-panel
                      v-for="period in schedulePeriods.filter((p) => hasActivities(p.activities))"
                      :key="period.id"
                    >
                      <v-expansion-panel-title>
                        <div class="tw:flex tw:items-center tw:gap-2">
                          <v-chip
                            v-if="period.is_current"
                            color="success"
                            size="x-small"
                            variant="flat"
                          >
                            Current
                          </v-chip>
                          <span class="tw:font-semibold">{{ period.name }}</span>
                          <span class="tw:text-sm tw:text-gray-500">
                            ({{ period.formatted_date_range }})
                          </span>
                        </div>
                      </v-expansion-panel-title>
                      <v-expansion-panel-text>
                        <p v-if="period.description" class="tw:mb-4 tw:text-gray-600">
                          {{ period.description }}
                        </p>

                        <!-- Period Mass Schedule -->
                        <div v-if="hasActivityType(period.activities?.mass)" class="tw:mb-4">
                          <h3 class="tw:font-medium tw:mb-2">Mass Schedule</h3>
                          <div v-if="Object.keys(period.activities?.mass?.weekly || {}).length > 0">
                            <div
                              v-for="(activities, day) in period.activities.mass.weekly"
                              :key="day"
                              class="tw:mb-2"
                            >
                              <span class="tw:font-medium">{{ day }}:</span>
                              <span
                                v-for="activity in activities"
                                :key="activity.id"
                                class="tw:ml-2"
                              >
                                <v-chip color="primary" size="small" variant="tonal">
                                  {{ formatActivityDescription(activity) }}
                                </v-chip>
                              </span>
                            </div>
                          </div>
                          <div v-if="period.activities?.mass?.daily?.length > 0" class="tw:mt-2">
                            <span class="tw:font-medium">Daily:</span>
                            <v-chip
                              v-for="activity in period.activities.mass.daily"
                              :key="activity.id"
                              class="tw:ml-2"
                              color="success"
                              size="small"
                              variant="tonal"
                            >
                              {{ formatActivityDescription(activity) }}
                            </v-chip>
                          </div>
                          <div v-if="period.activities?.mass?.monthly?.length > 0" class="tw:mt-2">
                            <div
                              v-for="monthlyGroup in period.activities.mass.monthly"
                              :key="monthlyGroup.label"
                            >
                              <span class="tw:font-medium">{{ monthlyGroup.label }} of Month:</span>
                              <v-chip
                                v-for="activity in monthlyGroup.activities"
                                :key="activity.id"
                                class="tw:ml-2"
                                color="warning"
                                size="small"
                                variant="tonal"
                              >
                                {{ formatActivityDescription(activity) }}
                              </v-chip>
                            </div>
                          </div>
                        </div>

                        <!-- Period Confession Schedule -->
                        <div v-if="hasActivityType(period.activities?.confession)" class="tw:mb-4">
                          <h3 class="tw:font-medium tw:mb-2">Confession Schedule</h3>
                          <div
                            v-if="
                              Object.keys(period.activities?.confession?.weekly || {}).length > 0
                            "
                          >
                            <div
                              v-for="(activities, day) in period.activities.confession.weekly"
                              :key="day"
                              class="tw:mb-2"
                            >
                              <span class="tw:font-medium">{{ day }}:</span>
                              <span
                                v-for="activity in activities"
                                :key="activity.id"
                                class="tw:ml-2"
                              >
                                <v-chip color="secondary" size="small" variant="tonal">
                                  {{ formatActivityDescription(activity) }}
                                </v-chip>
                              </span>
                            </div>
                          </div>
                          <div
                            v-if="period.activities?.confession?.daily?.length > 0"
                            class="tw:mt-2"
                          >
                            <span class="tw:font-medium">Daily:</span>
                            <v-chip
                              v-for="activity in period.activities.confession.daily"
                              :key="activity.id"
                              class="tw:ml-2"
                              color="success"
                              size="small"
                              variant="tonal"
                            >
                              {{ formatActivityDescription(activity) }}
                            </v-chip>
                          </div>
                          <div
                            v-if="period.activities?.confession?.monthly?.length > 0"
                            class="tw:mt-2"
                          >
                            <div
                              v-for="monthlyGroup in period.activities.confession.monthly"
                              :key="monthlyGroup.label"
                            >
                              <span class="tw:font-medium">{{ monthlyGroup.label }} of Month:</span>
                              <v-chip
                                v-for="activity in monthlyGroup.activities"
                                :key="activity.id"
                                class="tw:ml-2"
                                color="warning"
                                size="small"
                                variant="tonal"
                              >
                                {{ formatActivityDescription(activity) }}
                              </v-chip>
                            </div>
                          </div>
                          <div
                            v-if="period.activities?.confession?.one_time?.length > 0"
                            class="tw:mt-2"
                          >
                            <div class="tw:font-medium tw:mb-1">Special/One-time:</div>
                            <div
                              v-for="activity in period.activities.confession.one_time"
                              :key="activity.id"
                              class="tw:mb-1"
                            >
                              <v-chip color="secondary" size="small" variant="outlined">
                                {{ formatDate(activity.date) }} -
                                {{ formatActivityDescription(activity) }}
                              </v-chip>
                            </div>
                          </div>
                        </div>

                        <!-- Period Adoration Schedule -->
                        <div v-if="hasActivityType(period.activities?.adoration)">
                          <h3 class="tw:font-medium tw:mb-2">Adoration Schedule</h3>
                          <div
                            v-if="
                              Object.keys(period.activities?.adoration?.weekly || {}).length > 0
                            "
                          >
                            <div
                              v-for="(activities, day) in period.activities.adoration.weekly"
                              :key="day"
                              class="tw:mb-2"
                            >
                              <span class="tw:font-medium">{{ day }}:</span>
                              <span
                                v-for="activity in activities"
                                :key="activity.id"
                                class="tw:ml-2"
                              >
                                <v-chip color="info" size="small" variant="tonal">
                                  {{ formatActivityDescription(activity) }}
                                </v-chip>
                              </span>
                            </div>
                          </div>
                          <div
                            v-if="period.activities?.adoration?.daily?.length > 0"
                            class="tw:mt-2"
                          >
                            <span class="tw:font-medium">Daily:</span>
                            <v-chip
                              v-for="activity in period.activities.adoration.daily"
                              :key="activity.id"
                              class="tw:ml-2"
                              color="success"
                              size="small"
                              variant="tonal"
                            >
                              {{ formatActivityDescription(activity) }}
                            </v-chip>
                          </div>
                          <div
                            v-if="period.activities?.adoration?.monthly?.length > 0"
                            class="tw:mt-2"
                          >
                            <div
                              v-for="monthlyGroup in period.activities.adoration.monthly"
                              :key="monthlyGroup.label"
                            >
                              <span class="tw:font-medium">{{ monthlyGroup.label }} of Month:</span>
                              <v-chip
                                v-for="activity in monthlyGroup.activities"
                                :key="activity.id"
                                class="tw:ml-2"
                                color="warning"
                                size="small"
                                variant="tonal"
                              >
                                {{ formatActivityDescription(activity) }}
                              </v-chip>
                            </div>
                          </div>
                          <div
                            v-if="period.activities?.adoration?.one_time?.length > 0"
                            class="tw:mt-2"
                          >
                            <div class="tw:font-medium tw:mb-1">Special/One-time:</div>
                            <div
                              v-for="activity in period.activities.adoration.one_time"
                              :key="activity.id"
                              class="tw:mb-1"
                            >
                              <v-chip color="info" size="small" variant="outlined">
                                {{ formatDate(activity.date) }} -
                                {{ formatActivityDescription(activity) }}
                              </v-chip>
                            </div>
                          </div>
                        </div>
                      </v-expansion-panel-text>
                    </v-expansion-panel>
                  </v-expansion-panels>
                </v-card-text>
              </v-tabs-window-item>
            </v-tabs-window>
          </v-card>
        </section>
      </article>
    </v-container>
  </AppUserLayout>
</template>
