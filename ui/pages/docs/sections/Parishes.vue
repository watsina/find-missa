<script setup>
  import DocTip from '../components/DocTip.vue';
  import DocWarning from '../components/DocWarning.vue';
  import DocSteps from '../components/DocSteps.vue';
  import DocRelated from '../components/DocRelated.vue';

  const createParishSteps = [
    'Navigate to Parishes from the sidebar menu.',
    'Click the "Add Parish" button.',
    'Select the diocese this parish belongs to from the dropdown.',
    'Enter the parish name (e.g., "St. Mary\'s Parish").',
    'Add pastor information, contact details, and address.',
    'Click "Save" to create the parish.',
  ];
</script>

<template>
  <div class="doc-section">
    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-800 tw:mb-4">Managing Parishes</h2>

    <p class="tw:text-gray-700 tw:mb-4">
      Parishes are the primary organizational unit within a diocese. Each parish typically has one
      or more churches and may have sub-parishes (missions or chapels) associated with it.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Creating a New Parish
    </h3>

    <DocSteps :steps="createParishSteps" title="To create a parish:" />

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Parish Information</h3>

    <p class="tw:text-gray-700 tw:mb-4">When creating or editing a parish, you can specify:</p>

    <ul class="tw:list-disc tw:list-inside tw:text-gray-700 tw:space-y-2 tw:mb-4">
      <li><strong>Name</strong> - The parish name (required)</li>
      <li><strong>Diocese</strong> - The diocese this parish belongs to (required)</li>
      <li><strong>Pastor</strong> - Name of the current pastor</li>
      <li><strong>Website</strong> - Parish website URL</li>
      <li><strong>Address</strong> - Physical address of the parish office</li>
      <li><strong>Phone</strong> - Contact phone number</li>
      <li><strong>Email</strong> - Contact email address</li>
    </ul>

    <DocTip>
      The parish is typically where administrative functions are centered. Even if a parish has
      multiple church buildings, the parish record holds the main contact information.
    </DocTip>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Parish vs. Church vs. Sub-Parish
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">Understanding the distinction:</p>

    <ul class="tw:list-disc tw:list-inside tw:text-gray-700 tw:space-y-2 tw:mb-4">
      <li>
        <strong>Parish</strong> - The administrative/canonical unit with a pastor and defined
        territory
      </li>
      <li>
        <strong>Church</strong> - A physical building where Mass and sacraments are celebrated
      </li>
      <li>
        <strong>Sub-Parish</strong> - A mission, chapel, or satellite location under a parish's care
      </li>
    </ul>

    <DocWarning>
      Deleting a parish will also delete all associated sub-parishes, churches, and their schedules.
      Consider carefully before deleting.
    </DocWarning>

    <DocRelated :sections="['dioceses', 'sub-parishes', 'churches', 'hierarchy']" />
  </div>
</template>
