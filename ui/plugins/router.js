import VueRouter from 'vue-router';

import Vue from 'vue';

import NProgress from 'nprogress';

import { ensureUserIsAuthorized } from '@/routes/middleware';

import notify from '@/utils/notify';

import routes from '@/routes';

Vue.use(VueRouter);

const router = new VueRouter({
  mode: 'history',

  routes,
});

router.beforeEach((...args) => {
  NProgress.start();
  ensureUserIsAuthorized(...args);
});

router.afterEach(() => {
  NProgress.done();
  notify.close();
});

export default router;
