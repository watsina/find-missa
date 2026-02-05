<script setup>
  import DocTip from '../components/DocTip.vue';
  import DocWarning from '../components/DocWarning.vue';
  import DocRelated from '../components/DocRelated.vue';
</script>

<template>
  <div class="doc-section">
    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-800 tw:mb-4">Data Management</h2>

    <v-alert class="tw:mb-4" type="warning" variant="tonal">
      Some features in this section are only available to administrators.
    </v-alert>

    <p class="tw:text-gray-700 tw:mb-4">
      Find Missa provides tools for exporting, backing up, and managing your diocese data. These
      features help ensure your data is safe and can be transferred or restored as needed.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Exporting Data</h3>

    <p class="tw:text-gray-700 tw:mb-4">
      Administrators can export all team data to a file. This is useful for:
    </p>

    <ul class="tw:list-disc tw:list-inside tw:text-gray-700 tw:space-y-2 tw:mb-4">
      <li><strong>Backups</strong> - Regular exports ensure you have a copy of your data</li>
      <li><strong>Migration</strong> - Moving data to a different system</li>
      <li><strong>Reporting</strong> - Creating reports from exported data</li>
      <li><strong>Archiving</strong> - Keeping historical records</li>
    </ul>

    <v-card class="tw:mb-4 tw:p-4" variant="outlined">
      <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-2">To export data:</h4>
      <ol class="tw:list-decimal tw:list-inside tw:text-sm tw:text-gray-700 tw:space-y-1">
        <li>Go to the Dashboard</li>
        <li>Click "Export Data" button (admin only)</li>
        <li>Select the data types you want to export</li>
        <li>Choose the export format</li>
        <li>Download the exported file</li>
      </ol>
    </v-card>

    <DocTip>
      Export your data regularly as a backup. Store exports in a secure location separate from Find
      Missa.
    </DocTip>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Importing Data</h3>

    <p class="tw:text-gray-700 tw:mb-4">
      Data can be imported from properly formatted files. This is useful when setting up a new team
      or restoring from a backup.
    </p>

    <DocWarning title="Import Caution">
      Importing data may overwrite existing records. Always export your current data as a backup
      before importing new data.
    </DocWarning>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Clearing Data</h3>

    <p class="tw:text-gray-700 tw:mb-4">
      Administrators can clear all team data from the Dashboard. This permanently deletes all
      dioceses, parishes, churches, schedules, and related data.
    </p>

    <DocWarning title="Irreversible Action">
      Clearing data cannot be undone. All data will be permanently deleted. Always export your data
      first if you need to preserve it.
    </DocWarning>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Best Practices</h3>

    <v-card class="tw:mb-4 tw:p-4" variant="outlined">
      <ul class="tw:list-disc tw:list-inside tw:text-sm tw:text-gray-700 tw:space-y-2">
        <li>Export data weekly or before making major changes</li>
        <li>Store exports in multiple locations (cloud storage, local drive)</li>
        <li>Test your exports periodically to ensure they're complete</li>
        <li>Document your data management procedures for your team</li>
        <li>Keep a change log of major data modifications</li>
      </ul>
    </v-card>

    <DocRelated :sections="['team-management', 'global-exceptions']" />
  </div>
</template>
