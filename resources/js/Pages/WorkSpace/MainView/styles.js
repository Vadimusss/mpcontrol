export const tableClasses = {
  // Основные стили таблицы
  table: 'border-collapse w-auto',
  thead: 'bg-gray-50',
  tbody: 'bg-white',

  // Заголовки
  mainHeader: 'px-1 py-0.5 text-xs font-medium text-center whitespace-nowrap border border-gray-300',
  subHeader: 'px-1 py-0.5 text-xs font-medium text-left whitespace-nowrap border border-gray-300 min-w-fit',
  
  // Ячейки
  cell: 'px-1 py-0.5 text-xs whitespace-nowrap border border-gray-300 min-w-fit',
  textCell: 'max-w-3xs text-wrap',
  linkCell: 'text-blue-600 hover:underline',

  // Элементы управления
  checkbox: 'h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500',
  expandButton: 'p-1 rounded hover:bg-gray-100',
  icon: 'w-4 h-4'
};

export const columnWidths = {
  article: 'min-w-[120px]',
  name: 'min-w-[200px] max-w-sm',
  variant: 'min-w-[80px]',
  wbArticle: 'min-w-[100px]',
  fg1: 'min-w-[50px]',
  empty: 'min-w-[40px]'
};
