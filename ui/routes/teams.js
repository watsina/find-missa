export default [
  {
    path: 'teams/:teamId',
    name: 'teams.show',
    props: true,
    component: () => import('@/pages/teams/Show'),
  },
  {
    path: 'teams/create',
    name: 'teams.create',
    props: true,
    component: () => import('@/pages/teams/Create'),
  },
];
