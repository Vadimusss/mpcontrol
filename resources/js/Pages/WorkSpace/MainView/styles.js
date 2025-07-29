export const tableClasses = {
  // Контейнер таблицы
  tableContainer: 'overflow-x-auto h-[calc(100vh-65px)] pt-px max-w-full font-roboto-condensed',

  // Основные стили таблицы
  table: 'w-full border-separate border-spacing-[1px]',
  thead: 'z-40 top-[1px] sticky bg-gray-50 shadow-[0_-2px_0_0_rgba(255,255,255,1)]',
  tbody: '',

  // Заголовки
  mainHeader: 'bg-gray-50 px-1 py-0.5 text-xs font-medium text-center whitespace-nowrap shadow-[0_0_0_1px_rgba(209,213,219,1)]',
  subHeader: 'bg-gray-50 px-1 py-0.5 text-xs font-medium text-left whitespace-nowrap shadow-[0_0_0_1px_rgba(209,213,219,1)]',

  // строки
  row: 'hover:bg-gray-100 transition-colors duration-200',
  
  // Ячейки
  cell: 'px-1 py-0.5 text-xs whitespace-nowrap bg-white shadow-[0_0_0_1px_rgba(209,213,219,1)]',
  notesCell: 'shadow-[0_0_0_1px_rgba(209,213,219,1)]',
  cellBgGreen: 'bg-lime-200',
  cellBgYellow: 'bg-yellow-200',
  textCell: 'max-w-3xs',
  numbersCell: 'text-right',
  linkCell: 'text-blue-600 hover:underline',
  fixedCell: 'sticky left-[1px]',

  // Элементы управления
  checkbox: 'h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500',
  expandButton: 'p-1 rounded hover:bg-gray-100',
  icon: 'w-4 h-4'
};

export const columnPropertys = {
  control: 'max-w-min sticky left-[1px]',
  article: 'w-[80px] text-wrap sticky left-[58px]',
  name: 'w-[200px] sticky left-[104px]',
  variant: 'w-[156px] sticky left-[309px]',
  wbArticle: 'w-[80px] sticky left-[453px]',
  // fg1: 'min-w-fit',
  empty: 'w-[90px] sticky left-[515px]'
};
