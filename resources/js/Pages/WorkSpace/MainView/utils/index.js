import axios from 'axios';
import Cookies from 'js-cookie';
import { viewStore } from '../Stores/ViewStore';

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

const formatValueByType = (value, type, subtype = '', addString = '') => {
  switch (`${type}${subtype}`) {
    case 'advertising_costs':
    case 'orders_profit':
    case 'buyouts_profit':
    case 'profit_without_ads':
    case 'aac_sum':
    case 'auc_sum':
    case 'aac_ctr':
    case 'auc_ctr':
    case 'drr':
      return numericFormatter(value, 1);
    case 'orders_profit_total':
      return numericFormatter(value);
    case 'advertising_costs_percent':
    case 'orders_profit_percent':
    case 'no_ad_clicks_percent':
    case 'aac_views_percent':
    case 'aac_clicks_percent':
    case 'aac_sum_percent':
    case 'aac_orders_percent':
    case 'aac_cpo_percent':
    case 'auc_views_percent':
    case 'auc_clicks_percent':
    case 'auc_sum_percent':
    case 'auc_orders_percent':
    case 'ad_orders_percent':
    case 'no_ad_orders_percent':
      return numericFormatter(value, 0, addString);
    default:
      return numericFormatter(value);
  }
};

const checkOverflow = (element, text, minTextLength) => {
  if (!element || !text) return false;

  if (text.length < minTextLength) return false;
  if (!isNaN(parseFloat(text)) && isFinite(text)) return false;
  if (/^\d{4}[-\/]\d{2}[-\/]\d{2}/.test(text)) return false;

  return element.scrollWidth > element.clientWidth + 2;
};

const generateDateHeaders = (days) => {
  const result = [];
  const currentDate = new Date();

  for (let i = 0; i < days; i++) {
    const date = new Date(currentDate);
    date.setDate(currentDate.getDate() - i);

    const dateString = date.toISOString().split("T")[0];

    result.push(dateString);
  }

  return result.reverse();
};

const calculateCategorysTotalsFromGoods = (goods) => {
  if (goods.length === 0) return {};

  const firstGood = goods[0];
  const dates = firstGood.ordersSumRubByDate ? Object.keys(firstGood.ordersSumRubByDate) : [];
  if (dates.length === 0) return {};

  const result = {};
  const totals = {};
  const superCategory = 'Все товары';
  
  totals[superCategory] = {
    profit_without_ads: 0,
    advertising_costs: 0,
    orders_sum_rub: 0,
  };
  
  result[superCategory] = {};

  dates.forEach(date => {
    result[superCategory][date] = {
      profit_without_ads: 0,
      advertising_costs: 0,
      orders_sum_rub: 0,
    };
  });

  goods.forEach(good => {
    const category = good.category || 'Без категории';
    const categoryData = good.ordersSumRubByDate;
    
    if (!categoryData) return;

    if (!totals[category]) {
      totals[category] = {
        profit_without_ads: 0,
        advertising_costs: 0,
        orders_sum_rub: 0,
      };
      result[category] = {};
      
      dates.forEach(date => {
        result[category][date] = {
          profit_without_ads: 0,
          advertising_costs: 0,
          orders_sum_rub: 0,
        };
      });
    }

    const categoryTotals = totals[category];
    const superTotals = totals[superCategory];
    const categoryResult = result[category];
    const superResult = result[superCategory];

    for (let i = 0; i < dates.length; i++) {
      const date = dates[i];
      
      const ordersSumRub = parseFloat(categoryData[date]) || 0;
      if (ordersSumRub === 0) continue;
      
      const advertisingCosts = parseFloat(good.advertisingCostsByDate?.[date]) || 0;
      const profitWithoutAds = parseFloat(good.profitWithoutAdsByDate?.[date]) || 0;

      const categoryDayData = categoryResult[date];
      categoryDayData.profit_without_ads += profitWithoutAds;
      categoryDayData.advertising_costs += advertisingCosts;
      categoryDayData.orders_sum_rub += ordersSumRub;

      const superDayData = superResult[date];
      superDayData.profit_without_ads += profitWithoutAds;
      superDayData.advertising_costs += advertisingCosts;
      superDayData.orders_sum_rub += ordersSumRub;

      categoryTotals.profit_without_ads += profitWithoutAds;
      categoryTotals.advertising_costs += advertisingCosts;
      categoryTotals.orders_sum_rub += ordersSumRub;

      superTotals.profit_without_ads += profitWithoutAds;
      superTotals.advertising_costs += advertisingCosts;
      superTotals.orders_sum_rub += ordersSumRub;
    }
  });

  Object.keys(result).forEach(category => {
    const categoryResult = result[category];
    const categoryTotals = totals[category];
    
    dates.forEach(date => {
      const dayData = categoryResult[date];
      if (dayData.orders_sum_rub > 0) {
        dayData.drr = dayData.advertising_costs / dayData.orders_sum_rub;
      } else {
        dayData.drr = 0;
      }
    });

    if (categoryTotals.orders_sum_rub > 0) {
      categoryTotals.drr = categoryTotals.advertising_costs / categoryTotals.orders_sum_rub;
    } else {
      categoryTotals.drr = 0;
    }

    categoryResult.total = categoryTotals;
  });

  return result;
};

const getCategoryRows = (dates, categorysTotalsData, goods) => {
  const dataToUse = goods ? calculateCategorysTotalsFromGoods(goods) : categorysTotalsData;
  
  if (!dataToUse || Object.keys(dataToUse).length === 0) {
    return [];
  }

  const rows = [];
  const categories = Object.keys(dataToUse).filter(categoryName => {
    return viewStore.shouldDisplayCategory(categoryName);
  });
  const metrics = [
    { key: 'orders_sum_rub', label: 'ОП руб' },
    { key: 'advertising_costs', label: 'Реклама' },
    { key: 'drr', label: 'ДРР %' },
    { key: 'profit_without_ads', label: 'Прибыль' },
  ];

  categories.forEach((categoryName, categoryIndex) => {
    const categoryData = dataToUse[categoryName];
    const totalData = categoryData.total || {};

    metrics.forEach((metric, metricIndex) => {
      const row = {
        id: `category-${categoryName}-${metric.key}`,
        type: 'category_row',
        category: categoryName,
        metric: metric.label,
        metricKey: metric.key,
        dates: {},
        total: totalData[metric.key] || 0,
        isFirstMetric: metricIndex === 0,
        isFirstCategory: categoryIndex === 0 && metricIndex === 0
      };

      dates.forEach(date => {
        const dayData = categoryData[date] || {};
        row.dates[date] = dayData[metric.key] || 0;
      });

      rows.push(row);
    });

    if (categoryIndex < categories.length) {
      rows.push({
        id: `separator-${categoryName}`,
        type: 'category_separator'
      });
    }
  });

  return rows;
};

export {
  apiClient,
  numericFormatter,
  stylingFormatter,
  checkOverflow,
  generateDateHeaders,
  formatValueByType,
  getCategoryRows,
  calculateCategorysTotalsFromGoods
};
