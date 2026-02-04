<script setup>
  import { ref, computed, watch } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import {
    mdiClose,
    mdiMagnify,
    mdiBookOpenPageVariant,
    mdiArrowRight,
    mdiRocketLaunchOutline,
    mdiFrequentlyAskedQuestions,
  } from '@mdi/js';
  import { useDocumentation } from '@/composables/useDocumentation';

  const modelValue = defineModel({ type: Boolean, default: false });

  const route = useRoute();
  const router = useRouter();
  const { searchQuery, searchResults, getSectionsForRoute, clearSearch } = useDocumentation();

  const localSearchQuery = ref('');

  // Get sections related to the current route
  const contextualSections = computed(() => {
    const routeName = route.name;
    if (!routeName) return [];
    return getSectionsForRoute(routeName);
  });

  // Sync local search with composable search
  watch(localSearchQuery, (newValue) => {
    searchQuery.value = newValue;
  });

  function closeDrawer() {
    modelValue.value = false;
    localSearchQuery.value = '';
    clearSearch();
  }

  function navigateToSection(sectionId) {
    router.push({ name: 'docs.section', params: { section: sectionId } });
    closeDrawer();
  }

  function navigateToFullDocs() {
    router.push({ name: 'docs.index' });
    closeDrawer();
  }
</script>

<template>
  <v-navigation-drawer
    v-model="modelValue"
    class="help-drawer"
    location="right"
    temporary
    width="360"
  >
    <!-- Header -->
    <v-toolbar color="primary" density="compact">
      <v-icon class="tw:ml-4" :icon="mdiBookOpenPageVariant" />
      <v-toolbar-title>Help</v-toolbar-title>
      <v-btn :icon="mdiClose" variant="text" @click="closeDrawer" />
    </v-toolbar>

    <div class="tw:p-4">
      <!-- Search -->
      <v-text-field
        v-model="localSearchQuery"
        class="tw:mb-4"
        clearable
        density="compact"
        hide-details
        label="Search documentation..."
        :prepend-inner-icon="mdiMagnify"
        variant="outlined"
      />

      <!-- Search Results -->
      <div v-if="searchResults.length > 0" class="tw:mb-4">
        <p class="tw:text-xs tw:font-semibold tw:text-gray-500 tw:uppercase tw:mb-2">
          Search Results
        </p>
        <v-list class="tw:bg-transparent" density="compact">
          <v-list-item
            v-for="result in searchResults"
            :key="result.id"
            :prepend-icon="result.icon"
            rounded="lg"
            :subtitle="result.description"
            :title="result.title"
            @click="navigateToSection(result.id)"
          >
            <template #append>
              <v-icon :icon="mdiArrowRight" size="small" />
            </template>
          </v-list-item>
        </v-list>
      </div>

      <!-- Contextual Help -->
      <div v-if="contextualSections.length > 0 && !localSearchQuery" class="tw:mb-4">
        <p class="tw:text-xs tw:font-semibold tw:text-gray-500 tw:uppercase tw:mb-2">
          Related to this page
        </p>
        <v-list class="tw:bg-transparent" density="compact">
          <v-list-item
            v-for="section in contextualSections"
            :key="section.id"
            :prepend-icon="section.icon"
            rounded="lg"
            :subtitle="section.description"
            :title="section.title"
            @click="navigateToSection(section.id)"
          >
            <template #append>
              <v-icon :icon="mdiArrowRight" size="small" />
            </template>
          </v-list-item>
        </v-list>
      </div>

      <!-- Quick Links -->
      <div v-if="!localSearchQuery" class="tw:mb-4">
        <p class="tw:text-xs tw:font-semibold tw:text-gray-500 tw:uppercase tw:mb-2">Quick Links</p>
        <v-list class="tw:bg-transparent" density="compact">
          <v-list-item
            :prepend-icon="mdiRocketLaunchOutline"
            rounded="lg"
            subtitle="Learn the basics"
            title="Getting Started"
            @click="navigateToSection('getting-started')"
          >
            <template #append>
              <v-icon :icon="mdiArrowRight" size="small" />
            </template>
          </v-list-item>
          <v-list-item
            :prepend-icon="mdiFrequentlyAskedQuestions"
            rounded="lg"
            subtitle="Common questions"
            title="FAQ & Troubleshooting"
            @click="navigateToSection('faq')"
          >
            <template #append>
              <v-icon :icon="mdiArrowRight" size="small" />
            </template>
          </v-list-item>
        </v-list>
      </div>

      <!-- View All Documentation Button -->
      <v-btn
        block
        color="primary"
        :prepend-icon="mdiBookOpenPageVariant"
        variant="tonal"
        @click="navigateToFullDocs"
      >
        View All Documentation
      </v-btn>
    </div>
  </v-navigation-drawer>
</template>
