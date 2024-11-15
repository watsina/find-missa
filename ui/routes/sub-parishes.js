export default [
  {
    path: 'sub-parishes',
    name: 'sub-parishes.index',
    props: true,
    component: () => import('@/pages/sub-parishes/Index'),
  },
  {
    path: 'sub-parishes/:subParishId',
    name: 'sub-parishes.show',
    props: true,
    component: () => import('@/pages/sub-parishes/Show'),
  },
];
