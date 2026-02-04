<script setup>
  import DocTip from '../components/DocTip.vue';
  import DocSteps from '../components/DocSteps.vue';
  import DocRelated from '../components/DocRelated.vue';

  const createLiturgicalSteps = [
    'Navigate to a church and go to the "Liturgical" tab.',
    'Click "Add Liturgical Schedule" to open the form.',
    'Select the holiday this schedule applies to (e.g., Christmas, Easter).',
    'Choose whether this applies to a specific year or every year.',
    'Add one or more Mass times for this holiday.',
    'Decide if this replaces the regular schedule for the day.',
    'Click "Save" to create the liturgical schedule.',
  ];
</script>

<template>
  <div class="doc-section">
    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-800 tw:mb-4">Liturgical Schedules</h2>

    <p class="tw:text-gray-700 tw:mb-4">
      Liturgical schedules define special Mass times for holidays and holy days. Instead of creating
      one-time activities each year, you can link to a holiday and have the schedule automatically
      apply on the correct date.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Creating a Liturgical Schedule
    </h3>

    <DocSteps :steps="createLiturgicalSteps" title="To create a liturgical schedule:" />

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Key Options</h3>

    <v-card class="tw:mb-4 tw:p-4" variant="outlined">
      <div class="tw:space-y-4">
        <div>
          <h4 class="tw:font-semibold tw:text-gray-800">Holiday Selection</h4>
          <p class="tw:text-sm tw:text-gray-700">
            Choose from configured holidays. The schedule will apply on that holiday's date, whether
            it's fixed (like Christmas) or moveable (like Easter).
          </p>
        </div>

        <div>
          <h4 class="tw:font-semibold tw:text-gray-800">Year Specification</h4>
          <p class="tw:text-sm tw:text-gray-700">
            Leave blank to apply every year, or specify a year for one-time holiday schedules
            (useful if times change from year to year).
          </p>
        </div>

        <div>
          <h4 class="tw:font-semibold tw:text-gray-800">Replace Regular Schedule</h4>
          <p class="tw:text-sm tw:text-gray-700">
            When enabled, the regular schedule for that day is ignored, and only the liturgical
            times are shown. When disabled, both regular and liturgical times appear.
          </p>
        </div>

        <div>
          <h4 class="tw:font-semibold tw:text-gray-800">Multiple Times</h4>
          <p class="tw:text-sm tw:text-gray-700">
            You can add multiple Mass times to a single liturgical schedule (e.g., Christmas
            Midnight Mass, 8 AM, 10:30 AM).
          </p>
        </div>
      </div>
    </v-card>

    <DocTip title="Vigil Masses">
      For vigil Masses (like Christmas Eve), you may need to create separate liturgical schedules or
      use different holidays if your system tracks vigils separately from the feast day itself.
    </DocTip>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Example: Christmas Schedule
    </h3>

    <v-card class="tw:mb-4 tw:p-4 tw:bg-gray-50" variant="outlined">
      <p class="tw:font-semibold tw:text-gray-800 tw:mb-2">Christmas Day Masses</p>
      <ul class="tw:list-disc tw:list-inside tw:text-sm tw:text-gray-700 tw:space-y-1">
        <li>Holiday: Christmas</li>
        <li>Year: Every Year</li>
        <li>Times: 12:00 AM (Midnight), 8:00 AM, 10:30 AM</li>
        <li>Replaces Regular Schedule: Yes</li>
      </ul>
      <p class="tw:text-xs tw:text-gray-500 tw:mt-2">
        This schedule will automatically apply on December 25th each year, showing only these three
        Mass times instead of the regular weekday schedule.
      </p>
    </v-card>

    <DocRelated :sections="['holidays', 'one-time-activities', 'regular-schedules']" />
  </div>
</template>
