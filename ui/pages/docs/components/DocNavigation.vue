<script setup>
  import { computed } from 'vue';
  import { mdiLock } from '@mdi/js';

  const props = defineProps({
    sections: {
      type: Array,
      required: true,
    },
    activeSection: {
      type: String,
      default: null,
    },
  });

  const emit = defineEmits(['select']);

  // Group sections by category for better organization
  const groupedSections = computed(() => {
    const groups = [
      {
        title: 'Overview',
        items: props.sections.filter((s) => ['getting-started', 'hierarchy'].includes(s.id)),
      },
      {
        title: 'Organization',
        items: props.sections.filter((s) =>
          ['dioceses', 'parishes', 'sub-parishes', 'churches'].includes(s.id),
        ),
      },
      {
        title: 'Scheduling',
        items: props.sections.filter((s) =>
          [
            'regular-schedules',
            'day-variations',
            'one-time-activities',
            'schedule-periods',
            'holidays',
            'liturgical-schedules',
            'schedule-exceptions',
            'schedule-rules',
            'global-exceptions',
          ].includes(s.id),
        ),
      },
      {
        title: 'Other Features',
        items: props.sections.filter((s) =>
          ['adoration-chapels', 'priests', 'team-management', 'data-management'].includes(s.id),
        ),
      },
      {
        title: 'Help',
        items: props.sections.filter((s) => ['faq'].includes(s.id)),
      },
    ];

    // Filter out empty groups
    return groups.filter((g) => g.items.length > 0);
  });

  function selectSection(sectionId) {
    emit('select', sectionId);
  }
</script>

<template>
  <v-list density="compact" nav>
    <template v-for="group in groupedSections" :key="group.title">
      <v-list-subheader class="tw:text-xs tw:font-semibold tw:uppercase tw:text-gray-500">
        {{ group.title }}
      </v-list-subheader>

      <v-list-item
        v-for="section in group.items"
        :key="section.id"
        :active="activeSection === section.id"
        color="primary"
        :prepend-icon="section.icon"
        rounded="lg"
        :title="section.title"
        @click="selectSection(section.id)"
      >
        <template v-if="section.adminOnly" #append>
          <v-icon color="warning" :icon="mdiLock" size="x-small" />
        </template>
      </v-list-item>
    </template>
  </v-list>
</template>
