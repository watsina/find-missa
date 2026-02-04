<script setup>
  import DocTip from '../components/DocTip.vue';
  import DocWarning from '../components/DocWarning.vue';
  import DocSteps from '../components/DocSteps.vue';
  import DocRelated from '../components/DocRelated.vue';

  const createGlobalExceptionSteps = [
    'Navigate to Global Exceptions from the sidebar menu (admin only).',
    'Click "Add Global Exception" to open the form.',
    'Enter a descriptive name (e.g., "Diocesan Quarantine", "Weather Emergency").',
    'Set the start and end dates for the exception.',
    'Choose which activity types are affected.',
    'Add notes explaining the reason for the global exception.',
    'Click "Save" to create the global exception.',
  ];
</script>

<template>
  <div class="doc-section">
    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-800 tw:mb-4">Global Exceptions</h2>

    <v-alert class="tw:mb-4" type="warning" variant="tonal">
      This feature is only available to administrators.
    </v-alert>

    <p class="tw:text-gray-700 tw:mb-4">
      Global exceptions affect all churches across the entire system. They're used for system-wide
      closures or modifications that apply universally, such as during emergencies or diocesan-wide
      events.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      When to Use Global Exceptions
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">Global exceptions are appropriate for:</p>

    <ul class="tw:list-disc tw:list-inside tw:text-gray-700 tw:space-y-2 tw:mb-4">
      <li><strong>Pandemic closures</strong> - When all churches must close</li>
      <li><strong>Severe weather</strong> - Region-wide weather emergencies</li>
      <li><strong>Diocesan events</strong> - Bishop's events requiring all churches to adjust</li>
      <li><strong>Civil emergencies</strong> - Situations affecting the entire area</li>
      <li><strong>System maintenance</strong> - If schedules need to be suspended temporarily</li>
    </ul>

    <DocWarning title="Use With Caution">
      Global exceptions affect ALL churches in the system. Only use them for truly universal
      situations. For individual church closures, use regular schedule exceptions instead.
    </DocWarning>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Creating a Global Exception
    </h3>

    <DocSteps :steps="createGlobalExceptionSteps" title="To create a global exception:" />

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Global vs. Regular Exceptions
    </h3>

    <v-card class="tw:mb-4 tw:p-4" variant="outlined">
      <v-row>
        <v-col cols="12" md="6">
          <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-2">Global Exceptions:</h4>
          <ul class="tw:list-disc tw:list-inside tw:text-sm tw:text-gray-700 tw:space-y-1">
            <li>Affect ALL churches</li>
            <li>Admin-only access</li>
            <li>For universal situations</li>
            <li>Cannot be overridden by churches</li>
          </ul>
        </v-col>
        <v-col cols="12" md="6">
          <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-2">Regular Exceptions:</h4>
          <ul class="tw:list-disc tw:list-inside tw:text-sm tw:text-gray-700 tw:space-y-1">
            <li>Affect one church</li>
            <li>Any team member can create</li>
            <li>For local situations</li>
            <li>Managed per church</li>
          </ul>
        </v-col>
      </v-row>
    </v-card>

    <DocTip>
      Global exceptions take the highest priority. They will override any church-level exceptions or
      rules for the affected period.
    </DocTip>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Managing Global Exceptions
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">
      You can view all global exceptions from the Global Exceptions page. Active exceptions are
      clearly marked. You can edit or delete global exceptions as situations change.
    </p>

    <DocRelated :sections="['schedule-exceptions', 'schedule-rules', 'data-management']" />
  </div>
</template>
