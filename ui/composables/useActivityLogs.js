import { computed, ref } from 'vue';
import get from 'lodash/get';
import { http } from '@/plugins';
import useAuthStore from '@/stores/useAuthStore';

export const useActivityLogs = () => {
  const logs = ref([]);
  const loading = ref(false);
  const authStore = useAuthStore();
  const authUser = computed(() => authStore.user);

  const descriptions = {
    login: {
      default: 'logged in.',
    },
    activity: {
      default: '{{event.name}} an activity {{event.attributes.name}}',
    },
  };

  const getUserName = (user = null) => {
    if (!user) {
      return '';
    }
    // if user id is same as current user return 'You'
    if (user?.id === authUser.value?.id) {
      return 'You';
    }

    return user?.name;
  };

  const afterAttribute = (log, key) => get(log?.after, key);

  const beforeAttribute = (log, key) => get(log?.before, key);

  const getLogDescription = (log) => {
    const entityType = log?.entity_type;
    const eventName = log?.name;

    const entityDesctiptions = descriptions[entityType];

    const formattedDescription = entityDesctiptions?.[eventName] || entityDesctiptions?.default;

    if (!entityDesctiptions || !formattedDescription) {
      return `${log.name} ${entityType}`;
    }

    //expand formatted description.
    return formattedDescription.replaceAll(
      /{{event.([a-zA-Z0-9-_.]+)}}/g,
      (match, variableName, index) => {
        if (variableName?.startsWith('attributes.')) {
          const attributeName = variableName.substring(11);
          return afterAttribute(log, attributeName) || beforeAttribute(log, attributeName) || '';
        }

        return get(log, variableName, '');
      },
    );
  };

  const fetchActionLogs = () => {
    loading.value = true;
    http
      .get('/api/v1/action-logs')
      .then(({ data }) => {
        logs.value = data?.data?.map((log) => {
          return {
            id: log.id,
            username: getUserName(log?.user),
            description: getLogDescription(log),
            href: '#', // in time this will redirect to the user
            datetime: log?.occurred_at,
            avatar: log?.user?.profile_photo_url,
          };
        });
      })
      .finally(() => (loading.value = false));
  };

  return { logs, fetchActionLogs, loading };
};
