import auth from './auth';
import dashboard from './dashboard';
import users from './users';
import teams from './teams';
import parishes from './parishes';
import churches from './churches';
import activities from './activities';
import subParishes from './sub-parishes';
import dioceses from './dioceses';
import priests from './priests';
import errors from './errors';

export default [
  // backoffice routes
  {
    path: '/backoffice',
    children: [
      ...dashboard,
      ...auth,
      ...users,
      ...teams,
      ...dioceses,
      ...activities,
      ...parishes,
      ...churches,
      ...priests,
      ...subParishes,
    ],
  },
  ...errors,
  {
    path: '/:catchAll(.*)',
    component: () => import('@/errors/Error404'),
  },
];
