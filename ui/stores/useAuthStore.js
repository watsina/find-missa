import { defineStore } from 'pinia';
import { http } from '@/plugins';

const auth = window.Auth || {};

export default defineStore('authStore', {
  state: () => {
    return {
      isInitialized: false,
      isFetching: false,
      error: null,
      user: auth?.user || {},
      current_team: auth?.current_team || {},
      permissions: auth?.permissions || [],
      all_teams: Object.values(auth?.all_teams || []) || [],
      features: auth?.features || {},
    };
  },
  getters: {
    hasUser: (state) => !!state.user?.id,
  },
  actions: {
    featureIsEnabled(namespace, feature) {
      return this.features[namespace]['features']?.includes(feature);
    },
    featureOptionIsEnabled(namespace, feature, option) {
      return !!this.features[namespace]['options'][feature][option];
    },
    hasPermission(permission) {
      if (this.user?.is_admin) {
        return true;
      }

      if (!permission || !this.permissions.length) {
        return false;
      }

      return this.permissions.includes(permission);
    },
    updateUser(newData) {
      if (!newData) {
        return;
      }

      this.user = newData;
    },
    async refreshUser() {
      if (this.isFetching) {
        return;
      }
      this.isFetching = true;
      return http
        .get('/api/v1/auth/user')
        .then(({ data }) => {
          this.user = data;
        })
        .finally(() => (this.isFetching = false));
    },
  },
});
