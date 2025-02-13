import posthog from 'posthog-js';

export function usePostHog() {
  if (import.meta.env.VITE_POSTHOG_ENABLED === 'true') {
    posthog.init(import.meta.env.VITE_POSTHOG_KEY, {
      api_host: 'https://us.i.posthog.com',
      capture_pageview: false,
    });

    return {
      posthog,
    };
  }

  return {};
}
