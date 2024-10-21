export default [
  {
    path: '/churches',
    name: 'churches.index',
    props: true,
    component: () => import('@/pages/churches'),
  },
];
