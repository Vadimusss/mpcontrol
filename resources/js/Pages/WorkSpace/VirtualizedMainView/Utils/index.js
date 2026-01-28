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

const numericFormatter = (value, fractionDigits = 0, addString = '') => {
  if (value === '' || value === null || value === undefined || value === 0) return '';
  if (fractionDigits === 0 && value < 1) return '';
  if (typeof value === 'string') return value;

  const options = {
    minimumFractionDigits: fractionDigits,
    maximumFractionDigits: fractionDigits,
    useGrouping: true
  };

  return `${new Intl.NumberFormat('ru-RU', options).format(value)}${addString}`;
};

const stylingFormatter = {
  getStaticClass: (rowType) => {
    const staticClasses = {
      'orders_count': 'font-large font-bold bg-gray',
      'advertising_costs': '',
      'orders_profit': 'font-bold',
      'price_with_disc': '',
      'finished_price': '',
      'spp': 'text-gray italic',
      'orders_sum_rub': '',
      'orders_sum_rub_after_spp': '',
      'drr_common': 'text-gray italic',
      'buyouts_sum_rub': '',
      'buyout_percent': 'text-gray italic',
      'profit_without_ads': '',
      'open_card_count': 'text-green',
      'no_ad_clicks': 'text-green',
      'add_to_cart_count': 'text-green',
      'add_to_cart_conversion': 'text-green',
      'cart_to_order_conversion': 'text-green',
      'aac_cpm': 'text-blue',
      'aac_views': 'text-blue',
      'aac_clicks': 'text-blue',
      'aac_sum': 'text-blue',
      'aac_orders': 'text-blue',
      'aac_ctr': 'text-blue',
      'aac_cpo': 'text-blue',
      'auc_cpm': 'text-darkred',
      'auc_views': 'text-darkred',
      'auc_clicks': 'text-darkred',
      'auc_sum': 'text-darkred',
      'auc_orders': 'text-darkred',
      'auc_ctr': 'text-darkred',
      'auc_cpo': 'text-darkred',
      'ad_orders': '',
      'no_ad_orders': '',
      'assoc_orders_from_other': '',
      'assoc_orders_from_this': '',
    };
    return staticClasses[rowType] || '';
  },

  checkDynamicConditions: (rowType, value, advertisingCosts) => {
    const classes = [];

    if (rowType === 'orders_count' && advertisingCosts * 1000 > 100) {
      classes.push('bg-yellow');
    }

    if (rowType === 'advertising_costs' && value * 1000 > 100) {
      classes.push('bg-yellow');
    }

    if (rowType === 'aac_sum' && value * 1000 > 100) {
      classes.push('bg-yellow');
    }

    if (rowType === 'auc_sum' && value * 1000 > 100) {
      classes.push('bg-yellow');
    }

    return classes.join(' ');
  }
};

export { apiClient, numericFormatter, stylingFormatter };
