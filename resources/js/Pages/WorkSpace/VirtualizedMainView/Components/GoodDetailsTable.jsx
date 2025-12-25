import React from 'react';
import { observer } from 'mobx-react-lite';
import { PlusIcon } from '@heroicons/react/24/outline';
import { formatInteger, formatOneDecimal, formatNumber } from '../Utils/formatters';
import '../styles.css';

// Функция для форматирования значения в зависимости от типа поля
const formatValueByType = (value, type) => {
    if (value === '' || value === null || value === undefined) return '';
    
    // Определяем тип форматирования на основе type
    switch (type) {
        // Целые числа
        case 'orders_count':
        case 'buyouts_count':
        case 'open_card_count':
        case 'no_ad_clicks':
        case 'add_to_cart_count':
        case 'aac_views':
        case 'aac_clicks':
        case 'aac_orders':
        case 'auc_views':
        case 'auc_clicks':
        case 'auc_orders':
        case 'ad_orders':
        case 'no_ad_orders':
        case 'assoc_orders_from_other':
        case 'assoc_orders_from_this':
            return formatInteger(value);
        
        // Числа с 1 знаком после запятой
        case 'advertising_costs':
        case 'orders_profit':
        case 'orders_sum_rub':
        case 'buyouts_sum_rub':
        case 'buyouts_profit':
        case 'aac_sum':
        case 'auc_sum':
        case 'aac_cpm':
        case 'auc_cpm':
        case 'add_to_cart_conversion':
        case 'cart_to_order_conversion':
            return formatOneDecimal(value);
        
        // CTR и CPC - 2 знака после запятой
        case 'aac_ctr':
        case 'auc_ctr':
        case 'aac_cpc':
        case 'auc_cpc':
            return formatNumber(value, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        
        // Цены - целые числа
        case 'price_with_disc':
        case 'spp':
        case 'finished_price':
            return formatInteger(value);
        
        // По умолчанию - как есть
        default:
            return String(value);
    }
};

export const GoodDetailsTable = observer(({ goodDetails, dates, workSpaceSettings, handleOpenNotes }) => {
    const { salesData, monthlyTotals, salesByWarehouse, subRowsMetadata, notesData } = goodDetails;

    return (
        <div className="table-container">
            <table className="sticky-table">
                <colgroup>
                    <col style={{ width: '140px' }} />
                    {dates.map((date) => <col key={`date-${date}`} style={{ width: '46px' }} />)}
                    {[...Array(6).keys()].map((number) => <col key={`col-${number}`} style={{ width: '60px' }} />)}
                </colgroup>
                <thead className="sticky-header">
                    <tr>
                        <th className="sticky-column sticky-left">
                        </th>
                        <th colSpan={dates.length}></th>
                        <th></th>
                        <th colSpan="5">Продажи по складам шт.</th>
                    </tr>
                    <tr>
                        <th className="sticky-column sticky-left"></th>
                        {dates.map((date) => {
                            const formattedDate = new Date(date).toLocaleDateString('ru-RU', {
                                day: '2-digit',
                                month: '2-digit'
                            });
                            return (
                                <th key={date}>
                                    {formattedDate}
                                </th>
                            );
                        })}
                        <th>∑ мес.</th>
                        <th>Сталь</th>
                        <th>Тула</th>
                        <th>Нмысск</th>
                        <th>Красн</th>
                        <th>Казань</th>
                    </tr>
                    <tr>
                        <th className="sticky-column sticky-left"></th>
                        {Array.from({ length: workSpaceSettings.days }, (_, index) => -index).reverse().map((number, index) => (
                            <th key={index}>{number}</th>
                        ))}
                        <th></th>
                        <th colSpan="5"></th>
                    </tr>
                </thead>
                <tbody>
                    {subRowsMetadata.map((metadata, index) => {
                        const { name, type } = metadata;

                        const getWarehouseValue = (warehouseKey) => {
                            if (type === 'orders_count') {
                                return salesByWarehouse[warehouseKey] || 0;
                            }
                            return '';
                        };

                        if (type === 'notes') {
                            return (
                                <tr key={`${type}-${index}`} className="table-row">
                                    <td className="sticky-column sticky-left">
                                        {name}
                                    </td>
                                    {dates.map((date) => (
                                        <td key={date} className={notesData[date] ? 'notes-cell-green' : ''}>
                                            <button
                                                onClick={() => handleOpenNotes(date, goodDetails.goodId)}
                                                className="notes-button"
                                            >
                                                <PlusIcon className="notes-icon" />
                                            </button>
                                        </td>
                                    ))}
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            );
                        }

                        return (
                            <tr key={`${type}-${index}`} className="table-row">
                                <td className="sticky-column sticky-left">
                                    {name}
                                </td>
                                {dates.map((date) => {
                                    const value = salesData[date]?.[type] || '';
                                    return (
                                        <td key={date}>
                                            {formatValueByType(value, type)}
                                        </td>
                                    );
                                })}
                                <td>
                                    {formatValueByType(monthlyTotals[type] || '', type)}
                                </td>
                                <td>
                                    {type === 'orders_count' ? formatInteger(getWarehouseValue('elektrostal')) : ''}
                                </td>
                                <td>
                                    {type === 'orders_count' ? formatInteger(getWarehouseValue('tula')) : ''}
                                </td>
                                <td>
                                    {type === 'orders_count' ? formatInteger(getWarehouseValue('nevinnomyssk')) : ''}
                                </td>
                                <td>
                                    {type === 'orders_count' ? formatInteger(getWarehouseValue('krasnodar')) : ''}
                                </td>
                                <td>
                                    {type === 'orders_count' ? formatInteger(getWarehouseValue('kazan')) : ''}
                                </td>
                            </tr>
                        );
                    })}
                </tbody>
            </table>
        </div>
    );
});
