import posthog from 'posthog-js';

export function usePostHog() {
  if (import.meta.env.VITE_POSTHOG_ENABLED === 'true') {
    posthog.init(import.meta.env.VITE_POSTHOG_KEY, {
      api_host: import.meta.env.VITE_POSTHOG_HOST,
      capture_pageview: false,
    });

    return {
      posthog,
    };
  }

  return {};
}
