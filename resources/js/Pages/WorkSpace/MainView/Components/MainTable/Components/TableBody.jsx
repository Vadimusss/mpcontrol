import React, { useCallback, memo, useEffect } from 'react';
import { observer } from 'mobx-react-lite';
import { flexRender } from '@tanstack/react-table';
import { useVirtualizer } from '@tanstack/react-virtual';
import { checkOverflow } from '../../../utils';
import { viewStore } from '../../../Stores/ViewStore';
import '../../../styles.css';

const TableCell = memo(({ cell, row, onMouseEnter, onMouseLeave }) => {
    const cellValue = cell.getValue();
    const isClassNameDynamic = cell.column.columnDef.meta?.isClassNameDynamic;
    const tdClassName = cell.column.columnDef.meta?.tdClassName;
    const className = isClassNameDynamic
        ? tdClassName(row.original)
        : tdClassName || '';

    return (
        <td
            key={cell.id}
            className={className}
            onMouseEnter={(e) => onMouseEnter(e, cellValue)}
            onMouseLeave={onMouseLeave}
        >
            {flexRender(cell.column.columnDef.cell, cell.getContext())}
        </td>
    );
});

TableCell.displayName = 'TableCell';

export const TableBody = observer(({ tableContainerRef, table, columns, onTooltip }) => {
    const { rows } = table.getRowModel();
    
    const rowVirtualizer = useVirtualizer({
        count: rows.length,
        getScrollElement: () => tableContainerRef.current,
        estimateSize: () => 26,
        overscan: 20,
    });

    const virtualRows = rowVirtualizer.getVirtualItems();
    const totalSize = rowVirtualizer.getTotalSize();

    useEffect(() => {
        if (rows.length > 0) {
            rowVirtualizer.measure();
        }
    }, [rows, rowVirtualizer]);

    useEffect(() => {
        if (tableContainerRef.current) {
            tableContainerRef.current.scrollTop = 0;
            rowVirtualizer.measure();
        }
    }, [viewStore.searchQuery, viewStore.sortedColumn, viewStore.sortDirection]);

    const paddingTop = virtualRows.length > 0 ? virtualRows[0]?.start || 0 : 0;
    const paddingBottom = virtualRows.length > 0
        ? totalSize - (virtualRows[virtualRows.length - 1]?.end || 0)
        : 0;

    const handleMouseEnter = useCallback((e, cellValue) => {
        if (!cellValue) return;

        const displayText = String(cellValue);
        const cellElement = e.currentTarget;
        const minTextLength = 15;

        if (!checkOverflow(cellElement, displayText, minTextLength)) return;

        const rect = cellElement.getBoundingClientRect();

        onTooltip({
            text: displayText,
            x: rect.left + 40,
            y: rect.bottom + 5
        });
    }, [onTooltip]);

    const handleMouseLeave = useCallback(() => {
        onTooltip(null);
    }, [onTooltip]);

    return (
        <tbody>
            {paddingTop > 0 && (
                <tr key="padding-top">
                    <td colSpan={columns.length} style={{ height: `${paddingTop}px` }} />
                </tr>
            )}

            {virtualRows.map(virtualRow => {
                const row = rows[virtualRow.index];
                
                return (
                    <tr
                        key={row.id}
                        className="table-row"
                        style={{
                            height: '26px',
                        }}
                    >
                        {row.getVisibleCells().map(cell => (
                            <TableCell
                                key={cell.id}
                                cell={cell}
                                row={row}
                                onMouseEnter={handleMouseEnter}
                                onMouseLeave={handleMouseLeave}
                            />
                        ))}
                    </tr>
                );
            })}

            {paddingBottom > 0 && (
                <tr key="padding-bottom">
                    <td colSpan={columns.length} style={{ height: `${paddingBottom}px` }} />
                </tr>
            )}
        </tbody>
    );
});
