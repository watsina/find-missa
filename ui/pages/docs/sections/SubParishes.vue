<script setup>
  import DocTip from '../components/DocTip.vue';
  import DocSteps from '../components/DocSteps.vue';
  import DocRelated from '../components/DocRelated.vue';

  const createSubParishSteps = [
    'Navigate to Sub-Parishes from the sidebar menu.',
    'Click the "Add Sub-Parish" button.',
    'Select the parent parish from the dropdown.',
    'Enter the sub-parish name.',
    'Add any additional contact or location information.',
    'Click "Save" to create the sub-parish.',
  ];
</script>

<template>
  <div class="doc-section">
    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-800 tw:mb-4">Managing Sub-Parishes</h2>

    <p class="tw:text-gray-700 tw:mb-4">
      Sub-parishes represent missions, chapels, or satellite locations that are administratively
      part of a larger parish but have their own identity and possibly their own schedule of
      services.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      When to Use Sub-Parishes
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">Sub-parishes are appropriate for:</p>

    <ul class="tw:list-disc tw:list-inside tw:text-gray-700 tw:space-y-2 tw:mb-4">
      <li><strong>Mission churches</strong> - Churches in rural or remote areas</li>
      <li>
        <strong>Chapel communities</strong> - Smaller worship communities within a parish territory
      </li>
      <li><strong>Ethnic/language communities</strong> - Communities serving specific groups</li>
      <li>
        <strong>Historical chapels</strong> - Historic sites still used for occasional services
      </li>
    </ul>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Creating a Sub-Parish
    </h3>

    <DocSteps :steps="createSubParishSteps" title="To create a sub-parish:" />

    <DocTip>
      Sub-parishes inherit their team association from their parent parish. When you select a
      parish, the sub-parish will automatically be associated with the same team.
    </DocTip>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Sub-Parish vs. Multiple Churches
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">
      If a parish has multiple church buildings in the same location (e.g., an old church and a new
      church on the same campus), you may want to create them as separate churches under the same
      parish rather than using sub-parishes. Sub-parishes are best for geographically separate
      locations with their own community identity.
    </p>

    <DocRelated :sections="['parishes', 'churches', 'hierarchy']" />
  </div>
</template>
