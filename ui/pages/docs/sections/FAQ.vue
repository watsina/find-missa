<script setup>
  import DocTip from '../components/DocTip.vue';
  import DocRelated from '../components/DocRelated.vue';

  const faqs = [
    {
      question: 'How do I change the time for a Mass that only changed temporarily?',
      answer:
        'Use Schedule Exceptions to temporarily modify or cancel schedules for specific date ranges. This keeps your regular schedule intact for when things return to normal.',
    },
    {
      question: "What's the difference between a Parish and a Church?",
      answer:
        'A Parish is the canonical/administrative unit led by a pastor. A Church is a physical building. One parish can have multiple church buildings where Mass is celebrated.',
    },
    {
      question: 'How do I handle holiday Mass schedules?',
      answer:
        "Use Liturgical Schedules linked to Holidays. Create the holiday (if it doesn't exist), then create a liturgical schedule for that church specifying the special Mass times.",
    },
    {
      question: 'Can I have different schedules for summer and winter?',
      answer:
        'Yes! Create Schedule Periods for each season (e.g., "Summer Schedule" from June-August). Then create separate regular schedules linked to each period.',
    },
    {
      question: 'How do I indicate that Mass is in a different language?',
      answer:
        'When creating a schedule, select the language from the Language dropdown. Create separate schedules for each language if you have multiple language Masses.',
    },
    {
      question: 'What if my daily Mass is at a different time on Fridays?',
      answer:
        'Use Day Variations! Create the daily schedule, then add a variation for Friday with the different time. This is cleaner than creating separate schedules.',
    },
    {
      question: 'How do I cancel all Masses for a week due to repairs?',
      answer:
        'Create a Schedule Exception with the date range of the closure. Set it to "Cancel" and target all activities. This will suppress all schedules for that period.',
    },
    {
      question: 'Can multiple people manage the same parish data?',
      answer:
        'Yes! Invite team members to your team. All team members can view and edit data (depending on their role). Use the Team Management features to control access.',
    },
    {
      question: 'How do I set up a First Friday Mass?',
      answer:
        'Use the "Monthly (Nth Day)" pattern when creating the schedule. Select "First" and "Friday" to create a schedule that occurs on the first Friday of each month.',
    },
    {
      question: 'What happens if I delete a parish?',
      answer:
        'Deleting a parish will also delete all its sub-parishes, churches, and schedules. This cannot be undone. Export your data first if you need to preserve any information.',
    },
  ];
</script>

<template>
  <div class="doc-section">
    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-800 tw:mb-4">FAQ & Troubleshooting</h2>

    <p class="tw:text-gray-700 tw:mb-4">
      Find answers to commonly asked questions and solutions to frequent issues.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Frequently Asked Questions
    </h3>

    <v-expansion-panels class="tw:mb-6" variant="accordion">
      <v-expansion-panel v-for="(faq, index) in faqs" :key="index">
        <v-expansion-panel-title class="tw:font-medium">
          {{ faq.question }}
        </v-expansion-panel-title>
        <v-expansion-panel-text>
          <p class="tw:text-gray-700">{{ faq.answer }}</p>
        </v-expansion-panel-text>
      </v-expansion-panel>
    </v-expansion-panels>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Common Issues & Solutions
    </h3>

    <v-card class="tw:mb-4 tw:p-4" variant="outlined">
      <div class="tw:space-y-4">
        <div>
          <h4 class="tw:font-semibold tw:text-gray-800">Schedule not appearing</h4>
          <p class="tw:text-sm tw:text-gray-700">
            Check if the schedule has a Schedule Period or effective dates that might be limiting
            when it's active. Also verify that no exceptions are canceling it.
          </p>
        </div>

        <div>
          <h4 class="tw:font-semibold tw:text-gray-800">Can't find my church</h4>
          <p class="tw:text-sm tw:text-gray-700">
            Make sure you're viewing the correct team. Use the team selector in the top bar to
            switch teams if needed.
          </p>
        </div>

        <div>
          <h4 class="tw:font-semibold tw:text-gray-800">Changes not saving</h4>
          <p class="tw:text-sm tw:text-gray-700">
            Ensure all required fields are filled in. Check for validation errors highlighted in
            red. Try refreshing the page and attempting again.
          </p>
        </div>

        <div>
          <h4 class="tw:font-semibold tw:text-gray-800">Duplicate schedules appearing</h4>
          <p class="tw:text-sm tw:text-gray-700">
            Review your schedules and day variations. You may have accidentally created overlapping
            schedules. Delete duplicates and consolidate as needed.
          </p>
        </div>
      </div>
    </v-card>

    <DocTip title="Need More Help?">
      If you can't find the answer to your question here, contact your system administrator or reach
      out to support.
    </DocTip>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Quick Reference</h3>

    <v-table class="tw:mb-4" density="compact">
      <thead>
        <tr>
          <th>I want to...</th>
          <th>Use this feature</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Add a recurring Mass time</td>
          <td>Regular Schedules</td>
        </tr>
        <tr>
          <td>Cancel Mass for one day</td>
          <td>Schedule Exceptions</td>
        </tr>
        <tr>
          <td>Set Christmas Mass times</td>
          <td>Liturgical Schedules</td>
        </tr>
        <tr>
          <td>Change Friday Mass time only</td>
          <td>Day Variations</td>
        </tr>
        <tr>
          <td>Add summer schedule changes</td>
          <td>Schedule Periods</td>
        </tr>
        <tr>
          <td>Add a special event Mass</td>
          <td>One-Time Activities</td>
        </tr>
        <tr>
          <td>Automate holiday cancellations</td>
          <td>Schedule Rules</td>
        </tr>
      </tbody>
    </v-table>

    <DocRelated :sections="['getting-started', 'regular-schedules', 'hierarchy']" />
  </div>
</template>
