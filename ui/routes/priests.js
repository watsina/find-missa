export default [
  {
    path: '/priests',
    props: true,
    component: () => import('@/layouts/AppLayout'),
    children: [
      {
        path: '',
        name: 'priests.index',
        props: true,
        component: () => import('@/pages/priests'),
      },
    ],
  },
];
