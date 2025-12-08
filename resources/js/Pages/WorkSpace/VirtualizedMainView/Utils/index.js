import axios from 'axios';
import Cookies from 'js-cookie';

const apiClient = axios.create({
  baseURL: '/api',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'X-XSRF-TOKEN': Cookies.get('XSRF-TOKEN'),
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  withCredentials: true
});

apiClient.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 419) {
      window.location.reload();
    }
    return Promise.reject(error);
  }
);

export { apiClient };
