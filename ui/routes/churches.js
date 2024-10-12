export default [
  {
    path: '/churches',
    props: true,
    component: () => import('@/layouts/AppLayout'),
    children: [
      {
        path: '',
        name: 'churches.index',
        props: true,
        component: () => import('@/pages/churches'),
      },
    ],
  },
];
