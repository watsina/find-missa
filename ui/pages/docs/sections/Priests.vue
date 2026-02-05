<script setup>
  import DocTip from '../components/DocTip.vue';
  import DocSteps from '../components/DocSteps.vue';
  import DocRelated from '../components/DocRelated.vue';

  const createPriestSteps = [
    'Navigate to Priests from the sidebar menu.',
    'Click "Add Priest" to open the form.',
    "Enter the priest's name and title (Fr., Msgr., Bishop, etc.).",
    'Optionally add contact information and notes.',
    'Click "Save" to create the priest record.',
  ];
</script>

<template>
  <div class="doc-section">
    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-800 tw:mb-4">Priests</h2>

    <p class="tw:text-gray-700 tw:mb-4">
      The Priests section allows you to maintain records of clergy who serve in your parishes and
      churches. This information can be associated with parishes and used for reference.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Creating a Priest Record
    </h3>

    <DocSteps :steps="createPriestSteps" title="To add a priest:" />

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Priest Information</h3>

    <p class="tw:text-gray-700 tw:mb-4">Priest records can include:</p>

    <ul class="tw:list-disc tw:list-inside tw:text-gray-700 tw:space-y-2 tw:mb-4">
      <li><strong>Name</strong> - Full name of the priest</li>
      <li><strong>Title</strong> - Fr., Rev., Msgr., Bishop, etc.</li>
      <li><strong>Assignment</strong> - Current parish or role</li>
      <li><strong>Contact Information</strong> - Email and phone (optional)</li>
      <li><strong>Notes</strong> - Additional information</li>
    </ul>

    <DocTip>
      You can associate priests with parishes when creating or editing parish records. This helps
      track who serves where and maintains accurate directory information.
    </DocTip>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Priest Assignments</h3>

    <p class="tw:text-gray-700 tw:mb-4">
      When a priest is reassigned or transfers to a different parish, update both the priest record
      and the affected parish records to maintain accurate information.
    </p>

    <v-card class="tw:mb-4 tw:p-4" variant="outlined">
      <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-2">Common Priest Roles</h4>
      <ul class="tw:list-disc tw:list-inside tw:text-sm tw:text-gray-700 tw:space-y-1">
        <li><strong>Pastor</strong> - Primary priest responsible for a parish</li>
        <li><strong>Parochial Vicar</strong> - Associate priest assisting the pastor</li>
        <li><strong>Administrator</strong> - Temporary leader of a parish</li>
        <li><strong>Visiting Priest</strong> - Priest who assists occasionally</li>
        <li><strong>Retired</strong> - Retired priest who may still celebrate Mass</li>
      </ul>
    </v-card>

    <DocRelated :sections="['parishes', 'churches', 'team-management']" />
  </div>
</template>
