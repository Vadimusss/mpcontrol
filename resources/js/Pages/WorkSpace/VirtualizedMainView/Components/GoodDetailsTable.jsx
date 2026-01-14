import React from 'react';
import { observer } from 'mobx-react-lite';
import { PlusIcon } from '@heroicons/react/24/outline';
import { formatter } from '../Utils';
import '../styles.css';

const formatValueByType = (value, type) => {
    switch (type) {
        case 'advertising_costs':
        case 'orders_profit':
        case 'buyouts_profit':
        case 'profit_without_ads':
        case 'aac_sum':
        case 'auc_sum':
        case 'aac_ctr':
        case 'auc_ctr':
            return formatter(value, 1);
        default:
            return formatter(value);
    }
};

export const GoodDetailsTable = observer(({ goodDetails, dates, workSpaceSettings, handleOpenNotes }) => {
    const { salesData, monthlyTotals, salesByWarehouse, subRowsMetadata, notesData } = goodDetails;

    return (
        <div className="table-container">
            <table className="sticky-table">
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
                                        <td key={date} className={notesData[date] ? 'notes-cell-green' : 'notes-cell'}>
                                            <button
                                                onClick={() => handleOpenNotes(date, goodDetails.goodId)}
                                                className="notes-button"
                                            >
                                                <PlusIcon className="notes-icon" />
                                            </button>
                                        </td>
                                    ))}
                                    <td className="bg-gray"></td>
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
                                <td className="bg-gray">
                                    {formatValueByType(monthlyTotals[type], type)}
                                </td>
                                <td>
                                    {type === 'orders_count' ? formatValueByType(getWarehouseValue('elektrostal')) : ''}
                                </td>
                                <td>
                                    {type === 'orders_count' ? formatValueByType(getWarehouseValue('tula')) : ''}
                                </td>
                                <td>
                                    {type === 'orders_count' ? formatValueByType(getWarehouseValue('nevinnomyssk')) : ''}
                                </td>
                                <td>
                                    {type === 'orders_count' ? formatValueByType(getWarehouseValue('krasnodar')) : ''}
                                </td>
                                <td>
                                    {type === 'orders_count' ? formatValueByType(getWarehouseValue('kazan')) : ''}
                                </td>
                            </tr>
                        );
                    })}
                </tbody>
            </table>
        </div>
    );
});
