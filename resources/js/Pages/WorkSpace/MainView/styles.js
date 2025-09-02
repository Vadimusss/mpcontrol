export const tableClasses = {
  // Контейнер таблицы
  tableContainer: 'overflow-x-auto h-[calc(100vh-65px)] pt-px max-w-full font-roboto-condensed bg-white',

  // Основные стили таблицы
  table: 'w-full border-separate border-spacing-[1px]',
  thead: 'z-40 top-[1px] sticky bg-gray-50 shadow-[0_-2px_0_0_rgba(255,255,255,1)]',
  tbody: '',

  // Заголовки
  mainHeader: 'bg-gray-50 px-1 py-0.5 text-xs font-medium text-center whitespace-nowrap shadow-[0_0_0_1px_rgba(209,213,219,1)]',
  headerFixedCell: 'sticky left-[1px]',
  subHeader: 'bg-gray-50 px-1 py-0.5 text-xs font-medium text-center whitespace-nowrap shadow-[0_0_0_1px_rgba(209,213,219,1)]',
  subHeaderDaysAgo: 'bg-gray-50 px-1 py-0.5 text-xs font-medium text-right whitespace-nowrap shadow-[0_0_0_1px_rgba(209,213,219,1)]',

  // строки
  productRow: 'hover:bg-gray-100 transition-colors duration-200 shadow-[0_-2px_0_0_rgba(0,0,0,1)]',
  row: 'hover:bg-gray-100 transition-colors duration-200',

  // Ячейки
  // notesFixedCell: 'px-1 py-0.5 text-xs whitespace-nowrap shadow-[0_0_0_1px_rgba(209,213,219,1)] bg-[linear-gradient(to_bottom,white_0,white_calc(100%-1px),transparent_calc(100%-1px),transparent_100%)]',
  productFixedCell: 'px-1 py-0.5 text-xs whitespace-nowrap shadow-[0_0_0_1px_rgba(209,213,219,1)] bg-[linear-gradient(to_bottom,white_0,white_calc(100%-1px),transparent_calc(100%-1px),transparent_100%)]',
  fixedCell: 'px-1 py-0.5 text-xs whitespace-nowrap shadow-[0_0_0_1px_rgba(209,213,219,1)] bg-[linear-gradient(to_bottom,white_0,white_calc(100%-1px),transparent_calc(100%-1px),transparent_100%)]',
  cell: 'px-1 py-0.5 text-xs shadow-[0_0_0_1px_rgba(209,213,219,1)]',
  notesCell: 'shadow-[0_0_0_1px_rgba(209,213,219,1)]',
  cellBgGreen: 'bg-lime-200',
  cellBgYellow: 'bg-yellow-200',
  textCell: 'max-w-3xs',
  numbersCell: 'text-right',
  linkCell: 'text-blue-600 hover:underline',

  // Элементы управления
  checkbox: 'h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500',
  expandButton: 'p-1 rounded hover:bg-gray-100',
  icon: 'w-4 h-4'
};

export const columnPropertys = {
  control: 'max-w-min sticky left-[1px]',
  article: 'min-w-[60px] max-w-[60px] text-wrap sticky left-[58px]',
  name: 'min-w-[200px] max-w-[200px] sticky left-[119px]',
  variant: 'min-w-[150px] max-w-[150px] sticky left-[320px]',
  wbArticle: 'min-w-[62px] max-w-[62px] sticky left-[471px]',
  empty: 'min-w-[110px] max-w-[110px] sticky left-[534px]'
};
