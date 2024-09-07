export default [
  { path: '/errors/403', component: () => import('@/Errors/Error403'), name: 'errors.403' },
  { path: '/errors/404', component: () => import('@/Errors/Error404'), name: 'errors.404' },
];
