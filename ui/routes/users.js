export default [
  {
    path: '/users',
    props: true,
    component: () => import('@/pages/users/Layout'),
    children: [
      {
        path: 'activity',
        name: 'users.activity',
        props: true,
        component: () => import('@/pages/users/UserActivity'),
      },
    ],
  },
];
