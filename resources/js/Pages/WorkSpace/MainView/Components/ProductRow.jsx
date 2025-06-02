import React from 'react';
import { observer } from 'mobx-react-lite';
import { MinusIcon, PlusIcon } from '@heroicons/react/24/outline';
import { tableClasses, columnWidths } from '../styles';
import { TableControls } from './TableControls';
import { viewStore } from '../Stores/ViewStore';

export const ProductRow = observer(({
  item,
  dates
}) => {
  return (
    <tr className={tableClasses.row}>
      <td className={tableClasses.cell}>
        <TableControls
          showOnlySelected={viewStore.selectedItems.includes(item.id)}
          allExpanded={!!viewStore.expandedRows[item.id]}
          onToggleShowOnlySelected={() => viewStore.toggleItemSelection(item.id)}
          onToggleAllRows={() => viewStore.toggleRow(item.id)}
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
      {dates.map((date, i) => item.salesData.hasOwnProperty(date) ?
        <td key={`date-${i}`} className={`${tableClasses.cell} ${item.salesData[date].isHighlighted ? tableClasses.cellBgYellow : ''}`}>
          {item.salesData[date][item.mainRowMetadata.type]}
        </td> : <td key={`date-${i}`} className={`${tableClasses.cell}`}></td>
      )}
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
