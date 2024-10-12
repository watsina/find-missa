export default [
  {
    path: '/sub-parishes',
    props: true,
    component: () => import('@/layouts/AppLayout'),
    children: [
      {
        path: '',
        name: 'sub-parishes.index',
        props: true,
        component: () => import('@/pages/sub-parishes'),
      },
    ],
  },
];
