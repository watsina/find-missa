<script setup>
  import DocTip from '../components/DocTip.vue';
  import DocSteps from '../components/DocSteps.vue';
  import DocRelated from '../components/DocRelated.vue';

  const createOneTimeSteps = [
    'Navigate to a church and go to the "One-Time Activities" tab.',
    'Click "Add One-Time Activity" to open the form.',
    'Select the activity type (Mass, Confession, or Adoration).',
    'Choose the specific date for this activity.',
    'Enter the start time and optionally an end time.',
    'Select the language if applicable.',
    'Add any notes about this special event.',
    'Click "Save" to create the one-time activity.',
  ];
</script>

<template>
  <div class="doc-section">
    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-800 tw:mb-4">One-Time Activities</h2>

    <p class="tw:text-gray-700 tw:mb-4">
      One-time activities are events that occur only once and don't repeat. Use these for special
      Masses, extra Confession times, or any activity that isn't part of the regular schedule.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      When to Use One-Time Activities
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">One-time activities are appropriate for:</p>

    <ul class="tw:list-disc tw:list-inside tw:text-gray-700 tw:space-y-2 tw:mb-4">
      <li><strong>Special event Masses</strong> - Parish anniversary, patron saint feast day</li>
      <li><strong>Visiting priest Masses</strong> - When a guest celebrant offers Mass</li>
      <li><strong>Penance services</strong> - Special communal reconciliation services</li>
      <li><strong>Extended Adoration</strong> - 40 Hours devotion, special holy hours</li>
      <li><strong>Memorial Masses</strong> - Funeral Masses, anniversary Masses</li>
    </ul>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Creating a One-Time Activity
    </h3>

    <DocSteps :steps="createOneTimeSteps" title="To create a one-time activity:" />

    <DocTip>
      One-time activities appear alongside regular schedules in the output. They don't replace or
      modify existing schedules - they're simply added to the list for that date.
    </DocTip>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      One-Time vs. Liturgical Schedules
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">Consider which approach fits your needs:</p>

    <v-card class="tw:mb-4 tw:p-4" variant="outlined">
      <v-row>
        <v-col cols="12" md="6">
          <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-2">Use One-Time Activities when:</h4>
          <ul class="tw:list-disc tw:list-inside tw:text-sm tw:text-gray-700 tw:space-y-1">
            <li>The event happens only once</li>
            <li>It's not tied to a holiday</li>
            <li>You know the exact date</li>
            <li>It's a unique, non-recurring event</li>
          </ul>
        </v-col>
        <v-col cols="12" md="6">
          <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-2">Use Liturgical Schedules when:</h4>
          <ul class="tw:list-disc tw:list-inside tw:text-sm tw:text-gray-700 tw:space-y-1">
            <li>The event recurs annually</li>
            <li>It's tied to a holiday (Christmas, Easter)</li>
            <li>The date may change (moveable feasts)</li>
            <li>You want to set it once and have it repeat</li>
          </ul>
        </v-col>
      </v-row>
    </v-card>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Managing One-Time Activities
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">
      One-time activities appear in a table on the church detail page. You can edit or delete them
      as needed. Past one-time activities remain in the system for historical reference but won't
      appear in future schedule outputs.
    </p>

    <DocRelated :sections="['liturgical-schedules', 'regular-schedules', 'churches']" />
  </div>
</template>
