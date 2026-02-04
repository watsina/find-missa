<script setup>
  import DocTip from '../components/DocTip.vue';
  import DocWarning from '../components/DocWarning.vue';
  import DocSteps from '../components/DocSteps.vue';
  import DocRelated from '../components/DocRelated.vue';

  const createVariationSteps = [
    'Open a church and go to the "Regular Schedules" tab.',
    'Expand the schedule you want to add a variation to.',
    'Click "Add Variation" at the bottom of the expanded section.',
    'Select which day of the week this variation applies to.',
    'Choose whether to skip this day entirely or modify the time/language.',
    'If modifying, enter the new time and/or language.',
    'Click "Save" to apply the variation.',
  ];
</script>

<template>
  <div class="doc-section">
    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-800 tw:mb-4">Day Variations</h2>

    <p class="tw:text-gray-700 tw:mb-4">
      Day variations allow you to modify a regular schedule for specific days of the week. For
      example, if a daily Mass is normally at 8:00 AM but is at 9:00 AM on Fridays, you can create a
      day variation instead of creating separate schedules.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      When to Use Day Variations
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">Day variations are useful when:</p>

    <ul class="tw:list-disc tw:list-inside tw:text-gray-700 tw:space-y-2 tw:mb-4">
      <li>A daily schedule has different times on certain days</li>
      <li>A weekday schedule skips a particular day (e.g., no Mass on Wednesdays)</li>
      <li>The language changes on a specific day</li>
      <li>You want to note that a particular day follows the regular pattern differently</li>
    </ul>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Creating a Day Variation
    </h3>

    <DocSteps :steps="createVariationSteps" title="To create a day variation:" />

    <DocTip>
      Day variations only work with certain schedule patterns: Daily, Weekdays, and Specific Days.
      Schedules that only occur on a single day (like "Sunday only") don't need variations.
    </DocTip>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Variation Options</h3>

    <v-card class="tw:mb-4 tw:p-4" variant="outlined">
      <div class="tw:space-y-3">
        <div class="tw:flex tw:items-start tw:gap-3">
          <v-chip color="error" size="small" variant="tonal">Skip</v-chip>
          <p class="tw:text-sm tw:text-gray-700">
            The schedule does not occur at all on this day. For example, skip Wednesday for a
            weekday Mass schedule.
          </p>
        </div>
        <div class="tw:flex tw:items-start tw:gap-3">
          <v-chip color="success" size="small" variant="tonal">Modify Time</v-chip>
          <p class="tw:text-sm tw:text-gray-700">
            The schedule occurs at a different time on this day. Enter the new start time.
          </p>
        </div>
        <div class="tw:flex tw:items-start tw:gap-3">
          <v-chip color="info" size="small" variant="tonal">Modify Language</v-chip>
          <p class="tw:text-sm tw:text-gray-700">
            The schedule is in a different language on this day (e.g., Spanish Mass on Thursdays).
          </p>
        </div>
      </div>
    </v-card>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Viewing Day Variations
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">
      When a schedule has day variations, you'll see a chip showing the count (e.g., "2
      variations"). Expand the schedule to see each variation with its day, the changes applied, and
      options to edit or delete.
    </p>

    <DocWarning>
      Deleting a day variation removes it immediately. The schedule will revert to the base pattern
      for that day.
    </DocWarning>

    <DocRelated :sections="['regular-schedules', 'schedule-exceptions', 'churches']" />
  </div>
</template>
