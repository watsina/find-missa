<script setup>
  import { ref, computed, watch, onMounted, defineAsyncComponent } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import { mdiMagnify, mdiBookOpenPageVariant, mdiChevronLeft } from '@mdi/js';
  import AppLayout from '@/layouts/AppLayout.vue';
  import { useDocumentation } from '@/composables/useDocumentation';
  import useAuthStore from '@/stores/useAuthStore';
  import DocNavigation from './components/DocNavigation.vue';

  // Section components - lazy loaded
  const sectionComponents = {
    'getting-started': () => import('./sections/GettingStarted.vue'),
    dioceses: () => import('./sections/Dioceses.vue'),
    parishes: () => import('./sections/Parishes.vue'),
    'sub-parishes': () => import('./sections/SubParishes.vue'),
    churches: () => import('./sections/Churches.vue'),
    hierarchy: () => import('./sections/HierarchyOverview.vue'),
    'regular-schedules': () => import('./sections/RegularSchedules.vue'),
    'day-variations': () => import('./sections/DayVariations.vue'),
    'one-time-activities': () => import('./sections/OneTimeActivities.vue'),
    'schedule-periods': () => import('./sections/SchedulePeriods.vue'),
    holidays: () => import('./sections/Holidays.vue'),
    'liturgical-schedules': () => import('./sections/LiturgicalSchedules.vue'),
    'schedule-exceptions': () => import('./sections/ScheduleExceptions.vue'),
    'schedule-rules': () => import('./sections/ScheduleRules.vue'),
    'global-exceptions': () => import('./sections/GlobalExceptions.vue'),
    'adoration-chapels': () => import('./sections/AdorationChapels.vue'),
    priests: () => import('./sections/Priests.vue'),
    'team-management': () => import('./sections/TeamManagement.vue'),
    'data-management': () => import('./sections/DataManagement.vue'),
    faq: () => import('./sections/FAQ.vue'),
  };

  const props = defineProps({
    section: {
      type: String,
      default: null,
    },
  });

  const route = useRoute();
  const router = useRouter();
  const authStore = useAuthStore();
  const { searchQuery, searchResults, getAllSections, getSection, clearSearch } =
    useDocumentation();

  const isAdmin = computed(() => authStore.user?.is_admin);
  const sections = computed(() => getAllSections(isAdmin.value));
  const mobileNavOpen = ref(false);

  const activeSection = computed(() => {
    return props.section || 'getting-started';
  });

  const currentSectionData = computed(() => {
    return getSection(activeSection.value);
  });

  const CurrentSectionComponent = computed(() => {
    const loader = sectionComponents[activeSection.value];
    if (loader) {
      return defineAsyncComponent(loader);
    }
    return null;
  });

  function navigateToSection(sectionId) {
    router.push({ name: 'docs.section', params: { section: sectionId } });
    clearSearch();
    mobileNavOpen.value = false;
  }

  function handleSearchSelect(sectionId) {
    navigateToSection(sectionId);
  }

  // Watch for route changes to update active section
  watch(
    () => route.params.section,
    (newSection) => {
      if (!newSection && route.name === 'docs.index') {
        // Default to getting-started if no section specified
        router.replace({ name: 'docs.section', params: { section: 'getting-started' } });
      }
    },
    { immediate: true },
  );

  onMounted(() => {
    // If on index without section, redirect to getting-started
    if (route.name === 'docs.index' && !props.section) {
      router.replace({ name: 'docs.section', params: { section: 'getting-started' } });
    }
  });
</script>

<template>
  <AppLayout>
    <template #head>
      <div class="tw:flex tw:items-center tw:gap-2">
        <v-icon :icon="mdiBookOpenPageVariant" size="small" />
        <h2>Documentation</h2>
      </div>
    </template>

    <v-container class="tw:py-4" fluid>
      <v-row>
        <!-- Mobile navigation toggle -->
        <v-col class="tw:md:hidden" cols="12">
          <v-btn
            block
            :prepend-icon="mdiChevronLeft"
            variant="outlined"
            @click="mobileNavOpen = !mobileNavOpen"
          >
            {{ currentSectionData?.title || 'Navigation' }}
          </v-btn>
        </v-col>

        <!-- Sidebar Navigation -->
        <v-col :class="{ 'tw:hidden tw:md:block': !mobileNavOpen }" cols="12" lg="2" md="3">
          <v-card class="tw:sticky tw:top-4" variant="flat">
            <!-- Search -->
            <v-text-field
              v-model="searchQuery"
              class="tw:mb-4"
              clearable
              density="compact"
              hide-details
              label="Search documentation..."
              :prepend-inner-icon="mdiMagnify"
              variant="outlined"
            />

            <!-- Search Results -->
            <v-list v-if="searchResults.length > 0" class="tw:mb-4" density="compact">
              <v-list-subheader>Search Results</v-list-subheader>
              <v-list-item
                v-for="result in searchResults"
                :key="result.id"
                :prepend-icon="result.icon"
                :subtitle="result.description"
                :title="result.title"
                @click="handleSearchSelect(result.id)"
              />
            </v-list>

            <!-- Navigation Tree -->
            <DocNavigation
              v-show="!searchQuery"
              :active-section="activeSection"
              :sections="sections"
              @select="navigateToSection"
            />
          </v-card>
        </v-col>

        <!-- Main Content -->
        <v-col cols="12" lg="10" md="9">
          <v-card class="tw:p-4 tw:md:p-6" variant="flat">
            <!-- Section Header -->
            <div v-if="currentSectionData" class="tw:mb-6 tw:px-2 tw:pt-2">
              <div class="tw:flex tw:items-center tw:gap-3 tw:mb-2">
                <v-icon color="primary" :icon="currentSectionData.icon" size="large" />
                <h1 class="tw:text-2xl tw:font-bold tw:text-gray-900">
                  {{ currentSectionData.title }}
                </h1>
              </div>
              <p class="tw:text-gray-600">{{ currentSectionData.description }}</p>
            </div>

            <v-divider class="tw:mb-6" />

            <!-- Dynamic Section Content -->
            <div class="tw:px-2">
              <Suspense>
                <template #default>
                  <component :is="CurrentSectionComponent" v-if="CurrentSectionComponent" />
                  <v-alert v-else type="info" variant="tonal">
                    Documentation section not found. Please select a topic from the navigation.
                  </v-alert>
                </template>
                <template #fallback>
                  <div class="tw:flex tw:justify-center tw:py-8">
                    <v-progress-circular color="primary" indeterminate />
                  </div>
                </template>
              </Suspense>
            </div>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </AppLayout>
</template>
