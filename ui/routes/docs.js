export default [
  {
    path: 'docs',
    name: 'docs.index',
    component: () => import('@/pages/docs/Index'),
  },
  {
    path: 'docs/:section',
    name: 'docs.section',
    props: true,
    component: () => import('@/pages/docs/Index'),
  },
];
