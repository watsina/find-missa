import { ref, watch, computed } from 'vue';
import { toValue } from '@vueuse/core';
import { useAxios } from '@vueuse/integrations/useAxios';
import notify from './notify';
import http from '@/plugins/http';

export function useHttpGet(url, options = {}) {
  const response = useHttpWithCallbacks(url, options);

  response.get(url, options);

  watch(
    () => url,
    () => response.get(url, options),
  );

  response.refresh = (newOptions) => response.get(Object.assign(options, newOptions));

  return response;
}

export function usePaginatedHttp(url, options = {}) {
  const { data, isBusy, get } = useHttpWithCallbacks(url);

  get(url, options);

  const items = computed(() => data.value?.data || []);

  return {
    items,
    isBusy,
  };
}

export function useHttpWithCallbacks(url, options = {}) {
  const data = ref(options?.defaultValue || {});

  const error = ref(null);

  const isBusy = ref(false);

  const get = (options) => submit('get', options);
  const put = (options) => submit('put', options);
  const post = (options) => submit('post', options);
  const destroy = (options) => submit('delete', options);

  const submit = async (method, submitOptions = {}) => {
    async function triggerCallback(name, payload = undefined) {
      if (options[name]) {
        await options[name](payload);
      }
    }
    if (isBusy.value) {
      return;
    }

    isBusy.value = true;

    triggerCallback('onStart');

    const { default: http } = await import('@/plugins/http');

    error.value = null;

    http[method](url, options)
      .then(({ data: apiResponse }) => {
        data.value = apiResponse || options.defaultValue;
        triggerCallback('onFinish', data);
      })
      .catch((responseError) => {
        error.value = responseError.response || responseError;

        triggerCallback('onError', error.value);

        if (!options.onError) {
          return Promise.reject(responseError);
        }
      })
      .finally(() => {
        isBusy.value = false;
        triggerCallback('onFinish');
      });
  };

  return {
    data,
    error,
    get,
    post,
    put,
    destroy,
    isBusy,
  };
}

export function useHttp(url, givenOptions = {}) {
  const { disableNotifications, config = {}, ...options } = givenOptions;

  if (!Object.prototype.hasOwnProperty.call(config, 'immediate')) {
    config['immediate'] = true;
  }

  const response = useAxios(toValue(url), options, http, config);

  watch(response.error, (error) => {
    if (disableNotifications === true || error?.code === 'ERR_CANCELED') {
      return;
    }

    notify.error(
      `Something went wrong while making a request to ${url} error: ${
        error?.response?.data?.message || error.message
      }`,
    );
  });

  return response;
}
