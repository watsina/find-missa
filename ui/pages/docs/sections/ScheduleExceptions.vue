<script setup>
  import DocTip from '../components/DocTip.vue';
  import DocWarning from '../components/DocWarning.vue';
  import DocSteps from '../components/DocSteps.vue';
  import DocRelated from '../components/DocRelated.vue';

  const createExceptionSteps = [
    'Navigate to a church and go to the "Exceptions" tab.',
    'Click "Add Exception" to open the form.',
    'Enter a descriptive name (e.g., "Parish Retreat", "Construction Closure").',
    'Set the start and end dates for the exception period.',
    'Choose the action (Cancel, Modify Time, etc.).',
    'Select which activities are affected (all, Mass only, specific schedule).',
    'Add any notes to explain the exception.',
    'Click "Save" to create the exception.',
  ];

  const exceptionActions = [
    { action: 'Cancel', description: 'Remove the schedule entirely for these dates' },
    { action: 'Modify Time', description: 'Change the time for the affected period' },
    { action: 'Modify Location', description: 'Change where the activity takes place' },
    { action: 'Add Note', description: 'Keep the schedule but add a special notice' },
  ];
</script>

<template>
  <div class="doc-section">
    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-800 tw:mb-4">Schedule Exceptions</h2>

    <p class="tw:text-gray-700 tw:mb-4">
      Schedule exceptions allow you to temporarily modify or cancel regular schedules for specific
      date ranges. Use exceptions for closures, special events, or any temporary schedule changes.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      When to Use Exceptions
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">Exceptions are appropriate for:</p>

    <ul class="tw:list-disc tw:list-inside tw:text-gray-700 tw:space-y-2 tw:mb-4">
      <li><strong>Church closures</strong> - Maintenance, repairs, emergencies</li>
      <li><strong>Parish events</strong> - Retreats, missions, special celebrations</li>
      <li><strong>Priest absences</strong> - Vacation, illness, reassignment</li>
      <li><strong>Temporary relocations</strong> - Using a different building temporarily</li>
      <li><strong>Holiday adjustments</strong> - Changes beyond what liturgical schedules cover</li>
    </ul>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Creating an Exception
    </h3>

    <DocSteps :steps="createExceptionSteps" title="To create a schedule exception:" />

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Exception Actions</h3>

    <v-table class="tw:mb-4" density="compact">
      <thead>
        <tr>
          <th>Action</th>
          <th>Effect</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in exceptionActions" :key="item.action">
          <td class="tw:font-medium">{{ item.action }}</td>
          <td>{{ item.description }}</td>
        </tr>
      </tbody>
    </v-table>

    <DocTip>
      You can target specific schedules or affect all activities at once. If you only need to cancel
      one Mass time while others continue, target that specific schedule.
    </DocTip>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Exceptions vs. Day Variations
    </h3>

    <v-card class="tw:mb-4 tw:p-4" variant="outlined">
      <v-row>
        <v-col cols="12" md="6">
          <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-2">Use Exceptions for:</h4>
          <ul class="tw:list-disc tw:list-inside tw:text-sm tw:text-gray-700 tw:space-y-1">
            <li>Temporary changes (specific dates)</li>
            <li>One-time closures or modifications</li>
            <li>Date-range based changes</li>
          </ul>
        </v-col>
        <v-col cols="12" md="6">
          <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-2">Use Day Variations for:</h4>
          <ul class="tw:list-disc tw:list-inside tw:text-sm tw:text-gray-700 tw:space-y-1">
            <li>Permanent changes to specific days</li>
            <li>Weekly patterns (every Friday)</li>
            <li>Ongoing day-specific adjustments</li>
          </ul>
        </v-col>
      </v-row>
    </v-card>

    <DocWarning>
      Exceptions take precedence over regular schedules. Make sure your date ranges are correct to
      avoid unintended cancellations.
    </DocWarning>

    <DocRelated :sections="['schedule-rules', 'global-exceptions', 'day-variations']" />
  </div>
</template>
