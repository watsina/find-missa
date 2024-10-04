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
      features: auth?.features || {},
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
          console.log(data);
          this.user = data;
        })
        .finally(() => (this.isFetching = false));
    },
  },
});
