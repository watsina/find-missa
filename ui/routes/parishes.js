export default [
  {
    path: 'parishes',
    name: 'parishes.index',
    props: true,
    component: () => import('@/pages/parishes/Index'),
  },
  {
    path: 'parishes/:parishId',
    name: 'parishes.show',
    props: true,
    component: () => import('@/pages/parishes/Show'),
  },
];
