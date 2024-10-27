import useAuthStore from '@/stores/useAuthStore';

export async function ensureUserIsAuthorized(to, from, next) {
  if (['home.dashboard', 'errors.404', 'errors.403'].includes(to.name)) {
    return next();
  }

  const store = useAuthStore();

  const isNavigatingToLogin = to.name === 'auth.login';
  const isNavigatingToTwoFactor = to.name === 'auth.two-factor-challenge';
  const isNavigatingToForgotPassword = to.name === 'auth.forgot-password';
  const isNavigatingToResetPassword = to.name === 'auth.reset-password';

  const isNonAuthRoute =
    isNavigatingToTwoFactor ||
    isNavigatingToLogin ||
    isNavigatingToForgotPassword ||
    isNavigatingToResetPassword;

  console.info({ isNavigatingToForgotPassword, isNonAuthRoute });
  if (isNonAuthRoute && store.hasUser) {
    return next({ name: 'dashboard.index' });
  }

  if (!isNonAuthRoute && !store.hasUser) {
    return next({ name: 'auth.login' });
  }

  return next();
}
