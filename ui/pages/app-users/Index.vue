<script setup>
  import DateInput from '@/components/DateInput.vue';
  import NavLink from '@/components/NavLink.vue';
  import AppUserLayout from '@/layouts/AppUserLayout.vue';
  import TimeInput from '@/components/TimeInput.vue';
  import ShareButton from '@/components/ShareButton.vue';
  import { http, useForm } from '@/plugins';
  import { notify, useDayjs } from '@/utils';
  import { ref, onMounted, computed, watch } from 'vue';
  import { useDate, useDisplay } from 'vuetify';
  import { debounce } from 'lodash';
  import { useSeoMeta, useHead } from '@unhead/vue';
  import {
    mdiMagnify,
    mdiShareAllOutline,
    mdiChurch,
    mdiCalendarStar,
    mdiHandHeart,
    mdiArrowRight,
  } from '@mdi/js';
  import { useRoute, useRouter } from 'vue-router';

  const title = 'Find Catholic Mass Times in Uganda | Mass, Confession & Adoration';
  const description =
    'Find Catholic Mass times, Confession schedules, and Adoration hours at parishes across Uganda. Search by location, time, or church name. Updated daily with accurate schedules from dioceses nationwide.';
  const keywords =
    'Catholic Mass Uganda, Mass times Kampala, find Mass near me Uganda, Catholic church Uganda, Confession times Uganda, Adoration Uganda, Sunday Mass Uganda, daily Mass Uganda, Catholic parishes Uganda, Holy Mass schedule, Kampala diocese, Jinja diocese, Gulu archdiocese, Mbarara archdiocese, Catholic sacraments Uganda';

  useSeoMeta({
    title: title,
    description: description,
    keywords: keywords,
    ogType: 'website',
    ogTitle: title,
    ogDescription: description,
    ogUrl: 'https://findmissa.com',
    ogSiteName: 'Find Missa',
    ogLocale: 'en_UG',
    twitterCard: 'summary_large_image',
    twitterTitle: title,
    twitterDescription: description,
    robots: 'index, follow',
    author: 'Watsina Technologies',
  });

  useHead({
    link: [{ rel: 'canonical', href: 'https://findmissa.com' }],
    script: [
      {
        type: 'application/ld+json',
        innerHTML: JSON.stringify({
          '@context': 'https://schema.org',
          '@type': 'WebApplication',
          name: 'Find Missa',
          description: description,
          url: 'https://findmissa.com',
          applicationCategory: 'ReligiousOrganization',
          operatingSystem: 'Any',
          browserRequirements: 'Requires JavaScript',
          offers: {
            '@type': 'Offer',
            price: '0',
            priceCurrency: 'USD',
          },
          creator: {
            '@type': 'Organization',
            name: 'Watsina Technologies',
            url: 'https://watsina.io',
          },
          provider: {
            '@type': 'Organization',
            name: 'Find Missa',
            url: 'https://findmissa.com',
          },
          potentialAction: {
            '@type': 'SearchAction',
            target: {
              '@type': 'EntryPoint',
              urlTemplate: 'https://findmissa.com?location={location}&type={type}',
            },
            'query-input': 'required name=location',
          },
        }),
      },
    ],
  });

  const { smAndDown } = useDisplay();

  const results = ref(null);
  const dateUsed = ref(null);
  const holidayInfo = ref(null);
  const parser = useDate();
  const currentDate = new Date().toLocaleDateString('en-US');
  const date = ref(parser.date(currentDate));
  const startTime = parser.format(new Date(), 'fullTime24h')?.substring(0, 5);

  const router = useRouter();
  const route = useRoute();
  function convertTo12Hour(time24) {
    // Split the time string into hours and minutes
    const [hours, minutes] = time24.split(':');

    // Convert hours to number
    const hoursNum = parseInt(hours, 10);

    // Determine AM or PM
    const period = hoursNum >= 12 ? 'PM' : 'AM';

    // Convert to 12-hour format
    let hours12 = hoursNum % 12;
    hours12 = hours12 === 0 ? 12 : hours12; // Handle midnight (0 becomes 12)

    // Return formatted string
    return `${hours12}:${minutes} ${period}`;
  }

  const searchedQuery = ref({});

  const shareTitle = computed(() => `Share ${searchedQuery.value?.type || 'Mass'} Times`);

  const shareDescription = computed(() => {
    const query = searchedQuery.value;

    let description = `Checkout ${query.type || 'mass'} times on ${parser.format(dateUsed.value, 'fullDateWithWeekday')}`;

    if (query?.location) {
      description += ` in ${query.location}`;
    } else if (query?.name) {
      description += ` in ${query.name}`;
    }

    if (query?.fromTime) {
      description += ` from ${convertTo12Hour(query.fromTime)} onwards.`;
    }

    return description;
  });

  const queryParams = computed(() => {
    const query = route.query;
    // validate that pattern matches HH:MM
    const timeReg = /^([0-1]?[0-9]|2[0-3]):([0-5][0-9])$/;
    if (!query?.fromTime || !timeReg.test(query?.fromTime)) {
      query.fromTime = startTime;
    }
    // validate date
    if (!query?.date || useDayjs(query.date).isValid()) {
      query.date = parser.date(currentDate);
    }
    //validate types
    if (!['mass', 'confession', 'adoration'].includes(query?.type)) {
      query.type = 'mass';
    }

    return query;
  });

  const form = useForm({
    start_time: queryParams.value?.fromTime || startTime,
    church_name: queryParams.value?.name || '',
    district: queryParams.value?.location || '',
    type: queryParams.value?.type || 'mass',
    date: queryParams.value?.date || parser.date(currentDate),
  });

  const showDate = computed(() => {
    const date = smAndDown.value
      ? parser.format(dateUsed.value, 'fullDate')
      : parser.format(dateUsed.value, 'fullDateWithWeekday');

    return useDayjs().isSame(useDayjs(dateUsed.value).format('YYYY-MM-DD'), 'day') ? 'Today' : date;
  });

  // Liturgical color mapping to Vuetify semantic colors for readable text contrast
  const liturgicalColorMap = {
    white: 'warning',
    red: 'error',
    green: 'success',
    violet: 'info',
    purple: 'info',
    rose: 'pink-lighten-1',
    gold: 'warning',
    black: 'grey-darken-3',
  };

  function getLiturgicalColor(color) {
    const normalizedColor = color?.toLowerCase() || 'white';
    return liturgicalColorMap[normalizedColor] || liturgicalColorMap.white;
  }

  async function fetchHolidayInfo(dateString) {
    try {
      const response = await http.get('/api/v1/holiday-info', {
        params: { date: dateString },
      });
      holidayInfo.value = response.data;
    } catch {
      // Silently fail - holiday info is optional enhancement
      holidayInfo.value = null;
    }
  }

  watch(
    () => form.type,
    debounce(() => {
      getResults();
    }, 300),
  );

  function getResults() {
    form.date = useDayjs(date.value).format('YYYY-MM-DD');
    form.get('/api/v1/search-v2', {
      onSuccess: (data) => {
        results.value = data || [];
        dateUsed.value = form.date;

        searchedQuery.value = {
          date: form.date,
          fromTime: form.start_time,
          location: form.district,
          type: form.type,
          name: form.church_name,
        };

        router.push({
          query: searchedQuery.value,
        });

        // Fetch holiday info for the searched date
        fetchHolidayInfo(form.date);
      },
      onError: () => notify.error('Something went wrong...'),
    });
  }

  onMounted(() => {
    getResults();
  });
