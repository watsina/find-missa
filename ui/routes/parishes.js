export default [
  {
    path: '/dioceses',
    props: true,
    component: () => import('@/layouts/AppLayout'),
    children: [
      {
        path: '',
        name: 'dioceses.index',
        props: true,
        component: () => import('@/pages/dioceses'),
      },
    ],
  },
];
