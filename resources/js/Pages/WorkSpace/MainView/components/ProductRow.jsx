import React from 'react';
import { MinusIcon, PlusIcon } from '@heroicons/react/24/outline';
import { tableClasses, columnWidths } from '../styles';
import { TableControls } from './TableControls';

export const ProductRow = React.memo(({
  item,
  dates,
  viewState,
  onToggleItemSelection,
  onToggleRow
}) => {
  return (
    <tr className={tableClasses.cell}>
      <td className={tableClasses.cell}>
        <TableControls
          showOnlySelected={viewState.selectedItems.includes(item.id)}
          allExpanded={!!viewState.expandedRows[item.id]}
          onToggleShowOnlySelected={() => onToggleItemSelection(item.id)}
          onToggleAllRows={() => onToggleRow(item.id)}
        />
      </td>
      <td className={`${tableClasses.cell} ${columnWidths.article} ${tableClasses.textCell}`}>
        {item.article}
      </td>
      <td className={`${tableClasses.cell} ${columnWidths.name} ${tableClasses.textCell}`}>
        {item.name}
      </td>
      <td className={`${tableClasses.cell} ${columnWidths.variant}`}>
        {item.variant}
      </td>
      <td className={`${tableClasses.cell} ${columnWidths.wbArticle}`}>
        <a
          href={`https://www.wildberries.ru/catalog/${item.wbArticle}/detail.aspx`}
          target="_blank"
          rel="noopener noreferrer"
          className={tableClasses.linkCell}
        >
          {item.wbArticle}
        </a>
      </td>
      <td className={`${tableClasses.cell} ${columnWidths.fg1}`}>
        {item.fg1}
      </td>
      <td className={`${tableClasses.cell} ${columnWidths.empty}`}>
        {item.mainRowMetadata.name}
      </td>
      {dates.map((date, i) => (
        <td key={`date-${i}`} className={tableClasses.cell}>
          {item.salesData.hasOwnProperty(date) ? item.salesData[date][item.mainRowMetadata.type] : ''}
        </td>
      ))}
      <td className={tableClasses.cell}>{item.totals.orders_count}</td>
      <td className={tableClasses.cell}>{item.prices.discountedPrice}</td>
      <td className={tableClasses.cell}>?</td>
      <td className={tableClasses.cell}>{item.prices.costWithTaxes}</td>
      <td className={tableClasses.cell}>{item.mainRowProfit}</td>
      <td className={tableClasses.cell}>
        {item.percent === '?' ? '?' : `${item.percent}%`}
      </td>
      <td className={tableClasses.cell}>{item.stocks.totals}</td>
      <td className={tableClasses.cell}>{item.days_of_stock}</td>
      <td className={tableClasses.cell}>{item.stocks.elektrostal}</td>
      <td className={tableClasses.cell}>{item.stocks.tula}</td>
      <td className={tableClasses.cell}>{item.stocks.nevinnomyssk}</td>
      <td className={tableClasses.cell}>{item.stocks.krasnodar}</td>
      <td className={tableClasses.cell}>{item.stocks.kazan}</td>
    </tr>
  );
});
