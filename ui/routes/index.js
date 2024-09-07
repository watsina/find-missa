// import users from './users';
import auth from './auth';
import dashboard from './dashboard';
// import errors from './errors';

export default [
  {
    path: '/',
    component: () => import('@/layouts/AppLayout'),
    children: [
      {
        path: '',
        name: 'landing.index',
        props: true,
        component: () => import('@/pages/landing'),
      },
    ],
  },
  ...dashboard,
  ...auth,
  // ...users,
  // ...errors,
  // {
  //   path: '*',
  //   component: () => import('@/Errors/Error404'),
  // },
];
