import posthog from 'posthog-js';

export function usePostHog() {
  posthog.init('phc_n3jmcTS8DzEcponZiJjXc1ClUQP7yRWduLHKU9Y5qYH', {
    api_host: 'https://us.i.posthog.com',
    capture_pageview: false,
  });

  return {
    posthog,
  };
}
