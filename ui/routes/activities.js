export default [
  {
    path: '/activities',
    props: true,
    component: () => import('@/layouts/AppLayout'),
    children: [
      {
        path: '',
        name: 'activities.index',
        props: true,
        component: () => import('@/pages/activities'),
      },
    ],
  },
];
