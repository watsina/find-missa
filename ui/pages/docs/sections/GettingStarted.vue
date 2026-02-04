<script setup>
  import { mdiChurch, mdiCalendarClock, mdiAccountGroup } from '@mdi/js';
  import DocTip from '../components/DocTip.vue';
  import DocSteps from '../components/DocSteps.vue';
  import DocRelated from '../components/DocRelated.vue';

  const quickStartSteps = [
    {
      text: 'Log in to your account and select your team from the dropdown menu in the top bar.',
      description: 'Each team manages data for a specific diocese or group of parishes.',
    },
    {
      text: 'Start by adding or reviewing your Dioceses, Parishes, and Churches.',
      description: 'This organizational hierarchy is the foundation for all schedule management.',
    },
    {
      text: 'Add regular schedules for Mass, Confession, and Adoration times.',
      description: 'Set up recurring weekly patterns that will automatically repeat.',
    },
    {
      text: 'Configure special schedules for holidays and seasonal changes.',
      description: 'Use schedule periods, liturgical schedules, and exceptions as needed.',
    },
    {
      text: 'Review and maintain your data regularly to keep information accurate.',
      description: 'Update schedules when times change or special events occur.',
    },
  ];
</script>

<template>
  <div class="doc-section">
    <h2 class="tw:text-xl tw:font-semibold tw:text-gray-800 tw:mb-4">Welcome to Find Missa</h2>

    <p class="tw:text-gray-700 tw:mb-4">
      Find Missa is a comprehensive system for managing Mass times, Confession schedules, and
      Adoration hours across dioceses, parishes, and churches. This documentation will help you
      understand how to use the system effectively.
    </p>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      What You Can Do with Find Missa
    </h3>

    <v-row class="tw:mb-6">
      <v-col cols="12" md="4">
        <v-card class="tw:h-full" variant="outlined">
          <v-card-item>
            <template #prepend>
              <v-icon color="primary" :icon="mdiChurch" size="large" />
            </template>
            <v-card-title class="tw:text-base">Manage Churches</v-card-title>
            <v-card-subtitle>
              Organize dioceses, parishes, sub-parishes, and individual churches
            </v-card-subtitle>
          </v-card-item>
        </v-card>
      </v-col>
      <v-col cols="12" md="4">
        <v-card class="tw:h-full" variant="outlined">
          <v-card-item>
            <template #prepend>
              <v-icon color="primary" :icon="mdiCalendarClock" size="large" />
            </template>
            <v-card-title class="tw:text-base">Schedule Activities</v-card-title>
            <v-card-subtitle>
              Set up regular and special schedules for Mass, Confession, and Adoration
            </v-card-subtitle>
          </v-card-item>
        </v-card>
      </v-col>
      <v-col cols="12" md="4">
        <v-card class="tw:h-full" variant="outlined">
          <v-card-item>
            <template #prepend>
              <v-icon color="primary" :icon="mdiAccountGroup" size="large" />
            </template>
            <v-card-title class="tw:text-base">Collaborate</v-card-title>
            <v-card-subtitle>
              Work with your team to maintain accurate schedule information
            </v-card-subtitle>
          </v-card-item>
        </v-card>
      </v-col>
    </v-row>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">Quick Start Guide</h3>

    <DocSteps :steps="quickStartSteps" />

    <DocTip title="Pro Tip">
      Use the Dashboard to get a quick overview of your data. You can see counts of all your
      dioceses, parishes, churches, and activities at a glance.
    </DocTip>

    <h3 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mt-6 tw:mb-3">
      Understanding the Navigation
    </h3>

    <p class="tw:text-gray-700 tw:mb-4">
      The main navigation sidebar on the left provides quick access to all areas of the system:
    </p>

    <ul class="tw:list-disc tw:list-inside tw:text-gray-700 tw:space-y-2 tw:mb-4">
      <li><strong>Dashboard</strong> - Overview of your data and recent activity</li>
      <li><strong>Dioceses</strong> - Manage diocese/archdiocese records</li>
      <li><strong>Parishes</strong> - Manage parish records within dioceses</li>
      <li><strong>Sub-Parishes</strong> - Manage sub-parish records (missions, chapels)</li>
      <li><strong>Churches</strong> - Manage individual church buildings and their details</li>
      <li><strong>Activities</strong> - View and manage all activity schedules</li>
      <li><strong>Holidays</strong> - Configure liturgical holidays and feasts</li>
      <li><strong>Schedule Periods</strong> - Define seasonal schedule periods</li>
      <li><strong>Priests</strong> - Manage priest records and assignments</li>
    </ul>

    <DocRelated :sections="['hierarchy', 'churches', 'regular-schedules']" />
  </div>
</template>
