import React from 'react';
import { flexRender } from '@tanstack/react-table';
import '../styles.css';

export const TableHeader = ({
    shop,
    workSpaceSettings,
    dates,
    table,
    onTooltip
}) => {

    const checkOverflow = (element, text) => {
        if (!element || !text) return false;

        if (text.length < 4) return false;
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
        <thead className={`sticky-header`}>
            <tr>
                <th colSpan={5} className={`sticky-column sticky-left`}>
                    {`Комиссия: ${shop.settings?.commission}%,
                                Логистика: ${shop.settings?.logistics},
                                Коэф. процентиля: ${shop.settings?.percentile_coefficient},
                                Коэф. веса: ${shop.settings?.weight_coefficient}`}
                </th>
                <th colSpan={dates.length + 3}></th>
                <th colSpan={3}>Цена сейчас</th>
                <th colSpan={3}>Юнитка</th>
                <th colSpan={5}>Остаток</th>
                <th colSpan={23}>Остаток по складам</th>
            </tr>
            {table.getHeaderGroups().map(headerGroup => (
                <tr key={headerGroup.id}>
                    {headerGroup.headers.map(header => {
                        const headerValue = header.column.columnDef.header;
                        return (
                            <th
                                key={header.id}
                                onMouseEnter={(e) => handleMouseEnter(e, headerValue)}
                                onMouseLeave={handleMouseLeave}
                                className={header.column.columnDef.meta?.thClassName || ''}
                            >
                                {flexRender(header.column.columnDef.header, header.getContext())}
                            </th>)

                    })}
                </tr>
            ))}
            <tr>
                <th colSpan={5} className={`sticky-column sticky-left`}></th>
                {Array.from({ length: workSpaceSettings.days }, (_, index) => -index).reverse().map((number, index) => (
                    <th key={index}>{number}</th>
                ))}
                <th colSpan={37}></th>
            </tr>
        </thead>
    );
};