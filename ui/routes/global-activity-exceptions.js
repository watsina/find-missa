export default [
  {
    path: 'global-activity-exceptions',
    name: 'global-activity-exceptions.index',
    props: true,
    component: () => import('@/pages/global-activity-exceptions/Index'),
  },
  // {
  //   path: 'global-activity-exceptions/:exceptionId',
  //   name: 'global-activity-exceptions.show',
  //   props: true,
  //   component: () => import('@/pages/global-activity-exceptions/Show'),
  // },
];
