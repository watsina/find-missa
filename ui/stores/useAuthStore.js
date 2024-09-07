import { defineStore } from 'pinia';
import { http } from '@/plugins';

const auth = window.Auth || {};

export default defineStore('authStore', {
  state: () => {
    return {
      isInitialized: false,
      isFetching: false,
      error: null,
      user: auth.user || {},
      globalPermissions: null,
    };
  },
  getters: {
    hasUser: (state) => !!state.user?.id,
  },
  actions: {
    async fetchGlobalPermissions() {
      if (this.$state.isFetching) {
        return;
      }
      this.$state.isFetching = true;

      return http
        .get(`/api/v1/auth/global-permissions`)
        .then(({ data }) => {
          this.$state.globalPermissions = data;
        })
        .finally(() => {
          this.$state.isFetching = false;
        });
    },
  },
});
