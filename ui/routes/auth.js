export default [
  {
    path: '/auth/login',
    name: 'auth.login',
    component: () => import('@/pages/auth/Login'),
  },
  {
    path: '/auth/profile',
    name: 'user.profile',
    component: () => import('@/pages/profile/Show'),
  },
];
