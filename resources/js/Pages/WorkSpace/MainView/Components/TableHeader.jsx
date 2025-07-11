import React from 'react';
import { observer } from 'mobx-react-lite';
import { MinusIcon, PlusIcon } from '@heroicons/react/24/outline';
import { tableClasses, columnWidths } from '../styles';
import { viewStore } from '../Stores/ViewStore';

export const TableHeader = observer(({ 
  shop, 
  workSpaceSettings, 
  dates
}) => {
  return (
    <thead className={tableClasses.thead}>
      <tr>
        <th colSpan={7} className={tableClasses.mainHeader}>
          {`Комиссия: ${shop.settings?.commission}%,
            Логистика: ${shop.settings?.logistics},
            Коэф. процентиля: ${shop.settings?.percentile_coefficient},
            Коэф. веса: ${shop.settings?.weight_coefficient}`}
        </th>
        <th colSpan={6 + workSpaceSettings.days} className={tableClasses.mainHeader}></th>
        <th colSpan={2} className={tableClasses.mainHeader}>
          Остаток
        </th>
        <th colSpan={5} className={tableClasses.mainHeader}>
          Остаток по складам
        </th>
      </tr>
      <tr>
        <th className={`${tableClasses.subHeader} ${columnWidths.control}`}>
          <div className="flex items-center gap-2">
            <input
              type="checkbox"
              checked={viewStore.showOnlySelected}
              onChange={() => viewStore.toggleShowOnlySelected()}
              className="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
            />
            <button
              onClick={() => viewStore.toggleAllRows()}
              className="p-1 rounded hover:bg-gray-100"
            >
              {viewStore.allExpanded ? <MinusIcon className="w-4 h-4" /> : <PlusIcon className="w-4 h-4" />}
            </button>
          </div>
        </th>
        <th className={`${tableClasses.subHeader} ${columnWidths.article}`}>Артикул</th>
        <th className={`${tableClasses.subHeader} ${columnWidths.name}`}>Название</th>
        <th className={`${tableClasses.subHeader} ${columnWidths.variant}`}>Вариант</th>
        <th className={`${tableClasses.subHeader} ${columnWidths.wbArticle}`}>Артикул WB</th>
        <th className={`${tableClasses.subHeader} ${columnWidths.fg1}`}>ФГ1</th>
        <th className={`${tableClasses.subHeader} ${columnWidths.empty}`}></th>
        {dates.map((date, i) => (
          <th key={`date-${i}`} className={tableClasses.subHeader}>
            {new Date(date).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit' })}
          </th>
        ))}
        <th className={tableClasses.subHeader}>Сумма</th>
        <th className={tableClasses.subHeader}>Цена</th>
        <th className={tableClasses.subHeader}>Цена СПП</th>
        <th className={tableClasses.subHeader}>CC</th>
        <th className={tableClasses.subHeader}>Прибыль</th>
        <th className={tableClasses.subHeader}>%</th>
        <th className={tableClasses.subHeader}>остаток шт.</th>
        <th className={tableClasses.subHeader}>остаток дней</th>
        <th className={tableClasses.subHeader}>Сталь</th>
        <th className={tableClasses.subHeader}>Тула</th>
        <th className={tableClasses.subHeader}>Нмысск</th>
        <th className={tableClasses.subHeader}>Красн</th>
        <th className={tableClasses.subHeader}>Казань</th>
      </tr>
      <tr>
        <th colSpan={7} className={tableClasses.subHeader}></th>
        {Array.from({ length: workSpaceSettings.days }, (_, index) => -index).reverse().map((number, index) => (
          <td key={index} className={tableClasses.subHeader}>{number}</td>
        ))}
        {Array.from({ length: 13 }).map((_, index) => (
          <th key={`empty-${index}`} className={tableClasses.subHeader}></th>
        ))}
      </tr>
    </thead>
  );
});
