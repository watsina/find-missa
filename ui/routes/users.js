export default [
  {
    path: '/users/@:username',
    props: true,
    component: () => import('@/Pages/Users/Layout'),
    children: [
      {
        path: '',
        name: 'users.show',
        props: true,
        component: () => import('@/Pages/Users/UserProfile'),
      },
      {
        path: 'activity',
        name: 'users.activity',
        props: true,
        component: () => import('@/Pages/Users/UserActivity'),
      },
    ],
  },
];
