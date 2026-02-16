import React from 'react';
import '../styles.css';

export const GoodDetailsTableHeader = ({
    dates,
    workSpaceSettings
}) => {
    return (
        <thead className="sticky-header">
            <tr>
                <th className="sticky-column sticky-left">
                </th>
                <th colSpan={dates.length}></th>
                <th></th>
                <th></th>
                <th colSpan="5">Продажи по складам, шт. ∑ мес.</th>
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
                <th>%</th>
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
                <th></th>
                <th colSpan="5"></th>
            </tr>
        </thead>
    );
};