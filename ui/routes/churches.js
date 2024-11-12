export default [
  {
    path: 'churches',
    name: 'churches.index',
    props: true,
    component: () => import('@/pages/churches/Index'),
  },
  {
    path: 'churches/:churchId',
    name: 'churches.show',
    props: true,
    component: () => import('@/pages/churches/Show.vue'),
  },
];
