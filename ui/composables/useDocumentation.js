import { ref, computed } from 'vue';
import {
  mdiRocketLaunchOutline,
  mdiMapMarkerOutline,
  mdiHomeCity,
  mdiHomeGroup,
  mdiChurch,
  mdiSitemapOutline,
  mdiCalendarClock,
  mdiCalendarEdit,
  mdiCalendarStar,
  mdiCalendarMonth,
  mdiCalendarQuestion,
  mdiCalendarAlert,
  mdiShieldAlertOutline,
  mdiTimerOffOutline,
  mdiCross,
  mdiAccountTie,
  mdiAccountGroup,
  mdiDatabaseExport,
  mdiFrequentlyAskedQuestions,
} from '@mdi/js';

/**
 * Documentation sections configuration
 */
export const docSections = [
  {
    id: 'getting-started',
    title: 'Getting Started',
    icon: mdiRocketLaunchOutline,
    description: 'Learn the basics of Find Missa and get up and running quickly',
    keywords: ['welcome', 'overview', 'quick start', 'dashboard', 'introduction', 'begin'],
  },
  {
    id: 'dioceses',
    title: 'Dioceses',
    icon: mdiMapMarkerOutline,
    description: 'Creating and managing dioceses and archdioceses',
    keywords: ['diocese', 'archdiocese', 'region', 'territory', 'bishop'],
    relatedRoutes: ['dioceses.index', 'dioceses.show'],
  },
  {
    id: 'parishes',
    title: 'Parishes',
    icon: mdiHomeCity,
    description: 'Setting up and managing parishes within dioceses',
    keywords: ['parish', 'pastor', 'community'],
    relatedRoutes: ['parishes.index', 'parishes.show'],
  },
  {
    id: 'sub-parishes',
    title: 'Sub-Parishes',
    icon: mdiHomeGroup,
    description: 'When and how to use sub-parishes for organizational structure',
    keywords: ['sub-parish', 'subparish', 'mission', 'station', 'chapel'],
    relatedRoutes: ['sub-parishes.index', 'sub-parishes.show'],
  },
  {
    id: 'churches',
    title: 'Churches',
    icon: mdiChurch,
    description: 'Adding and managing church details, locations, and contact information',
    keywords: ['church', 'building', 'location', 'address', 'contact', 'cathedral', 'basilica'],
    relatedRoutes: ['churches.index', 'churches.show'],
  },
  {
    id: 'hierarchy',
    title: 'Understanding the Hierarchy',
    icon: mdiSitemapOutline,
    description: 'How dioceses, parishes, sub-parishes, and churches connect',
    keywords: ['hierarchy', 'structure', 'organization', 'relationship', 'connect'],
  },
  {
    id: 'regular-schedules',
    title: 'Regular Schedules',
    icon: mdiCalendarClock,
    description: 'Setting up recurring Mass, Confession, and Adoration times',
    keywords: [
      'schedule',
      'recurring',
      'regular',
      'mass',
      'confession',
      'adoration',
      'weekly',
      'daily',
      'time',
    ],
    relatedRoutes: ['churches.show', 'activities.index'],
  },
  {
    id: 'day-variations',
    title: 'Day Variations',
    icon: mdiCalendarEdit,
    description: 'Overriding schedules for specific days of the week',
    keywords: ['variation', 'override', 'different', 'friday', 'specific day', 'exception'],
    relatedRoutes: ['churches.show'],
  },
  {
    id: 'one-time-activities',
    title: 'One-Time Activities',
    icon: mdiCalendarStar,
    description: 'Creating single-occurrence events that do not repeat',
    keywords: ['one-time', 'single', 'event', 'special', 'once'],
    relatedRoutes: ['churches.show'],
  },
  {
    id: 'schedule-periods',
    title: 'Schedule Periods',
    icon: mdiCalendarMonth,
    description: 'Managing seasonal schedules like Advent, Lent, and Summer',
    keywords: ['period', 'season', 'advent', 'lent', 'summer', 'winter', 'seasonal'],
    relatedRoutes: ['schedule-periods.index'],
  },
  {
    id: 'holidays',
    title: 'Holidays',
    icon: mdiCalendarQuestion,
    description: 'Managing fixed and moveable feasts in the liturgical calendar',
    keywords: ['holiday', 'feast', 'christmas', 'easter', 'holy day', 'liturgical'],
    relatedRoutes: ['holidays.index'],
  },
  {
    id: 'liturgical-schedules',
    title: 'Liturgical Schedules',
    icon: mdiCalendarAlert,
    description: 'Setting special Mass times for holidays like Christmas and Easter',
    keywords: ['liturgical', 'holiday mass', 'christmas mass', 'easter mass', 'special times'],
    relatedRoutes: ['churches.show'],
  },
  {
    id: 'schedule-exceptions',
    title: 'Schedule Exceptions',
    icon: mdiShieldAlertOutline,
    description: 'Temporarily canceling or modifying regular schedules',
    keywords: ['exception', 'cancel', 'modify', 'temporary', 'closure', 'skip'],
    relatedRoutes: ['churches.show'],
  },
  {
    id: 'schedule-rules',
    title: 'Schedule Rules',
    icon: mdiCalendarAlert,
    description: 'Automatic schedule adjustments based on conditions',
    keywords: ['rule', 'automatic', 'condition', 'public holiday', 'adjust'],
    relatedRoutes: ['churches.show'],
  },
  {
    id: 'global-exceptions',
    title: 'Global Exceptions',
    icon: mdiTimerOffOutline,
    description: 'System-wide closures affecting all churches (admin only)',
    keywords: ['global', 'system-wide', 'all churches', 'pandemic', 'emergency'],
    relatedRoutes: ['global-activity-exceptions.index'],
    adminOnly: true,
  },
  {
    id: 'adoration-chapels',
    title: 'Adoration Chapels',
    icon: mdiCross,
    description: 'Managing perpetual adoration chapel schedules and hours',
    keywords: ['adoration', 'chapel', 'perpetual', 'eucharistic', '24 hours', 'opening hours'],
    relatedRoutes: ['churches.show'],
  },
  {
    id: 'priests',
    title: 'Priests',
    icon: mdiAccountTie,
    description: 'Adding and assigning priests to parishes and churches',
    keywords: ['priest', 'clergy', 'pastor', 'father', 'assign'],
    relatedRoutes: ['priests.index'],
  },
  {
    id: 'team-management',
    title: 'Team Management',
    icon: mdiAccountGroup,
    description: 'Managing users, roles, and team settings',
    keywords: ['team', 'user', 'member', 'role', 'permission', 'invite', 'settings'],
    relatedRoutes: ['teams.show'],
  },
  {
    id: 'data-management',
    title: 'Data Management',
    icon: mdiDatabaseExport,
    description: 'Exporting, importing, and managing your diocese data (admin only)',
    keywords: ['export', 'import', 'backup', 'restore', 'migrate', 'data'],
    relatedRoutes: ['dashboard.index', 'teams.show'],
    adminOnly: true,
  },
  {
    id: 'faq',
    title: 'FAQ & Troubleshooting',
    icon: mdiFrequentlyAskedQuestions,
    description: 'Answers to common questions and solutions to frequent issues',
    keywords: ['faq', 'help', 'question', 'problem', 'issue', 'troubleshoot', 'support'],
  },
];

