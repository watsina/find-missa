export default [
  {
    path: 'users',
    name: 'users.index',
    props: true,
    component: () => import('@/pages/users/Index'),
  },
];
