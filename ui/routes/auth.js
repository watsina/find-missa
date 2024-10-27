export default [
  {
    path: '/auth/login',
    name: 'auth.login',
    component: () => import('@/pages/auth/Login'),
  },
  {
    path: '/auth/forgot-password',
    name: 'auth.forgot-password',
    component: () => import('@/pages/auth/ForgotPassword'),
  },
  {
    path: '/auth/reset-password/:token*',
    name: 'auth.reset-password',
    component: () => import('@/pages/auth/ResetPassword'),
  },
  {
    path: '/auth/two-factor-challenge',
    name: 'auth.two-factor-challenge',
    component: () => import('@/pages/auth/TwoFactorChallenge'),
  },
  {
    path: '/auth/profile',
    name: 'user.profile',
    component: () => import('@/pages/profile/Show'),
  },
];
