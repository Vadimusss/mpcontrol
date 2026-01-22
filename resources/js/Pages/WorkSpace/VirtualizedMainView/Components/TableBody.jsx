import React, { useState, useRef, useEffect } from 'react';
import { flexRender } from '@tanstack/react-table';
import { useVirtualizer } from '@tanstack/react-virtual';
import '../styles.css';

export const TableBody = ({ tableContainerRef, table, columns, onTooltip }) => {
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

    const checkOverflow = (element, text) => {
        if (!element || !text) return false;

        if (text.length < 15) return false;
        if (!isNaN(parseFloat(text)) && isFinite(text)) return false;
        if (/^\d{4}[-\/]\d{2}[-\/]\d{2}/.test(text)) return false;

        return element.scrollWidth > element.clientWidth + 2;
    };

    const handleMouseEnter = (e, cellValue) => {
        if (!cellValue) return;

        const displayText = String(cellValue);
        const cellElement = e.currentTarget;

        if (!checkOverflow(cellElement, displayText)) return;

        const rect = cellElement.getBoundingClientRect();

        onTooltip({
            text: displayText,
            x: rect.left + 40,
            y: rect.bottom + 5
        });
    };

    const handleMouseLeave = () => {
        onTooltip(null);
    };

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
                        {row.getVisibleCells().map(cell => {

                            const cellValue = cell.getValue();

                            return (
                                <td
                                    key={cell.id}
                                    className={cell.column.columnDef.meta?.tdClassName || ''}
                                    onMouseEnter={(e) => handleMouseEnter(e, cellValue)}
                                    onMouseLeave={handleMouseLeave}
                                >
                                    {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                </td>
                            )
                        })}
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