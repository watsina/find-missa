import { createRouter, createWebHistory } from 'vue-router';
import NProgress from 'nprogress';
import { ensureUserIsAuthorized } from '@/routes/middleware';
import { notify } from '@/utils';
import routes from '@/routes';
import { usePostHog } from '@/composables/usePostHog';

const router = createRouter({
  history: createWebHistory(),
  routes,
});

const { posthog } = usePostHog();

router.beforeEach((to, from, next) => {
  NProgress.start();

  if (from.path !== to.path) {
    posthog.capture('$pageleave');
  }

  ensureUserIsAuthorized(to, from, next);
});

router.afterEach(() => {
  posthog.capture('$pageview');
  NProgress.done();
  notify.close();
});

export default router;
