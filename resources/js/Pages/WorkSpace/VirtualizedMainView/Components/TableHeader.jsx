import React from 'react';
import { flexRender } from '@tanstack/react-table';
import '../styles.css';

export const TableHeader = ({
    shop,
    workSpaceSettings,
    dates,
    table
}) => {
    return (
        <thead className={`sticky-header`}>
            <tr>
                <th colSpan={6} className={`sticky-column sticky-left`}>
                    {`Комиссия: ${shop.settings?.commission}%,
                                Логистика: ${shop.settings?.logistics},
                                Коэф. процентиля: ${shop.settings?.percentile_coefficient},
                                Коэф. веса: ${shop.settings?.weight_coefficient}`}
                </th>
                <th colSpan={dates.length + 5}></th>
                <th colSpan={2}>Остаток</th>
                <th colSpan={5}>Остаток по складам</th>
            </tr>
            {table.getHeaderGroups().map(headerGroup => (
                <tr key={headerGroup.id}>
                    {headerGroup.headers.map(header => (
                        <th
                            key={header.id}
                            className={
                                header.column.columnDef.sticky
                                    ? `sticky-column sticky-${header.column.columnDef.sticky}`
                                    : ``
                            }
                        >
                            {flexRender(header.column.columnDef.header, header.getContext())}
                        </th>
                    ))}
                </tr>
            ))}
            <tr>
                <th colSpan={6} className={`sticky-column sticky-left`}></th>
                {Array.from({ length: workSpaceSettings.days }, (_, index) => -index).reverse().map((number, index) => (
                    <th key={index}>{number}</th>
                ))}
                <th colSpan={12}></th>
            </tr>
        </thead>
    );
};