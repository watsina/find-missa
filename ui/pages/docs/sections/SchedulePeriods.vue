<script setup>
  import DocTip from '../components/DocTip.vue';
  import DocWarning from '../components/DocWarning.vue';
  import DocSteps from '../components/DocSteps.vue';
  import DocRelated from '../components/DocRelated.vue';

  const createPeriodSteps = [
    'Navigate to Schedule Periods from the sidebar menu.',
    'Click "Add Schedule Period" to open the form.',
    'Enter a descriptive name (e.g., "Summer Schedule", "Lenten Schedule").',
    'Set the start and end dates for this period.',
    'Optionally add notes about this period.',
    'Click "Save" to create the schedule period.',
  ];

  const periodExamples = [
    {
      name: 'Summer Schedule',
      dates: 'June 1 - August 31',
      use: 'Reduced Mass times during summer',
    },
    {
      name: 'Lenten Schedule',
      dates: 'Ash Wednesday - Holy Thursday',
      use: 'Extra Confession times during Lent',
    },
    {
      name: 'Advent Schedule',
      dates: '4 weeks before Christmas',
      use: 'Special Advent Mass times',
    },
    { name: 'School Year', dates: 'September - May', use: 'School Mass schedules' },
  ];
</script>

<template>
  <div class="doc-section">
    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-800 tw:mb-4">Schedule Periods</h2>

    <p class="tw:text-gray-700 tw:mb-4">
      Schedule periods define time ranges during which certain schedules are active. They help
      manage seasonal changes like summer schedules, Lenten schedules, or any period when your
      regular schedule changes.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      When to Use Schedule Periods
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">Schedule periods are useful for:</p>

    <ul class="tw:list-disc tw:list-inside tw:text-gray-700 tw:space-y-2 tw:mb-4">
      <li><strong>Seasonal schedule changes</strong> - Summer vs. winter Mass times</li>
      <li><strong>Liturgical seasons</strong> - Lent, Advent, Easter season</li>
      <li><strong>School schedules</strong> - Different times when school is in session</li>
      <li><strong>Vacation periods</strong> - Reduced schedules during holidays</li>
    </ul>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Creating a Schedule Period
    </h3>

    <DocSteps :steps="createPeriodSteps" title="To create a schedule period:" />

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Example Periods</h3>

    <v-table class="tw:mb-4" density="compact">
      <thead>
        <tr>
          <th>Period Name</th>
          <th>Typical Dates</th>
          <th>Common Use</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="example in periodExamples" :key="example.name">
          <td class="tw:font-medium">{{ example.name }}</td>
          <td>{{ example.dates }}</td>
          <td class="tw:text-sm">{{ example.use }}</td>
        </tr>
      </tbody>
    </v-table>

    <DocTip>
      You can create schedule periods that span multiple years. For recurring seasonal periods,
      you'll need to update the dates each year or create new periods.
    </DocTip>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Linking Schedules to Periods
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">
      When creating or editing a regular schedule, you can select a schedule period. The schedule
      will only be active during that period. Schedules without a period are active year-round
      (unless they have specific effective dates).
    </p>

    <v-card class="tw:mb-4 tw:p-4" variant="outlined">
      <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-2">Example: Managing Summer Schedule</h4>
      <ol class="tw:list-decimal tw:list-inside tw:text-sm tw:text-gray-700 tw:space-y-1">
        <li>Create a "Summer Schedule" period (June 1 - August 31)</li>
        <li>Create a "Regular Schedule" period (September 1 - May 31)</li>
        <li>For each Mass time, create two schedules: one linked to each period</li>
        <li>The system will automatically show the correct schedule based on the current date</li>
      </ol>
    </v-card>

    <DocWarning>
      If a schedule has both a period AND effective dates, both conditions must be met. Make sure
      your date ranges don't conflict.
    </DocWarning>

    <DocRelated :sections="['regular-schedules', 'holidays', 'schedule-exceptions']" />
  </div>
</template>
