export default [
  {
    path: '/roles-permissions',
    name: 'acl.index',
    props: true,
    component: () => import('@/pages/acl/RolesPermissions'),
  },
];
