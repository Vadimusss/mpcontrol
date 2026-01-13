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

const formatter = (value, fractionDigits = 0) => {
  if (value === '' || value === null || value === undefined || value === 0) return '';
  if (fractionDigits === 0 && value < 1) return '';
  if (typeof value === 'string') return value;

  const options = {
    minimumFractionDigits: fractionDigits,
    maximumFractionDigits: fractionDigits,
    useGrouping: true
  };

  return new Intl.NumberFormat('ru-RU', options).format(value);
};

export { apiClient, formatter };
