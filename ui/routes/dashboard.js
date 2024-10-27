export default [
    {
        path: '/dashboard',
        name: 'dashboard.index',
        props: true,
        component: () => import('@/pages/dashboard'),
    },
];
