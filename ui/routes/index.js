import auth from './auth';
import dashboard from './dashboard';
// import users from './users';
import parishes from './parishes';
import churches from './churches';
import activities from './activities';
import subParishes from './sub-parishes';
import dioceses from './dioceses';
import priests from './priests';
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
  ...dioceses,
  ...activities,
  ...parishes,
  ...churches,
  ...priests,
  ...subParishes,
  // ...errors,
  // {
  //   path: '*',
  //   component: () => import('@/Errors/Error404'),
  // },
];
