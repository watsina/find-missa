import auth from './auth';
import dashboard from './dashboard';
// import users from './users';
// import parishes from './parishes';
// import churces from './churces';
// import activities from './activities';
// import sub-parishes from './sub-parishes';
// import dioceses from './dioceses';
// import priests from './priests';
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
  // ...dioceses,
  // ...activities,
  // ...parishes,
  // ...churces,
  // ...priests,
  // ...sub-parishes,
  // ...errors,
  // {
  //   path: '*',
  //   component: () => import('@/Errors/Error404'),
  // },
];
