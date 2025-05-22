import axios from 'axios';
import Cookies from 'js-cookie';

export const useApiClient = () => {
  const instance = axios.create({
    baseURL: '/api',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-XSRF-TOKEN': Cookies.get('XSRF-TOKEN'),
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    withCredentials: true
  });

  instance.interceptors.response.use(
    response => response,
    error => {
      if (error.response && error.response.status === 419) {
        window.location.reload();
      }
      return Promise.reject(error);
    }
  );

  return instance;
};
