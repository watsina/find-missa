<script setup>
  import DocTip from '../components/DocTip.vue';
  import DocWarning from '../components/DocWarning.vue';
  import DocSteps from '../components/DocSteps.vue';
  import DocRelated from '../components/DocRelated.vue';

  const createChurchSteps = [
    'Navigate to Churches from the sidebar menu.',
    'Click the "Add Church" button.',
    'Select whether this church belongs to a Parish or Sub-Parish.',
    'Choose the specific parish or sub-parish from the dropdown.',
    'Enter the church name (e.g., "St. Joseph Church").',
    'Add the physical address and location details.',
    'Optionally add accessibility information and notes.',
    'Click "Save" to create the church.',
  ];
</script>

<template>
  <div class="doc-section">
    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-800 tw:mb-4">Managing Churches</h2>

    <p class="tw:text-gray-700 tw:mb-4">
      Churches represent the physical buildings where Mass and other sacraments are celebrated. Each
      church belongs to either a parish or a sub-parish and can have its own set of schedules.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Creating a New Church
    </h3>

    <DocSteps :steps="createChurchSteps" title="To create a church:" />

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Church Information</h3>

    <p class="tw:text-gray-700 tw:mb-4">Church records include:</p>

    <ul class="tw:list-disc tw:list-inside tw:text-gray-700 tw:space-y-2 tw:mb-4">
      <li><strong>Name</strong> - Official name of the church building (required)</li>
      <li><strong>Parent</strong> - The parish or sub-parish this church belongs to (required)</li>
      <li><strong>Address</strong> - Full street address for the church</li>
      <li><strong>City, State, Zip</strong> - Location details for searching</li>
      <li><strong>Latitude/Longitude</strong> - GPS coordinates for mapping</li>
      <li><strong>Accessibility</strong> - Wheelchair access, hearing assistance, etc.</li>
      <li><strong>Notes</strong> - Any additional information visitors should know</li>
    </ul>

    <DocTip>
      If a church has multiple entrances or parking areas, include this information in the notes
      field to help visitors find the right entrance.
    </DocTip>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Church Detail Page</h3>

    <p class="tw:text-gray-700 tw:mb-4">
      When you click on a church from the list, you'll see the church detail page. This page has
      several tabs for managing different aspects of the church:
    </p>

    <ul class="tw:list-disc tw:list-inside tw:text-gray-700 tw:space-y-2 tw:mb-4">
      <li><strong>Regular Schedules</strong> - Recurring Mass, Confession, and Adoration times</li>
      <li><strong>One-Time Activities</strong> - Special events that don't repeat</li>
      <li><strong>Liturgical</strong> - Holiday-specific schedules</li>
      <li><strong>Exceptions</strong> - Temporary schedule modifications</li>
      <li><strong>Rules</strong> - Automatic schedule adjustments</li>
    </ul>

    <DocWarning>
      Make sure the church address is accurate. This information may be used by visitors to find the
      church location.
    </DocWarning>

    <DocRelated :sections="['regular-schedules', 'parishes', 'sub-parishes', 'hierarchy']" />
  </div>
</template>
