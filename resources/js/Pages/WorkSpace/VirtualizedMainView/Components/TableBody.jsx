import React from 'react';
import { flexRender } from '@tanstack/react-table';
import { useVirtualizer } from '@tanstack/react-virtual';
import '../styles.css';

export const TableBody = ({ tableContainerRef, table, columns }) => {
    const { rows } = table.getRowModel();

    const rowVirtualizer = useVirtualizer({
        count: rows.length,
        getScrollElement: () => tableContainerRef.current,
        estimateSize: () => 26,
        overscan: 10,
    });

    const virtualRows = rowVirtualizer.getVirtualItems();
    const totalSize = rowVirtualizer.getTotalSize();

    const paddingTop = virtualRows.length > 0 ? virtualRows[0]?.start || 0 : 0;
    const paddingBottom = virtualRows.length > 0
        ? totalSize - (virtualRows[virtualRows.length - 1]?.end || 0)
        : 0;

    return (
        <tbody>
            {paddingTop > 0 && (
                <tr>
                    <td colSpan={columns.length} style={{ height: `${paddingTop}px` }} />
                </tr>
            )}

            {virtualRows.map(virtualRow => {
                const row = rows[virtualRow.index];
                return (
                    <tr
                        key={row.id}
                        className={`table-row`}
                        style={{
                            height: '26px',
                        }}
                    >
                        {row.getVisibleCells().map(cell => (
                            <td
                                key={cell.id}
                                className={
                                    cell.column.columnDef.sticky
                                        ? `sticky-column sticky-${cell.column.columnDef.sticky}`
                                        : ''
                                }
                                style={{
                                    width: cell.column.getSize(),
                                }}
                            >
                                {flexRender(cell.column.columnDef.cell, cell.getContext())}
                            </td>
                        ))}
                    </tr>
                );
            })}

            {paddingBottom > 0 && (
                <tr>
                    <td colSpan={columns.length} style={{ height: `${paddingBottom}px` }} />
                </tr>
            )}
        </tbody>
    );
};