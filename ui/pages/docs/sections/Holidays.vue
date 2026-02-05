<script setup>
  import DocTip from '../components/DocTip.vue';
  import DocWarning from '../components/DocWarning.vue';
  import DocSteps from '../components/DocSteps.vue';
  import DocRelated from '../components/DocRelated.vue';

  const createHolidaySteps = [
    'Navigate to Holidays from the sidebar menu.',
    'Click "Add Holiday" to open the form.',
    'Enter the holiday name (e.g., "Christmas", "Easter Sunday").',
    'Select the holiday type (Fixed or Moveable).',
    'For fixed holidays, enter the month and day.',
    'For moveable holidays, the date is calculated automatically.',
    'Click "Save" to create the holiday.',
  ];
</script>

<template>
  <div class="doc-section">
    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-800 tw:mb-4">Holidays</h2>

    <p class="tw:text-gray-700 tw:mb-4">
      Holidays in Find Missa represent liturgical feasts and holy days. They serve as reference
      points for liturgical schedules, allowing you to define special Mass times that automatically
      apply on the correct date each year.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Types of Holidays</h3>

    <v-row class="tw:mb-4">
      <v-col cols="12" md="6">
        <v-card class="tw:h-full tw:p-4" variant="outlined">
          <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-2">Fixed Holidays</h4>
          <p class="tw:text-sm tw:text-gray-700 tw:mb-2">
            Occur on the same calendar date every year.
          </p>
          <ul class="tw:list-disc tw:list-inside tw:text-sm tw:text-gray-600 tw:space-y-1">
            <li>Christmas (December 25)</li>
            <li>Assumption (August 15)</li>
            <li>All Saints (November 1)</li>
            <li>Immaculate Conception (December 8)</li>
          </ul>
        </v-card>
      </v-col>
      <v-col cols="12" md="6">
        <v-card class="tw:h-full tw:p-4" variant="outlined">
          <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-2">Moveable Holidays</h4>
          <p class="tw:text-sm tw:text-gray-700 tw:mb-2">
            Date changes each year based on the liturgical calendar.
          </p>
          <ul class="tw:list-disc tw:list-inside tw:text-sm tw:text-gray-600 tw:space-y-1">
            <li>Easter Sunday</li>
            <li>Ash Wednesday</li>
            <li>Ascension Thursday</li>
            <li>Pentecost Sunday</li>
          </ul>
        </v-card>
      </v-col>
    </v-row>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Creating a Holiday</h3>

    <DocSteps :steps="createHolidaySteps" title="To create a holiday:" />

    <DocTip>
      Common liturgical holidays are typically already in the system. Check the existing list before
      creating new ones to avoid duplicates.
    </DocTip>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      How Holidays Are Used
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">
      Holidays serve as anchors for liturgical schedules. Instead of specifying a date like
      "December 25, 2025," you link to the "Christmas" holiday. This allows:
    </p>

    <ul class="tw:list-disc tw:list-inside tw:text-gray-700 tw:space-y-2 tw:mb-4">
      <li>
        <strong>Year-independent scheduling</strong> - Set Christmas Mass times once and they apply
        every year
      </li>
      <li>
        <strong>Moveable feast handling</strong> - Easter schedules automatically adjust to the
        correct date
      </li>
      <li>
        <strong>Consistency</strong> - All churches can reference the same holiday definitions
      </li>
    </ul>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Holidays vs. Schedule Periods
    </h3>

    <v-card class="tw:mb-4 tw:p-4" variant="outlined">
      <v-row>
        <v-col cols="12" md="6">
          <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-2">Use Holidays for:</h4>
          <ul class="tw:list-disc tw:list-inside tw:text-sm tw:text-gray-700 tw:space-y-1">
            <li>Single-day special schedules</li>
            <li>Holy days of obligation</li>
            <li>Feast day Masses</li>
          </ul>
        </v-col>
        <v-col cols="12" md="6">
          <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-2">Use Schedule Periods for:</h4>
          <ul class="tw:list-disc tw:list-inside tw:text-sm tw:text-gray-700 tw:space-y-1">
            <li>Multi-week schedule changes</li>
            <li>Seasonal adjustments</li>
            <li>Extended periods (Lent, Summer)</li>
          </ul>
        </v-col>
      </v-row>
    </v-card>

    <DocWarning>
      Deleting a holiday will affect any liturgical schedules linked to it. Make sure no schedules
      depend on a holiday before removing it.
    </DocWarning>

    <DocRelated :sections="['liturgical-schedules', 'schedule-periods', 'regular-schedules']" />
  </div>
</template>
