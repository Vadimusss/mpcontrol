import { useMemo, useState, useEffect } from 'react';
import {
  useReactTable,
  getCoreRowModel,
  getSortedRowModel,
  getFilteredRowModel,
  flexRender,
} from '@tanstack/react-table';

import {
  ArrowsUpDownIcon,
  BarsArrowUpIcon,
  BarsArrowDownIcon,
} from '@heroicons/react/24/solid';

export default function GoodsTable({ goods, selectedGoodsId, setSelectedGoodsId }) {
  // console.log(goods);

  const [rowSelection, setRowSelection] = useState({});
  const [sorting, setSorting] = useState([]);
  const [columnFilters, setColumnFilters] = useState([]);

  const columns = useMemo(
    () => [
      {
        accessorKey: 'nm_id',
        header: 'nm_id',
        filterFn: (row, columnId, filterValue) => {
          const rowValue = row.getValue(columnId);
          return String(rowValue).includes(String(filterValue));
        },
      },
      {
        accessorKey: 'vendor_code',
        header: 'vendor_code',
      },
      {
        id: 'selection',
        header: ({ table }) => (
          <input
            type="checkbox"
            className="rounded text-gray-900"
            checked={table.getIsAllRowsSelected()}
            onChange={table.getToggleAllRowsSelectedHandler()}
          />
        ),
        cell: ({ row }) => (
          <input
            type="checkbox"
            className="rounded text-gray-900"
            checked={row.getIsSelected()}
            onChange={row.getToggleSelectedHandler()}
          />
        ),
      }
    ], []);

  const table = useReactTable({
    data: goods,
    columns,
    state: {
      sorting,
      columnFilters,
      rowSelection,
    },
    onSortingChange: setSorting,
    onColumnFiltersChange: setColumnFilters,
    onRowSelectionChange: setRowSelection,
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    enableRowSelection: true,
  });

  const handleNmIdFilterChange = (e) => {
    const value = e.target.value || '';
    setColumnFilters((prev) =>
      prev.filter((filter) => filter.id !== 'nm_id').concat({ id: 'nm_id', value })
    );
  };

  const handleVendorCodeFilterChange = (e) => {
    const value = e.target.value || '';
    setColumnFilters((prev) =>
      prev.filter((filter) => filter.id !== 'vendor_code').concat({ id: 'vendor_code', value })
    );
  };

  useEffect(() => {
    const selectedIds = Object.keys(rowSelection)
      .filter((key) => rowSelection[key])
      .map((key) => goods[key].id);

    setSelectedGoodsId(selectedIds);
  }, [rowSelection, goods, setSelectedGoodsId]);

  return (
    <div>
      <div className='flex gap-x-[40px] mb-2'>
        <input
          placeholder="Фильтр по nm_id"
          className='border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2'
          onChange={handleNmIdFilterChange}
        />
        <input
          placeholder="Фильтр по vendor_code"
          className='border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2'
          onChange={handleVendorCodeFilterChange}
        />
      </div>
      <table className="w-full text-sm text-left text-gray-500">
        <thead className="text-xs text-gray-700 uppercase bg-gray-200">
          {table.getHeaderGroups().map((headerGroup) => (
            <tr key={headerGroup.id}>
              {headerGroup.headers.map((header) => (
                <th
                  className="px-6 py-3 cursor-pointer"
                  key={header.id}
                  colSpan={header.colSpan}
                  style={{
                    width:
                      header.column.id === 'nm_id' ? '190px' :
                      header.column.id === 'vendor_code' ? '380px' : 'auto', // Задаем ширину для каждой колонки
                  }}>
                  <div
                    className='flex gap-x-2'
                    {...{
                      onClick: header.column.getToggleSortingHandler(),
                    }}
                  >
                    {flexRender(header.column.columnDef.header, header.getContext())}
                    {header.column.getCanSort() && (
                      <span className="ml-1">
                        {{
                          asc: <BarsArrowUpIcon className="w-4 h-4" />,
                          desc: <BarsArrowDownIcon className="w-4 h-4" />,
                        }[header.column.getIsSorted()] ?? (
                          <ArrowsUpDownIcon className="w-4 h-4" />
                        )}
                      </span>
                    )}
                  </div>
                </th>
              ))}
            </tr>
          ))}
        </thead>
        <tbody>
          {table.getRowModel().rows.map((row) => (
            <tr key={row.id} className="bg-white border-b ">
              {row.getVisibleCells().map((cell) => (
                <td key={cell.id} className="px-6 py-2 text-gray-700">
                  {flexRender(cell.column.columnDef.cell, cell.getContext())}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}