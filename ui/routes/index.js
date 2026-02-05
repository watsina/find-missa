import appUsers from './app-users';
import auth from './auth';
import dashboard from './dashboard';
import users from './users';
import teams from './teams';
import acl from './acl';
import parishes from './parishes';
import churches from './churches';
import activities from './activities';
import globalExceptions from './global-activity-exceptions';
import holidays from './holidays';
import schedulePeriods from './schedule-periods';
import subParishes from './sub-parishes';
import dioceses from './dioceses';
import priests from './priests';
import docs from './docs';
import errors from './errors';

export default [
  // app user routes (guest)
  ...appUsers,
  // backoffice routes
  {
    path: '/backoffice',
    children: [
      ...dashboard,
      ...auth,
      ...users,
      ...teams,
      ...acl,
      ...dioceses,
      ...activities,
      ...globalExceptions,
      ...holidays,
      ...schedulePeriods,
      ...parishes,
      ...churches,
      ...priests,
      ...subParishes,
      ...docs,
    ],
  },
  ...errors,
  {
    path: '/:catchAll(.*)',
    name: 'NotFound',
    component: () => import('@/errors/Error404'),
  },
];
