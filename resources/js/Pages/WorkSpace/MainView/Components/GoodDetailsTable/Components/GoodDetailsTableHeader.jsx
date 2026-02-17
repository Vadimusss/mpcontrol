import React from 'react';
import viewStore from '../../../Stores/ViewStore';
import '../styles.css';

export const GoodDetailsTableHeader = ({
    dates,
    workSpaceSettings
}) => {
    const displayDays = viewStore.goodDetailsDaysDisplay || workSpaceSettings.days;

    return (
        <thead className="sticky-header">
            <tr>
                <th className="sticky-column sticky-left">
                </th>
                <th colSpan={dates.length}>
                    <div className="days-selector">
                        <span className="days-label">Дней: </span>
                        <a 
                            href="#" 
                            className={displayDays === 7 ? 'selected' : ''}
                            onClick={(e) => { e.preventDefault(); viewStore.setGoodDetailsDaysDisplay(7); }}
                        >
                            7
                        </a>
                        <span className="separator">|</span>
                        <a 
                            href="#" 
                            className={displayDays === 14 ? 'selected' : ''}
                            onClick={(e) => { e.preventDefault(); viewStore.setGoodDetailsDaysDisplay(14); }}
                        >
                            14
                        </a>
                        <span className="separator">|</span>
                        <a 
                            href="#" 
                            className={displayDays === 30 ? 'selected' : ''}
                            onClick={(e) => { e.preventDefault(); viewStore.setGoodDetailsDaysDisplay(30); }}
                        >
                            30
                        </a>
                    </div>
                </th>
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
                {Array.from({ length: displayDays }, (_, index) => -index).reverse().map((number, index) => (
                    <th key={index}>{number}</th>
                ))}
                <th></th>
                <th></th>
                <th colSpan="5"></th>
            </tr>
        </thead>
    );
};