import useAuthStore from '@/stores/useAuthStore';

export async function ensureUserIsAuthorized(to, from, next) {
  if (['home.dashboard', 'errors.404', 'errors.403'].includes(to.name)) {
    return next();
  }

  const store = useAuthStore();

  const isNavigatingToLogin = to.name === 'auth.login';

  if (isNavigatingToLogin && store.hasUser) {
    return next({ name: 'dashboard.index' });
  }

  if (!isNavigatingToLogin && !store.hasUser) {
    return next({ name: 'auth.login' });
  }

  return next();
}
