<script setup>
  import DocTip from '../components/DocTip.vue';
  import DocRelated from '../components/DocRelated.vue';
</script>

<template>
  <div class="doc-section">
    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-800 tw:mb-4">Adoration Chapels</h2>

    <p class="tw:text-gray-700 tw:mb-4">
      Adoration chapels have special scheduling needs compared to regular churches. They often have
      extended or perpetual adoration hours, and their schedules may need to show when adorers are
      needed or when the chapel is accessible.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Setting Up an Adoration Chapel
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">
      Adoration chapels are managed as churches in the system. When creating or editing a church,
      you can indicate that it's used primarily for adoration by focusing on adoration schedules.
    </p>

    <DocTip>
      For perpetual adoration chapels (24/7), you may want to create schedules that cover the full
      day, or create multiple schedules for different time blocks to indicate different adorers'
      commitments.
    </DocTip>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Adoration Schedule Patterns
    </h3>

    <v-card class="tw:mb-4 tw:p-4" variant="outlined">
      <div class="tw:space-y-3">
        <div>
          <h4 class="tw:font-semibold tw:text-gray-800">Perpetual Adoration</h4>
          <p class="tw:text-sm tw:text-gray-700">
            24 hours a day, 7 days a week. Consider creating hourly or multi-hour blocks to manage
            adorer commitments.
          </p>
        </div>

        <div>
          <h4 class="tw:font-semibold tw:text-gray-800">Daily Adoration Hours</h4>
          <p class="tw:text-sm tw:text-gray-700">
            Chapel is open for specific hours each day. Use regular schedules with start and end
            times.
          </p>
        </div>

        <div>
          <h4 class="tw:font-semibold tw:text-gray-800">Weekly Adoration</h4>
          <p class="tw:text-sm tw:text-gray-700">
            First Friday adoration, weekly holy hours. Use specific day patterns or one-time
            activities.
          </p>
        </div>

        <div>
          <h4 class="tw:font-semibold tw:text-gray-800">Seasonal Variations</h4>
          <p class="tw:text-sm tw:text-gray-700">
            Different hours during summer or holidays. Use schedule periods to manage these
            variations.
          </p>
        </div>
      </div>
    </v-card>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Managing Chapel Closures
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">
      Adoration chapels may close for cleaning, repairs, or during certain liturgical celebrations.
      Use schedule exceptions to indicate temporary closures without affecting the regular schedule.
    </p>

    <DocRelated :sections="['regular-schedules', 'schedule-periods', 'schedule-exceptions']" />
  </div>
</template>
