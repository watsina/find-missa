export default [
  {
    path: 'dioceses',
    name: 'dioceses.index',
    props: true,
    component: () => import('@/pages/dioceses/Index'),
  },
  {
    path: 'dioceses/:dioceseId',
    name: 'dioceses.show',
    props: true,
    component: () => import('@/pages/dioceses/Show'),
  },
];
