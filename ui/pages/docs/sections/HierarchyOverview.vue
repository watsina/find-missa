<script setup>
  import { mdiSitemapOutline } from '@mdi/js';
  import DocTip from '../components/DocTip.vue';
  import DocRelated from '../components/DocRelated.vue';
</script>

<template>
  <div class="doc-section">
    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-800 tw:mb-4">
      Understanding the Hierarchy
    </h2>

    <p class="tw:text-gray-700 tw:mb-4">
      Find Missa uses a hierarchical structure to organize church data. Understanding this hierarchy
      is key to effectively managing your schedules and information.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      The Four-Level Hierarchy
    </h3>

    <v-card class="tw:mb-6 tw:p-4" variant="outlined">
      <div class="tw:flex tw:items-start tw:gap-4">
        <v-icon color="primary" :icon="mdiSitemapOutline" size="48" />
        <div class="tw:flex-1">
          <div class="tw:space-y-4">
            <div class="tw:p-3 tw:bg-blue-50 tw:rounded-lg tw:border-l-4 tw:border-blue-500">
              <p class="tw:font-semibold tw:text-blue-800">Level 1: Diocese</p>
              <p class="tw:text-sm tw:text-blue-700">
                The highest level. A diocese or archdiocese encompasses a geographic region and is
                led by a bishop.
              </p>
            </div>

            <div
              class="tw:ml-6 tw:p-3 tw:bg-green-50 tw:rounded-lg tw:border-l-4 tw:border-green-500"
            >
              <p class="tw:font-semibold tw:text-green-800">Level 2: Parish</p>
              <p class="tw:text-sm tw:text-green-700">
                The primary community unit. Each parish belongs to one diocese and is led by a
                pastor.
              </p>
            </div>

            <div
              class="tw:ml-12 tw:p-3 tw:bg-yellow-50 tw:rounded-lg tw:border-l-4 tw:border-yellow-500"
            >
              <p class="tw:font-semibold tw:text-yellow-800">Level 3: Sub-Parish (Optional)</p>
              <p class="tw:text-sm tw:text-yellow-700">
                Missions or chapels that are part of a parish but have their own identity.
              </p>
            </div>

            <div
              class="tw:ml-12 tw:p-3 tw:bg-purple-50 tw:rounded-lg tw:border-l-4 tw:border-purple-500"
            >
              <p class="tw:font-semibold tw:text-purple-800">Level 4: Church</p>
              <p class="tw:text-sm tw:text-purple-700">
                The physical building. Churches belong to either a parish or sub-parish. Schedules
                are set at this level.
              </p>
            </div>
          </div>
        </div>
      </div>
    </v-card>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Key Relationships</h3>

    <ul class="tw:list-disc tw:list-inside tw:text-gray-700 tw:space-y-2 tw:mb-4">
      <li>A <strong>Diocese</strong> contains many <strong>Parishes</strong></li>
      <li>A <strong>Parish</strong> can have multiple <strong>Sub-Parishes</strong> (optional)</li>
      <li>
        A <strong>Parish</strong> or <strong>Sub-Parish</strong> can have multiple
        <strong>Churches</strong>
      </li>
      <li><strong>Schedules</strong> are always assigned to a specific <strong>Church</strong></li>
    </ul>

    <DocTip>
      Not every parish needs sub-parishes. Many parishes have just one or two church buildings
      directly under the parish without any sub-parishes. Only create sub-parishes when there are
      distinct mission communities or geographically separate chapels.
    </DocTip>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Example Structure</h3>

    <v-card class="tw:p-4 tw:mb-4" variant="outlined">
      <pre class="tw:text-sm tw:text-gray-700 tw:whitespace-pre-wrap">
Diocese of Springfield
├── St. Mary's Parish
│   ├── St. Mary Church (main church)
│   └── Holy Family Chapel (church on campus)
├── Sacred Heart Parish
│   ├── Sacred Heart Church
│   └── St. Joseph Mission (sub-parish)
│       └── St. Joseph Chapel (church)
└── Our Lady of Grace Parish
    └── Our Lady of Grace Church
      </pre>
    </v-card>

    <p class="tw:text-gray-700 tw:mb-4">
      In this example, St. Mary's Parish has two churches directly under it. Sacred Heart Parish has
      one church plus a sub-parish (St. Joseph Mission) with its own chapel. Our Lady of Grace is a
      simple parish with just one church.
    </p>

    <DocRelated :sections="['dioceses', 'parishes', 'sub-parishes', 'churches']" />
  </div>
</template>
