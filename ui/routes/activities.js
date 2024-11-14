export default [
  {
    path: 'activities',
    name: 'activities.index',
    props: true,
    component: () => import('@/pages/activities/Index'),
  },
  {
    path: 'activities/:activityId',
    name: 'activities.show',
    props: true,
    component: () => import('@/pages/activities/Show'),
  },
];
