import React from 'react';
import { observer } from 'mobx-react-lite';
import { PlusIcon } from '@heroicons/react/24/outline';
import { formatValueByType, stylingFormatter } from '../../../utils';
import '../../../styles.css';

export const GoodDetailsTableBody = observer(({ dates, goodDetails, handleOpenNotes }) => {
    const { salesData, monthlyTotals, prcentColumn, salesByWarehouse, subRowsMetadata, notesData } = goodDetails;

    return (
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
                        <td className={`sticky-column sticky-left ${stylingFormatter.getStaticClass(type)}`}>
                            {name}
                        </td>
                        {dates.map((date) => {
                            const value = salesData[date]?.[type] || '';
                            const advertisingCosts = salesData[date]?.advertising_costs || 0;

                            const staticClass = stylingFormatter.getStaticClass(type);
                            const dynamicClass = stylingFormatter.checkDynamicConditions(type, value, advertisingCosts);
                            const cellClasses = `${staticClass} ${dynamicClass}`.trim();

                            return (
                                <td key={date} className={cellClasses || undefined}>
                                    {formatValueByType(value, type)}
                                </td>
                            );
                        })}
                        <td className={`bg-gray ${stylingFormatter.getStaticClass(type)}`}>
                            {formatValueByType(monthlyTotals[type], type, '_total')}
                        </td>
                        <td className="bg-gray">
                            {formatValueByType(prcentColumn[type], type, '_percent', '%')}
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
    );
});
