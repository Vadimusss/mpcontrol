import React from 'react';
import { observer } from 'mobx-react-lite';
import { MinusIcon, PlusIcon } from '@heroicons/react/24/outline';
import { tableClasses, columnPropertys } from '../styles';
import { TableControls } from './TableControls';
import { viewStore } from '../Stores/ViewStore';

export const ProductRow = observer(({
  item,
  dates
}) => {
  return (
    <tr className={tableClasses.productRow}>
      <td className={`${tableClasses.productFixedCell} ${columnPropertys.control}`} >
        <TableControls
          showOnlySelected={viewStore.selectedItems.includes(item.id)}
          allExpanded={!!viewStore.expandedRows[item.id]}
          onToggleShowOnlySelected={() => viewStore.toggleItemSelection(item.id)}
          onToggleAllRows={() => viewStore.toggleRow(item.id)}
        />
      </td>
      <td className={`${tableClasses.productFixedCell} ${columnPropertys.article} ${tableClasses.textCell}`}>
        {item.article}
      </td>
      <td className={`${tableClasses.productFixedCell} ${columnPropertys.name} ${tableClasses.textCell}`}>
        {item.name}
      </td>
      <td className={`${tableClasses.productFixedCell} ${columnPropertys.variant}`}>
        {item.variant}
      </td>
      <td className={`${tableClasses.productFixedCell} ${columnPropertys.wbArticle}`}>
        <a
          href={`https://www.wildberries.ru/catalog/${item.wbArticle}/detail.aspx`}
          target="_blank"
          rel="noopener noreferrer"
          className={tableClasses.linkCell}
        >
          {item.wbArticle}
        </a>
      </td>
      <td className={`${tableClasses.productFixedCell} ${columnPropertys.empty}`}>
        {item.mainRowMetadata.name}
      </td>
      {dates.map((date, i) => item.salesData.hasOwnProperty(date) ?
        <td key={`date-${i}`} className={`${tableClasses.cell} ${tableClasses.numbersCell} ${item.salesData[date].isHighlighted ? tableClasses.cellBgYellow : ''}`}>
          {item.salesData[date][item.mainRowMetadata.type]}
        </td> : <td key={`date-${i}`} className={`${tableClasses.cell}`}></td>
      )}
      <td className={`${tableClasses.cell} ${tableClasses.numbersCell}`}>{item.totals.orders_count}</td>
      <td className={`${tableClasses.cell} ${tableClasses.numbersCell}`}>{item.prices.discountedPrice}</td>
      {/* <td className={tableClasses.cell}>?</td> */}
      <td className={`${tableClasses.cell} ${tableClasses.numbersCell}`}>{item.prices.costWithTaxes}</td>
      <td className={`${tableClasses.cell} ${tableClasses.numbersCell}`}>{item.mainRowProfit}</td>
      <td className={`${tableClasses.cell} ${tableClasses.numbersCell}`}>
        {item.percent === '?' ? '?' : `${item.percent}%`}
      </td>
      <td className={`${tableClasses.cell} ${tableClasses.numbersCell}`}>{item.stocks.totals}</td>
      <td className={`${tableClasses.cell} ${tableClasses.numbersCell}`}>{item.days_of_stock}</td>
      <td className={`${tableClasses.cell} ${tableClasses.numbersCell}`}>{item.stocks.elektrostal}</td>
      <td className={`${tableClasses.cell} ${tableClasses.numbersCell}`}>{item.stocks.tula}</td>
      <td className={`${tableClasses.cell} ${tableClasses.numbersCell}`}>{item.stocks.nevinnomyssk}</td>
      <td className={`${tableClasses.cell} ${tableClasses.numbersCell}`}>{item.stocks.krasnodar}</td>
      <td className={`${tableClasses.cell} ${tableClasses.numbersCell}`}>{item.stocks.kazan}</td>
    </tr>
  );
});
