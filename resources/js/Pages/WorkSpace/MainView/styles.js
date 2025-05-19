export const tableClasses = {
  // Основные стили таблицы
  table: 'w-full border-separate border-spacing-[1px] bg-gray-300',
  thead: 'z-40 top-0 sticky table-head-with-gaps',
  tbody: '',

  // Заголовки
  mainHeader: 'bg-gray-50 px-1 py-0.5 text-xs font-medium text-center whitespace-nowrap',
  subHeader: 'bg-gray-50 px-1 py-0.5 text-xs font-medium text-left whitespace-nowrap',

  // строки
  row: 'hover:bg-gray-100 transition-colors duration-200 bg-white',
  
  // Ячейки
  cell: 'px-1 py-0.5 text-xs whitespace-nowrap',
  textCell: 'max-w-3xs',
  linkCell: 'text-blue-600 hover:underline',

  // Элементы управления
  checkbox: 'h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500',
  expandButton: 'p-1 rounded hover:bg-gray-100',
  icon: 'w-4 h-4'
};

export const columnWidths = {
  control: 'max-w-min',
  article: 'w-[80px] text-wrap',
  name: 'min-w-fit',
  variant: 'min-w-fit',
  wbArticle: 'min-w-[80px]',
  fg1: 'min-w-fit',
  empty: 'min-w-[90px]'
};
