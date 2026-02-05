<script setup>
  import DocTip from '../components/DocTip.vue';
  import DocWarning from '../components/DocWarning.vue';
  import DocSteps from '../components/DocSteps.vue';
  import DocRelated from '../components/DocRelated.vue';

  const createRuleSteps = [
    'Navigate to a church and go to the "Rules" tab.',
    'Click "Add Rule" to open the form.',
    'Enter a descriptive name for the rule.',
    'Define the condition that triggers this rule.',
    'Choose the action to take when the condition is met.',
    'Select which schedules this rule affects.',
    'Enable or disable the rule as needed.',
    'Click "Save" to create the rule.',
  ];
</script>

<template>
  <div class="doc-section">
    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-800 tw:mb-4">Schedule Rules</h2>

    <p class="tw:text-gray-700 tw:mb-4">
      Schedule rules automate schedule changes based on conditions. Instead of manually creating
      exceptions every time a condition occurs, rules automatically apply the appropriate changes.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">How Rules Work</h3>

    <p class="tw:text-gray-700 tw:mb-4">
      Rules have two parts: a <strong>condition</strong> that determines when the rule applies, and
      an <strong>action</strong> that specifies what happens. When the condition is met, the action
      is automatically applied to the affected schedules.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Creating a Rule</h3>

    <DocSteps :steps="createRuleSteps" title="To create a schedule rule:" />

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Common Conditions</h3>

    <v-card class="tw:mb-4 tw:p-4" variant="outlined">
      <div class="tw:space-y-3">
        <div>
          <h4 class="tw:font-semibold tw:text-gray-800">Public Holiday</h4>
          <p class="tw:text-sm tw:text-gray-700">
            Trigger when a date is a civil public holiday (New Year's Day, Labor Day, etc.)
          </p>
        </div>

        <div>
          <h4 class="tw:font-semibold tw:text-gray-800">Liturgical Holiday</h4>
          <p class="tw:text-sm tw:text-gray-700">
            Trigger when a date matches a specific liturgical feast or holy day
          </p>
        </div>

        <div>
          <h4 class="tw:font-semibold tw:text-gray-800">Day of Week</h4>
          <p class="tw:text-sm tw:text-gray-700">
            Trigger on specific days (e.g., all Mondays, all weekends)
          </p>
        </div>

        <div>
          <h4 class="tw:font-semibold tw:text-gray-800">Date Range</h4>
          <p class="tw:text-sm tw:text-gray-700">Trigger during a specific time period each year</p>
        </div>
      </div>
    </v-card>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Common Actions</h3>

    <ul class="tw:list-disc tw:list-inside tw:text-gray-700 tw:space-y-2 tw:mb-4">
      <li><strong>Cancel</strong> - Remove the schedule when the condition is met</li>
      <li><strong>Modify Time</strong> - Change to a different time</li>
      <li><strong>Add Notice</strong> - Keep the schedule but add a note</li>
    </ul>

    <DocTip title="Example Rule">
      "Cancel weekday morning Mass on public holidays" - Condition: Public Holiday, Action: Cancel,
      Target: 8:00 AM Daily Mass. This automatically cancels the early Mass on days like
      Thanksgiving without manual intervention.
    </DocTip>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Rules vs. Exceptions
    </h3>

    <v-card class="tw:mb-4 tw:p-4" variant="outlined">
      <v-row>
        <v-col cols="12" md="6">
          <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-2">Use Rules for:</h4>
          <ul class="tw:list-disc tw:list-inside tw:text-sm tw:text-gray-700 tw:space-y-1">
            <li>Recurring, predictable conditions</li>
            <li>Automation of common changes</li>
            <li>Condition-based logic</li>
          </ul>
        </v-col>
        <v-col cols="12" md="6">
          <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-2">Use Exceptions for:</h4>
          <ul class="tw:list-disc tw:list-inside tw:text-sm tw:text-gray-700 tw:space-y-1">
            <li>Specific, known date ranges</li>
            <li>One-time or infrequent changes</li>
            <li>Situations not covered by rules</li>
          </ul>
        </v-col>
      </v-row>
    </v-card>

    <DocWarning>
      Rules can have unexpected effects if conditions overlap. Review your rules periodically to
      ensure they're working as intended.
    </DocWarning>

    <DocRelated :sections="['schedule-exceptions', 'holidays', 'regular-schedules']" />
  </div>
</template>
