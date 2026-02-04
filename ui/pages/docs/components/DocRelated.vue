<script setup>
  import { computed } from 'vue';
  import { mdiArrowRight } from '@mdi/js';
  import { useDocumentation } from '@/composables/useDocumentation';

  const props = defineProps({
    sections: {
      type: Array,
      required: true,
    },
    title: {
      type: String,
      default: 'Related Topics',
    },
  });

  const emit = defineEmits(['navigate']);

  const { getSection } = useDocumentation();

  const relatedSections = computed(() => {
    return props.sections.map((sectionId) => getSection(sectionId)).filter(Boolean);
  });

  function navigateTo(sectionId) {
    emit('navigate', sectionId);
  }
</script>

<template>
  <div v-if="relatedSections.length > 0" class="tw:my-6 tw:p-4 tw:bg-gray-50 tw:rounded-lg">
    <h4 class="tw:text-sm tw:font-semibold tw:text-gray-700 tw:mb-3">{{ title }}</h4>
    <div class="tw:flex tw:flex-wrap tw:gap-2">
      <v-chip
        v-for="section in relatedSections"
        :key="section.id"
        :append-icon="mdiArrowRight"
        color="primary"
        link
        :prepend-icon="section.icon"
        size="small"
        :to="{ name: 'docs.section', params: { section: section.id } }"
        variant="outlined"
        @click="navigateTo(section.id)"
      >
        {{ section.title }}
      </v-chip>
    </div>
  </div>
</template>
