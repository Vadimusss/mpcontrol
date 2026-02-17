import React from 'react';
import { flexRender } from '@tanstack/react-table';
import { checkOverflow, generateDateHeaders } from '../../../utils';
import viewStore from '../../../Stores/ViewStore';
import '../styles.css';

export const TableHeader = ({
    shop,
    workSpaceSettings,
    table,
    onTooltip
}) => {
    const handleMouseEnter = (e, cellValue) => {
        if (!cellValue) return;

        const displayText = String(cellValue);
        const cellElement = e.currentTarget;
        const minTextLength = 6;

        if (!checkOverflow(cellElement, displayText, minTextLength)) return;

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

    const displayDays = viewStore.daysDisplay || workSpaceSettings.days;
    const dates = generateDateHeaders(displayDays);

    return (
        <thead className={`sticky-header`}>
            <tr>
                <th colSpan={5} className={`sticky-column sticky-left`}>
                    {`Комиссия: ${shop.settings?.commission}%,
                                Логистика: ${shop.settings?.logistics},
                                Коэф. процентиля: ${shop.settings?.percentile_coefficient},
                                Коэф. веса: ${shop.settings?.weight_coefficient}`}
                </th>
                <th colSpan={dates.length + 3}>
                    <div className="days-selector">
                        <span className="days-label">Дней: </span>
                        <a 
                            href="#" 
                            className={displayDays === 7 ? 'selected' : ''}
                            onClick={(e) => { e.preventDefault(); viewStore.setDaysDisplay(7); }}
                        >
                            7
                        </a>
                        <span className="separator">|</span>
                        <a 
                            href="#" 
                            className={displayDays === 14 ? 'selected' : ''}
                            onClick={(e) => { e.preventDefault(); viewStore.setDaysDisplay(14); }}
                        >
                            14
                        </a>
                        <span className="separator">|</span>
                        <a 
                            href="#" 
                            className={displayDays === 30 ? 'selected' : ''}
                            onClick={(e) => { e.preventDefault(); viewStore.setDaysDisplay(30); }}
                        >
                            30
                        </a>
                    </div>
                </th>
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
                {Array.from({ length: displayDays }, (_, index) => -index).reverse().map((number, index) => (
                    <th key={index}>{number}</th>
                ))}
                <th colSpan={37}></th>
            </tr>
        </thead>
    );
};