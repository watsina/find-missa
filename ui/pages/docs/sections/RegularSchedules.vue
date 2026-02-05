<script setup>
  import DocTip from '../components/DocTip.vue';
  import DocWarning from '../components/DocWarning.vue';
  import DocSteps from '../components/DocSteps.vue';
  import DocRelated from '../components/DocRelated.vue';

  const createScheduleSteps = [
    'Navigate to a church and go to the "Regular Schedules" tab.',
    'Click "Add Schedule" to open the schedule form.',
    'Select the activity type (Mass, Confession, or Adoration).',
    'Choose the day pattern (daily, weekdays, specific days, etc.).',
    'Enter the start time and optionally an end time.',
    'Select the language if applicable.',
    'Optionally set a schedule period or effective date range.',
    'Click "Save" to create the schedule.',
  ];

  const dayPatterns = [
    { pattern: 'Daily', description: 'Every day of the week' },
    { pattern: 'Weekdays', description: 'Monday through Friday' },
    { pattern: 'Specific Days', description: 'Choose which days (e.g., Mon, Wed, Fri)' },
    { pattern: 'Saturday', description: 'Saturdays only' },
    { pattern: 'Sunday', description: 'Sundays only' },
    { pattern: 'Monthly (Nth Day)', description: 'First Sunday, Third Wednesday, etc.' },
  ];
</script>

<template>
  <div class="doc-section">
    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-800 tw:mb-4">Regular Schedules</h2>

    <p class="tw:text-gray-700 tw:mb-4">
      Regular schedules are recurring activities like weekly Mass times, daily Confession hours, or
      Adoration schedules. Once created, these schedules automatically repeat according to their
      pattern.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Activity Types</h3>

    <p class="tw:text-gray-700 tw:mb-4">Find Missa supports three types of activities:</p>

    <ul class="tw:list-disc tw:list-inside tw:text-gray-700 tw:space-y-2 tw:mb-4">
      <li><strong>Mass</strong> - Holy Mass celebrations</li>
      <li><strong>Confession</strong> - Reconciliation/Confession times</li>
      <li><strong>Adoration</strong> - Eucharistic Adoration hours</li>
    </ul>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Creating a Regular Schedule
    </h3>

    <DocSteps :steps="createScheduleSteps" title="To create a regular schedule:" />

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Day Patterns</h3>

    <p class="tw:text-gray-700 tw:mb-4">
      Choose the pattern that matches when the activity occurs:
    </p>

    <v-table class="tw:mb-4" density="compact">
      <thead>
        <tr>
          <th>Pattern</th>
          <th>Description</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in dayPatterns" :key="item.pattern">
          <td class="tw:font-medium">{{ item.pattern }}</td>
          <td>{{ item.description }}</td>
        </tr>
      </tbody>
    </v-table>

    <DocTip title="Multiple Times">
      If you have multiple Mass times on the same day pattern (e.g., 8:00 AM and 10:30 AM on
      Sundays), create a separate schedule for each time.
    </DocTip>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Schedule Options</h3>

    <ul class="tw:list-disc tw:list-inside tw:text-gray-700 tw:space-y-2 tw:mb-4">
      <li>
        <strong>Language</strong> - Specify if the Mass is in a particular language (English,
        Spanish, Latin, etc.)
      </li>
      <li>
        <strong>Schedule Period</strong> - Link to a seasonal period (Summer, Lent, etc.) so the
        schedule only applies during that time
      </li>
      <li>
        <strong>Effective From/Until</strong> - Set specific start and end dates for temporary
        schedules
      </li>
      <li><strong>Notes</strong> - Add any additional information about this schedule</li>
    </ul>

    <DocWarning>
      If you set both a schedule period AND effective dates, both conditions must be met for the
      schedule to be active.
    </DocWarning>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Viewing and Managing Schedules
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">
      On the church detail page, you can expand each schedule to see its details and any day
      variations. Use the edit and delete icons to modify or remove schedules.
    </p>

    <DocRelated :sections="['day-variations', 'schedule-periods', 'churches']" />
  </div>
</template>
