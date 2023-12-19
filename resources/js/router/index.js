// Composables
import NProgress from 'nprogress';
import { createRouter, createWebHistory } from 'vue-router';

const routes = [
  {
    path: '/',
    // component: () => import('@/layouts/default/Default.vue'),
    children: [
      {
        path: '',
        name: 'Home',
        // route level code-splitting
        // this generates a separate chunk (Home-[hash].js) for this route
        // which is lazy-loaded when the route is visited.
        component: () => import('@/app/home.vue'),
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

router.beforeEach((...args) => {
  NProgress.start();
  // ensureUserIsAuthenticated(...args)
});

router.afterEach(() => {
  NProgress.done();
  // notify.close()
});

export default router;
