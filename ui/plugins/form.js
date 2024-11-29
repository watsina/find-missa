/**
 * Modified from inertia
 * @see https://github.com/inertiajs/inertia/blob/master/packages/vue3/src/useForm.ts
 */
import http from '@/plugins/http';
import cloneDeep from 'lodash/cloneDeep';
import isEqual from 'lodash/isEqual';
import { reactive, watch } from 'vue';

export default function useForm(...args) {
  const data = (typeof args[0] === 'string' ? args[1] : args[0]) || {};

  let defaults = typeof data === 'object' ? cloneDeep(data) : cloneDeep(data());
  let cancelToken = null;
  let recentlySuccessfulTimeoutId = null;
  let transform = (data) => data;

  const form = reactive({
    ...data,
    isDirty: false,
    errors: {},
    hasErrors: false,
    processing: false,
    progress: null,
    wasSuccessful: false,
    recentlySuccessful: false,
    data() {
      return Object.keys(defaults).reduce((carry, key) => {
        carry[key] = this[key];
        return carry;
      }, {});
    },
    transform(callback) {
      transform = callback;

      return this;
    },
    defaults(fieldOrFields, maybeValue) {
      if (typeof data === 'function') {
        throw new Error(
          'You cannot call `defaults()` when using a function to define your form data.',
        );
      }

      if (typeof fieldOrFields === 'undefined') {
        defaults = this.data();
      } else {
        defaults = Object.assign(
          {},
          cloneDeep(defaults),
          typeof fieldOrFields === 'string' ? { [fieldOrFields]: maybeValue } : fieldOrFields,
        );
      }

      return this;
    },
    reset(...fields) {
      const resolvedData = typeof data === 'object' ? cloneDeep(defaults) : cloneDeep(data());
      const clonedData = cloneDeep(resolvedData);
      if (fields.length === 0) {
        defaults = clonedData;
        Object.assign(this, resolvedData);
      } else {
        Object.keys(resolvedData)
          .filter((key) => fields.includes(key))
          .forEach((key) => {
            defaults[key] = clonedData[key];
            this[key] = resolvedData[key];
          });
      }

      return this;
    },
    setError(fieldOrFields, maybeValue) {
      Object.assign(
        this.errors,
        typeof fieldOrFields === 'string' ? { [fieldOrFields]: maybeValue } : fieldOrFields,
      );

      this.hasErrors = Object.keys(this.errors).length > 0;

      return this;
    },
    clearErrors(...fields) {
      this.errors = Object.keys(this.errors).reduce(
        (carry, field) => ({
          ...carry,
          ...(fields.length > 0 && !fields.includes(field) ? { [field]: this.errors[field] } : {}),
        }),
        {},
      );

      this.hasErrors = Object.keys(this.errors).length > 0;

      return this;
    },
    submit(method, url, options = {}) {
      const data = transform(this.data());
      const _options = {
        ...options,
        onCancelToken: (token) => {
          cancelToken = token;

          if (options.onCancelToken) {
            return options.onCancelToken(token);
          }
        },
        onBefore: (visit) => {
          this.wasSuccessful = false;
          this.recentlySuccessful = false;
          clearTimeout(recentlySuccessfulTimeoutId);

          if (options.onBefore) {
            return options.onBefore(visit);
          }
        },
        onStart: (visit) => {
          this.processing = true;

          if (options.onStart) {
            return options.onStart(visit);
          }
        },
        onProgress: (event) => {
          this.progress = event;

          if (options.onProgress) {
            return options.onProgress(event);
          }
        },
        onSuccess: async (page) => {
          this.processing = false;
          this.progress = null;
          this.clearErrors();
          this.wasSuccessful = true;
          this.recentlySuccessful = true;
          recentlySuccessfulTimeoutId = setTimeout(() => (this.recentlySuccessful = false), 2000);

          const onSuccess = options.onSuccess ? await options.onSuccess(page) : null;
          defaults = cloneDeep(this.data());
          this.isDirty = false;
          return onSuccess;
        },
        onError: (errors) => {
          this.processing = false;
          this.progress = null;
          this.clearErrors().setError(errors);

          if (options.onError) {
            return options.onError(errors);
          }
        },
        onCancel: () => {
          this.processing = false;
          this.progress = null;

          if (options.onCancel) {
            return options.onCancel();
          }
        },
        onFinish: (visit) => {
          this.processing = false;
          this.progress = null;
          cancelToken = null;

          if (options.onFinish) {
            return options.onFinish(visit);
          }
        },
      };

      _options.onStart();

      let request;

      if (method === 'delete') {
        request = http.delete(url, { ..._options, data });
      } else if (method === 'get') {
        request = http.get(url, { params: { ...data }, ..._options });
      } else {
        request = http[method](url, data, _options);
      }

      request
        .then((response) => {
          _options.onSuccess(response.data, response);
        })
        .catch((error) => {
          const response = error?.response || {};

          _options.onError(response.data?.errors, error);
        })
        .finally(() => _options.onFinish());
    },
    get(url, options) {
      this.submit('get', url, options);
    },
    post(url, options) {
      this.submit('post', url, options);
    },
    put(url, options) {
      this.submit('put', url, options);
    },
    patch(url, options) {
      this.submit('patch', url, options);
    },
    delete(url, options) {
      this.submit('delete', url, options);
    },
    cancel() {
      if (cancelToken) {
        cancelToken.cancel();
      }
    },
    __remember() {
      return { data: this.data(), errors: this.errors };
    },
    __restore(restored) {
      Object.assign(this, restored.data);
      this.setError(restored.errors);
    },
  });

  watch(
    form,
    () => {
      form.isDirty = !isEqual(form.data(), defaults);
    },
    { immediate: true, deep: true },
  );

  return form;
}
