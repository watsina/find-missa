import axios from 'axios';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

const instance = axios.create({
  headers: {
    Accept: 'application/json',
  },
  withCredentials: true,
  withXSRFToken: true,
});

instance.getErrorMessageFromResponse = (error) => {
  return error?.response?.data?.message || error?.message || 'Something went wrong';
};

instance.interceptors.request.use(
  function (config) {
    return config;
  },
  function (error) {
    return Promise.reject(error);
  },
);

instance.interceptors.response.use(
  function (response) {
    return response;
  },
  function (error) {
    return Promise.reject(error);
  },
);

export default instance;
