export default [
  {
    path: 'holidays',
    name: 'holidays.index',
    props: true,
    component: () => import('@/pages/holidays/Index'),
  },
  {
    path: 'holidays/calendar',
    name: 'holidays.instances',
    props: true,
    component: () => import('@/pages/holidays/Instances'),
  },
];
