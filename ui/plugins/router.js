import { createRouter, createWebHistory } from 'vue-router';
import NProgress from 'nprogress';
import { ensureUserIsAuthorized } from '@/routes/middleware';
import { notify } from '@/utils';
import routes from '@/routes';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
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
