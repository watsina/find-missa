export default [
  {
    path: '/',
    name: 'home',
    props: true,
    component: () => import('@/pages/app-users/Index'),
    meta: {
      requiresAuth: false,
    },
  },
  {
    path: '/church-details/:churchId',
    name: 'church.details',
    props: true,
    component: () => import('@/pages/app-users/ShowChurch'),
    meta: {
      requiresAuth: false,
    },
  },
];
