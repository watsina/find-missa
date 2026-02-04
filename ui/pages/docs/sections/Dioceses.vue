<script setup>
  import DocTip from '../components/DocTip.vue';
  import DocWarning from '../components/DocWarning.vue';
  import DocSteps from '../components/DocSteps.vue';
  import DocRelated from '../components/DocRelated.vue';

  const createDioceseSteps = [
    'Navigate to Dioceses from the sidebar menu.',
    'Click the "Add Diocese" button in the top right.',
    'Fill in the diocese name (e.g., "Diocese of Springfield" or "Archdiocese of Chicago").',
    'Optionally add contact information, website, and other details.',
    'Click "Save" to create the diocese.',
  ];
</script>

<template>
  <div class="doc-section">
    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-800 tw:mb-4">Managing Dioceses</h2>

    <p class="tw:text-gray-700 tw:mb-4">
      A diocese is the highest level in the organizational hierarchy. Each diocese contains multiple
      parishes, which in turn contain churches. Archdioceses are also managed as dioceses in the
      system.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Creating a New Diocese
    </h3>

    <DocSteps :steps="createDioceseSteps" title="To create a diocese:" />

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Diocese Information
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">When creating or editing a diocese, you can specify:</p>

    <ul class="tw:list-disc tw:list-inside tw:text-gray-700 tw:space-y-2 tw:mb-4">
      <li><strong>Name</strong> - The official name of the diocese (required)</li>
      <li><strong>Type</strong> - Diocese or Archdiocese</li>
      <li><strong>Website</strong> - Official diocese website URL</li>
      <li><strong>Address</strong> - Physical address of the diocesan offices</li>
      <li><strong>Contact Information</strong> - Phone number and email</li>
    </ul>

    <DocTip>
      When naming a diocese, use the official title including "Diocese of" or "Archdiocese of" for
      clarity. This helps users identify the correct diocese when searching.
    </DocTip>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Editing and Deleting Dioceses
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">
      To edit a diocese, click on it from the list to view its details, then use the edit button.
      You can update any information as needed.
    </p>

    <DocWarning>
      Deleting a diocese will also delete all associated parishes, sub-parishes, churches, and
      schedules. This action cannot be undone. Make sure to export your data first if you need to
      preserve it.
    </DocWarning>

    <DocRelated :sections="['parishes', 'hierarchy', 'churches']" />
  </div>
</template>