</script>

<template>
  <AppUserLayout>
    <v-container>
      <v-row class="py-4">
        <v-col cols="12">
          <v-alert
            class="contribution-alert"
            color="primary"
            :icon="mdiHandHeart"
            prominent
            variant="tonal"
          >
            <template #title>
              <span class="tw:font-bold">Help Us Grow This Directory!</span>
            </template>
            <p class="tw:mb-3">
              Know a parish schedule that's missing or outdated? Your contribution helps thousands
              of Catholics find Mass across Uganda.
            </p>
            <v-btn
              :append-icon="mdiArrowRight"
              :block="smAndDown"
              color="primary"
              href="https://forms.gle/kGiX1A36dW3is1BJA"
              rel="noopener"
              :size="smAndDown ? 'small' : 'default'"
              target="_blank"
              variant="elevated"
            >
              Submit Church Schedule
            </v-btn>
          </v-alert>
        </v-col>

        <v-col class="tw:flex tw:justify-around" cols="12">
          <v-btn-toggle v-model="form.type" color="primary" group mandatory rounded>
            <v-btn rounded="0" value="mass">Mass</v-btn>
            <v-btn rounded="0" value="confession">Confession</v-btn>
            <v-btn rounded="0" value="adoration">Adoration</v-btn>
          </v-btn-toggle>
        </v-col>
        <v-col cols="12" md="3">
          <DateInput
            v-model="date"
            :hide-actions="false"
            label="Date"
            :min="currentDate"
            variant="solo"
          ></DateInput>
        </v-col>
        <v-col cols="12" md="2">
          <TimeInput
            v-model="form.start_time"
            :error="form.errors.start_time"
            label="From Time (24h)"
            required
            title="Select Mass Time"
            variant="solo"
          />
        </v-col>
        <v-col class="tw:flex tw:items-end" cols="12" md="3">
          <v-text-field
            v-model="form.district"
            clearable
            label="District/Town"
            variant="solo"
          ></v-text-field>
        </v-col>
        <v-col class="tw:flex tw:items-end tw:justify-between" cols="12" md="4">
          <v-text-field
            v-model="form.church_name"
            class="tw:mr-3"
            clearable
            label="Parish/Church Name"
            variant="solo"
          ></v-text-field>
          <v-btn
            v-if="smAndDown"
            color="primary"
            :icon="mdiMagnify"
            variant="elevated"
            @click="getResults"
          />

          <v-btn
            v-else
            color="primary"
            :prepend-icon="mdiMagnify"
            variant="elevated"
            @click="getResults"
          >
            Search
          </v-btn>
        </v-col>
        <!-- Holiday Banner -->
        <v-col v-if="holidayInfo?.is_holiday && holidayInfo?.holidays?.length" cols="12">
          <v-alert
            v-for="holidayInstance in holidayInfo.holidays"
            :key="holidayInstance.id"
            class="tw:mb-2"
            :color="getLiturgicalColor(holidayInstance.holiday?.liturgical_color)"
            prominent
            :title="holidayInstance.holiday?.name"
            variant="tonal"
          >
            <template #prepend>
              <v-icon
                :color="getLiturgicalColor(holidayInstance.holiday?.liturgical_color)"
                :icon="mdiCalendarStar"
                size="large"
              />
            </template>
            <template #text>
              <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-2">
                <v-chip
                  v-if="holidayInstance.holiday?.is_holy_day_of_obligation"
                  :color="getLiturgicalColor(holidayInstance.holiday?.liturgical_color)"
                  :prepend-icon="mdiChurch"
                  size="small"
                  variant="elevated"
                >
                  Holy Day of Obligation
                </v-chip>
                <v-chip
                  v-if="holidayInstance.holiday?.liturgical_color"
                  :color="getLiturgicalColor(holidayInstance.holiday?.liturgical_color)"
                  size="small"
                  variant="flat"
                >
                  {{ holidayInstance.holiday?.liturgical_color }}
                </v-chip>
              </div>
              <p v-if="holidayInstance.holiday?.description" class="tw:mt-2 tw:mb-0">
                {{ holidayInstance.holiday?.description }}
              </p>
            </template>
          </v-alert>
        </v-col>
        <share-button :description="shareDescription" :title="shareTitle" />
        <template v-if="form.processing">
          <v-col cols="12">
            <v-skeleton-loader type="card"></v-skeleton-loader>
          </v-col>
        </template>
        <v-col v-else cols="12">
          <v-sheet>
            <v-divider opacity="10"></v-divider>
            <v-card-title class="tw:flex! tw:justify-between">
              <span :class="{ 'tw:text-base': smAndDown, 'tw:text-md': !smAndDown }">
                {{ showDate }}
              </span>
              <span class="tw:text-base">
                {{ `${!smAndDown ? 'Results:' : ''} ${results?.data?.length || 0}` }}
              </span>
            </v-card-title>
            <v-divider opacity="10"></v-divider>
            <v-card
              v-if="!results?.data || !results?.data?.length"
              class="tw:flex tw:justify-around"
              height="150"
            >
              <v-card-title class="tw:mx-auto tw:p-12 tw:my-auto">
                No {{ form.type || 'activities' }} found for the
                {{
                  form.district || form.church_name ? ' selected search query.' : ' selected time. '
                }}
              </v-card-title>
            </v-card>
            <v-timeline v-else class="tw:md:ml-4" density="comfortable" side="end">
              <v-timeline-item
                v-for="(activity, index) in results?.data"
                :key="`${activity?.church_id}-${activity?.start_time}-${index}`"
                dot-color="primary"
                size="small"
              >
                <div class="tw:flex">
                  <strong class="tw:me-4!">
                    {{
                      activity?.end_time
                        ? `${activity?.start_time} - ${activity?.end_time}`
                        : `${activity?.start_time}`
                    }}
                  </strong>
                  <div>
                    <strong class="tw:capitalize">
                      {{
                        activity?.language
                          ? `${activity?.activity_type_description} (${activity?.language})`
                          : activity?.activity_type_description
                      }}
                    </strong>
                    <v-chip
                      v-if="activity?.holiday_name"
                      class="tw:ml-2"
                      color="purple"
                      size="x-small"
                      variant="tonal"
                    >
                      {{ activity.holiday_name }}
                    </v-chip>
                    <div>
                      <NavLink
                        class="tw:text-indigo-600"
                        :href="{
                          name: 'church.details',
                          params: { churchId: activity?.church?.code_name },
                        }"
                      >
                        {{
                          `${activity?.church?.name} - ${activity?.church?.district} (${activity?.church?.city})`
                        }}
                        <v-icon end :icon="mdiShareAllOutline"></v-icon>
                      </NavLink>
                    </div>
                  </div>
                </div>
              </v-timeline-item>
            </v-timeline>
          </v-sheet>
        </v-col>
      </v-row>
    </v-container>
  </AppUserLayout>
</template>