/**
 * Composable for documentation functionality
 */
export function useDocumentation() {
  const searchQuery = ref('');
  const isSearching = ref(false);

  /**
   * Search documentation sections by query
   */
  const searchResults = computed(() => {
    if (!searchQuery.value || searchQuery.value.length < 2) {
      return [];
    }

    const query = searchQuery.value.toLowerCase();

    return docSections.filter((section) => {
      // Search in title
      if (section.title.toLowerCase().includes(query)) return true;

      // Search in description
      if (section.description.toLowerCase().includes(query)) return true;

      // Search in keywords
      if (section.keywords?.some((keyword) => keyword.toLowerCase().includes(query))) return true;

      return false;
    });
  });

  /**
   * Get documentation sections relevant to a route
   */
  function getSectionsForRoute(routeName) {
    if (!routeName) return [];

    return docSections.filter((section) => section.relatedRoutes?.includes(routeName));
  }

  /**
   * Get a specific section by ID
   */
  function getSection(sectionId) {
    return docSections.find((section) => section.id === sectionId);
  }

  /**
   * Get all sections, optionally filtered by admin status
   */
  function getAllSections(isAdmin = false) {
    if (isAdmin) {
      return docSections;
    }
    return docSections.filter((section) => !section.adminOnly);
  }

  /**
   * Clear the search
   */
  function clearSearch() {
    searchQuery.value = '';
    isSearching.value = false;
  }

  return {
    searchQuery,
    isSearching,
    searchResults,
    getSectionsForRoute,
    getSection,
    getAllSections,
    clearSearch,
    docSections,
  };
}

export default useDocumentation;
