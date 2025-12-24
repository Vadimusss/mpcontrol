import React from 'react';
import { observer } from 'mobx-react-lite';
import { PlusIcon } from '@heroicons/react/24/outline';
import '../styles.css';

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
                                {dates.map((date) => (
                                    <td key={date}>
                                        {salesData[date]?.[type] || ''}
                                    </td>
                                ))}
                                <td>
                                    {monthlyTotals[type] || ''}
                                </td>
                                <td>
                                    {getWarehouseValue('elektrostal')}
                                </td>
                                <td>
                                    {getWarehouseValue('tula')}
                                </td>
                                <td>
                                    {getWarehouseValue('nevinnomyssk')}
                                </td>
                                <td>
                                    {getWarehouseValue('krasnodar')}
                                </td>
                                <td>
                                    {getWarehouseValue('kazan')}
                                </td>
                            </tr>
                        );
                    })}
                </tbody>
            </table>
        </div>
    );
});