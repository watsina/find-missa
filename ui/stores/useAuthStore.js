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
    updateUser(newData) {
      if (!newData) {
        return;
      }
      console.log('setting user value...');
      this.user = newData;
    },
    async fetchGlobalPermissions() {
      if (this.isFetching) {
        return;
      }
      this.isFetching = true;

      return http
        .get(`/api/v1/auth/global-permissions`)
        .then(({ data }) => {
          this.globalPermissions = data;
        })
        .finally(() => {
          this.isFetching = false;
        });
    },
  },
});
