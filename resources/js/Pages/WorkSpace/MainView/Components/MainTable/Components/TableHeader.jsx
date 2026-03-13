import React, { useCallback, useMemo } from 'react';
import { observer } from 'mobx-react-lite';
import { flexRender } from '@tanstack/react-table';
import { checkOverflow, generateDateHeaders } from '../../../utils';
import viewStore from '../../../Stores/ViewStore';
import { PlusIcon, MinusIcon, Cog6ToothIcon } from '@heroicons/react/24/outline';
import '../../../styles.css';

export const TableHeader = observer(({
    shop,
    workSpaceSettings,
    table,
    onTooltip,
}) => {
    const handleMouseEnter = useCallback((e, cellValue) => {
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
    }, [onTooltip]);

    const handleMouseLeave = useCallback(() => {
        onTooltip(null);
    }, [onTooltip]);

    const displayDays = viewStore.daysDisplay || workSpaceSettings.days;
    const dates = useMemo(() => generateDateHeaders(displayDays), [displayDays]);

    return (
        <>
            <thead className={`sticky-header`}>
                <tr>
                    <th colSpan={6} className={`sticky-column sticky-left`}>
                        {`Комиссия: ${shop.settings?.commission}%,
                                Логистика: ${shop.settings?.logistics},
                                Коэф. процентиля: ${shop.settings?.percentile_coefficient},
                                Коэф. веса: ${shop.settings?.weight_coefficient}`}
                    </th>
                    <th colSpan={dates.length + 2}>
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
                    <th colSpan={3}>Реклама</th>
                    <th colSpan={4}>Заказы</th>
                    <th></th>
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
                    <th colSpan={6} className={`sticky-column sticky-left`}>
                        <div className="categorys-controls">
                            <button
                                className="totals-toggle-button"
                                onClick={(e) => {
                                    e.preventDefault();
                                    viewStore.toggleCategoryTotals();
                                }}
                                title={viewStore.showCategoryTotals ? "Скрыть итоги" : "Показать итоги"}
                            >
                                <span className="totals-toggle-text">
                                    Итоги
                                </span>
                                {viewStore.showCategoryTotals ? (
                                    <MinusIcon className="totals-toggle-icon" />
                                ) : (
                                    <PlusIcon className="totals-toggle-icon" />
                                )}
                            </button>
                            <button
                                className="settings-toggle-button"
                                onClick={(e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    viewStore.toggleCategorySelectionModal();
                                }}
                                title="Настройки отображения категорий"
                            >
                                <Cog6ToothIcon className="settings-toggle-icon" />
                            </button>
                        </div>
                    </th>
                    {Array.from({ length: displayDays }, (_, index) => -index).reverse().map((number, index) => (
                        <th key={index}>{number}</th>
                    ))}
                    <th></th>
                    <th></th>
                    <th colSpan={3}></th>
                    <th colSpan={4}></th>
                    <th></th>
                    <th colSpan={3}></th>
                    <th colSpan={3}></th>
                    <th colSpan={5}></th>
                    <th colSpan={23}></th>
                </tr>
            </thead>
        </>
    );
});
