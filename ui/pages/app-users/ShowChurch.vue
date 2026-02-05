<script setup>
  import { computed, onMounted, ref } from 'vue';
  import { useHead, useSeoMeta } from '@unhead/vue';
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
    mdiPhoneClassic,
    mdiTownHall,
    mdiWeb,
  } from '@mdi/js';
  import { http } from '@/plugins';
  import { notify } from '@/utils';
  import { useActivitySchedule } from '@/composables';
  import AppUserLayout from '@/layouts/AppUserLayout';
  import EntityDetails from '@/components/EntityDetails.vue';
  import ActivityScheduleSection from '@/components/ActivityScheduleSection.vue';
  import SchedulePeriodActivities from '@/components/SchedulePeriodActivities.vue';
  import OneTimeActivitySection from '@/components/OneTimeActivitySection.vue';
  import LiturgicalScheduleSection from '@/components/LiturgicalScheduleSection.vue';

  const props = defineProps({
    churchId: { type: [String, Number], required: true },
  });

  const { hasActivities } = useActivitySchedule();

  const church = ref(null);
  const regularSchedule = ref(null);
  const schedulePeriods = ref([]);
  const oneTimeActivities = ref(null);
  const liturgicalSchedules = ref([]);
  const isFetching = ref(false);
  const activeTab = ref('regular');

  // Google Form URL for submitting schedule updates
  const googleFormUrl = 'https://forms.gle/kGiX1A36dW3is1BJA';

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

  // Computed: Filter periods with activities
  const periodsWithActivities = computed(() =>
    schedulePeriods.value.filter((p) => hasActivities(p.activities)),
  );

  // Computed: Check if there are any one-time activities
  const hasOneTimeActivities = computed(() => {
    const a = oneTimeActivities.value;
    return (a?.mass?.length || 0) + (a?.confession?.length || 0) + (a?.adoration?.length || 0) > 0;
  });

  // Computed: Check if there are any liturgical schedules
  const hasLiturgicalSchedules = computed(() => liturgicalSchedules.value.length > 0);

  // Computed: Check if special schedules tab should show
  const hasSpecialSchedules = computed(
    () => hasSchedulePeriods.value || hasOneTimeActivities.value || hasLiturgicalSchedules.value,
  );

  const fetchChurchDetails = () => {
    if (!props.churchId) return;
    isFetching.value = true;
    http
      .get(`/api/v1/church-details/${props.churchId}`)
      .then(({ data }) => {
        church.value = data?.data?.details || {};
        regularSchedule.value = data?.data?.regular_schedule || null;
        schedulePeriods.value = data?.data?.schedule_periods || [];
        oneTimeActivities.value = data?.data?.one_time_activities || null;
        liturgicalSchedules.value = data?.data?.liturgical_schedules || [];
      })
      .catch(() => notify.error('Something went wrong fetching the church details.'))
      .finally(() => (isFetching.value = false));
  };

  onMounted(() => {
    fetchChurchDetails();
  });
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
          class="tw:mb-2 tw:mt-2"
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
          <v-card class="tw:mt-2">
            <v-tabs v-model="activeTab" color="primary" grow>
              <v-tab value="regular">
                <v-icon :icon="mdiCalendarClock" start />
                Regular Schedule
              </v-tab>
              <v-tab v-if="hasSpecialSchedules" value="special">
                <v-icon :icon="mdiCalendarMonth" start />
                Special Schedules
              </v-tab>
            </v-tabs>

            <v-tabs-window v-model="activeTab">
              <!-- Regular Schedule Tab -->
              <v-tabs-window-item value="regular">
                <v-card-text>
                  <!-- Mass Schedule -->
                  <ActivityScheduleSection
                    activity-name="Mass"
                    color="primary"
                    :google-form-url="googleFormUrl"
                    :icon="mdiCross"
                    :schedule="regularSchedule?.mass"
                    :show-all-days="true"
                    title="Mass Schedule"
                  />

                  <v-divider class="tw:my-4" />

                  <!-- Confession Schedule -->
                  <ActivityScheduleSection
                    activity-name="Confession"
                    color="purple"
                    :google-form-url="googleFormUrl"
                    :icon="mdiClock"
                    :schedule="regularSchedule?.confession"
                    :show-all-days="false"
                    title="Confession Schedule"
                  />

                  <v-divider class="tw:my-4" />

                  <!-- Adoration Schedule -->
                  <ActivityScheduleSection
                    activity-name="Adoration"
                    color="info"
                    :google-form-url="googleFormUrl"
                    :icon="mdiHandsPray"
                    :schedule="regularSchedule?.adoration"
                    :show-all-days="false"
                    :show-end-time="true"
                    title="Adoration Schedule"
                  />
                </v-card-text>
              </v-tabs-window-item>

              <!-- Special Schedules Tab -->
              <v-tabs-window-item v-if="hasSpecialSchedules" value="special">
                <v-card-text>
                  <!-- Schedule Periods Section -->
                  <section v-if="hasSchedulePeriods" class="tw:mb-6">
                    <h2 class="tw:text-lg tw:font-semibold tw:mb-3">Liturgical Seasons</h2>
                    <v-expansion-panels>
                      <v-expansion-panel v-for="period in periodsWithActivities" :key="period.id">
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
                          <SchedulePeriodActivities
                            activity-name="Mass"
                            color="primary"
                            :schedule="period.activities?.mass"
                            title="Mass Schedule"
                          />

                          <!-- Period Confession Schedule -->
                          <SchedulePeriodActivities
                            activity-name="Confession"
                            color="purple"
                            :schedule="period.activities?.confession"
                            title="Confession Schedule"
                          />

                          <!-- Period Adoration Schedule -->
                          <SchedulePeriodActivities
                            activity-name="Adoration"
                            color="info"
                            :schedule="period.activities?.adoration"
                            :show-end-time="true"
                            title="Adoration Schedule"
                          />
                        </v-expansion-panel-text>
                      </v-expansion-panel>
                    </v-expansion-panels>
                  </section>

                  <v-divider v-if="hasSchedulePeriods && hasOneTimeActivities" class="tw:my-4" />

                  <!-- One-time Activities Section -->
                  <OneTimeActivitySection
                    v-if="hasOneTimeActivities"
                    :activities="oneTimeActivities"
                  />

                  <v-divider
                    v-if="(hasSchedulePeriods || hasOneTimeActivities) && hasLiturgicalSchedules"
                    class="tw:my-4"
                  />

                  <!-- Liturgical Schedules Section -->
                  <LiturgicalScheduleSection
                    v-if="hasLiturgicalSchedules"
                    :schedules="liturgicalSchedules"
                  />
                </v-card-text>
              </v-tabs-window-item>
            </v-tabs-window>
          </v-card>
        </section>
      </article>
    </v-container>
  </AppUserLayout>
</template>
