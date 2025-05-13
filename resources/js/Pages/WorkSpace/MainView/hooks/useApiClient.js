import axios from 'axios';
import Cookies from 'js-cookie';

export const useApiClient = () => {
  return axios.create({
    baseURL: '/api',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-XSRF-TOKEN': Cookies.get('XSRF-TOKEN')
    },
    withCredentials: true
  });
};
