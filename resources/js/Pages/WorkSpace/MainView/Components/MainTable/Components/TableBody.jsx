import React, { useCallback, memo, useEffect, useMemo } from 'react';
import { observer } from 'mobx-react-lite';
import { flexRender } from '@tanstack/react-table';
import { useVirtualizer } from '@tanstack/react-virtual';
import { checkOverflow } from '../../../utils';
import { viewStore } from '../../../Stores/ViewStore';
import CategoryTableRow from './CategoryTableRow';
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

export const TableBody = observer(({ 
    tableContainerRef, 
    table, 
    columns, 
    onTooltip,
    categoryRows = [],
    dates = []
}) => {
    const { rows } = table.getRowModel();
    
    const allRows = useMemo(() => {
        const combined = [];
        if (viewStore.showCategoryTotals) {
            categoryRows.forEach(row => {
                combined.push(row);
            });
        }
        rows.forEach(row => {
            combined.push({
                ...row,
                type: 'good_row',
                rowData: row
            });
        });
        return combined;
    }, [categoryRows, rows, viewStore.showCategoryTotals, viewStore.selectedCategories]);
    
    const rowVirtualizer = useVirtualizer({
        count: allRows.length,
        getScrollElement: () => tableContainerRef.current,
        estimateSize: (index) => {
            const row = allRows[index];
            if (row.type === 'category_separator') {
                return 2;
            }
            return 26;
        },
        overscan: 20,
    });

    const virtualRows = rowVirtualizer.getVirtualItems();
    const totalSize = rowVirtualizer.getTotalSize();

    useEffect(() => {
        if (allRows.length > 0) {
            rowVirtualizer.measure();
        }
    }, [allRows, rowVirtualizer]);

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
                const row = allRows[virtualRow.index];
                
                if (row.type === 'category_separator') {
                    return (
                        <tr key={row.id} className="category-separator-row">
                            <td colSpan={columns.length} className="bg-category-separator" style={{ height: '2px' }} />
                        </tr>
                    );
                }
                
                if (row.type === 'category_row') {
                    return (
                        <CategoryTableRow
                            key={row.id}
                            row={row}
                            dates={dates}
                            categoryNameColSpan={5}
                            metricNameColSpan={1}
                            totalColSpan={2}
                            columnsLength={columns.length}
                        />
                    );
                }
                
                return (
                    <tr
                        key={row.id}
                        className="table-row"
                        style={{
                            height: '26px',
                        }}
                    >
                        {row.rowData.getVisibleCells().map(cell => (
                            <TableCell
                                key={cell.id}
                                cell={cell}
                                row={row.rowData}
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
