/**
 * Форматирование чисел по русскому стандарту (# ###,##)
 */

/**
 * Форматирует число с разделителями тысяч и указанным количеством знаков после запятой
 * @param {number|string} value - Число для форматирования
 * @param {Object} options - Опции форматирования
 * @param {number} options.minimumFractionDigits - Минимальное количество знаков после запятой (по умолчанию 0)
 * @param {number} options.maximumFractionDigits - Максимальное количество знаков после запятой (по умолчанию 2)
 * @param {boolean} options.useGrouping - Использовать разделитель тысяч (по умолчанию true)
 * @returns {string} Отформатированное число или исходное значение, если не число
 */
export const formatNumber = (value, options = {}) => {
  if (value === null || value === undefined || value === '') return '';
  if (typeof value === 'string' && value.trim() === '') return '';
  
  // Пытаемся преобразовать в число
  let num;
  if (typeof value === 'string') {
    // Заменяем запятую на точку для корректного парсинга
    const cleaned = value.replace(',', '.').replace(/\s/g, '');
    num = parseFloat(cleaned);
  } else {
    num = value;
  }
  
  if (isNaN(num)) return String(value);
  
  const {
    minimumFractionDigits = 0,
    maximumFractionDigits = 2,
    useGrouping = true
  } = options;
  
  return new Intl.NumberFormat('ru-RU', {
    minimumFractionDigits,
    maximumFractionDigits,
    useGrouping
  }).format(num);
};

/**
 * Форматирует целое число с разделителями тысяч
 * @param {number|string} value - Число для форматирования
 * @returns {string} Отформатированное целое число
 */
export const formatInteger = (value) => formatNumber(value, { 
  minimumFractionDigits: 0, 
  maximumFractionDigits: 0 
});

/**
 * Форматирует число как валюту (2 знака после запятой)
 * @param {number|string} value - Число для форматирования
 * @returns {string} Отформатированная валюта
 */
export const formatCurrency = (value) => formatNumber(value, { 
  minimumFractionDigits: 2, 
  maximumFractionDigits: 2 
});

/**
 * Форматирует число как проценты (1-2 знака после запятой)
 * @param {number|string} value - Число для форматирования
 * @returns {string} Отформатированный процент
 */
export const formatPercent = (value) => {
  if (value === null || value === undefined || value === '') return '';
  const num = typeof value === 'string' ? parseFloat(value.replace(',', '.')) : value;
  if (isNaN(num)) return String(value);
  return new Intl.NumberFormat('ru-RU', {
    minimumFractionDigits: 1,
    maximumFractionDigits: 2
  }).format(num) + '%';
};

/**
 * Форматирует число с 1 знаком после запятой
 * @param {number|string} value - Число для форматирования
 * @returns {string} Отформатированное число с 1 знаком после запятой
 */
export const formatOneDecimal = (value) => formatNumber(value, {
  minimumFractionDigits: 1,
  maximumFractionDigits: 1
});

/**
 * Форматирует число для отображения в таблице (адаптивно)
 * Определяет тип числа по контексту и форматирует соответствующим образом
 * @param {number|string} value - Значение для форматирования
 * @param {string} type - Тип значения (например, 'price', 'count', 'percent', 'decimal1')
 * @returns {string} Отформатированное значение
 */
export const formatTableValue = (value, type = 'auto') => {
  if (value === null || value === undefined || value === '') return '';
  
  switch (type) {
    case 'price':
    case 'currency':
      return formatCurrency(value);
    case 'percent':
      return formatPercent(value);
    case 'integer':
    case 'count':
      return formatInteger(value);
    case 'decimal1':
      return formatOneDecimal(value);
    case 'auto':
    default:
      // Автоматическое определение
      if (typeof value === 'number') {
        if (Number.isInteger(value)) {
          return formatInteger(value);
        } else {
          return formatNumber(value);
        }
      }
      return String(value);
  }
};
