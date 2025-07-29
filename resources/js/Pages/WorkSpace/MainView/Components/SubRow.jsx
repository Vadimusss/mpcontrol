import React from 'react';
import { tableClasses, columnPropertys } from '../styles';

export const SubRow = React.memo(({ item, metadata, dates }) => {
  const { name, type } = metadata;

  return (
    <tr className={tableClasses.row}>
      <td className={`${tableClasses.cell} ${columnPropertys.control}`}></td>
      <td className={`${tableClasses.cell} ${columnPropertys.article}`}></td>
      <td className={`${tableClasses.cell} ${columnPropertys.name}`}></td>
      <td className={`${tableClasses.cell} ${columnPropertys.variant}`}></td>
      <td className={`${tableClasses.cell} ${columnPropertys.wbArticle}`}></td>
      <td className={`${tableClasses.cell} ${columnPropertys.empty} text-gray-500`}>
        {name}
      </td>
      {dates.map((date, i) => item.salesData.hasOwnProperty(date) ?
        <td key={`date-${i}`} className={`${tableClasses.cell} ${tableClasses.numbersCell} ${item.salesData[date].isHighlighted && type === 'advertising_costs' ?
          tableClasses.cellBgYellow : ''
          }`}>
          {item.salesData[date][type]}
        </td> : <td key={`date-${i}`} className={`${tableClasses.cell}`}></td>
      )}
      <td className={`${tableClasses.cell} ${tableClasses.numbersCell}`}>
        {item.totals[type]}
      </td>
      <td className={`${tableClasses.cell} ${tableClasses.numbersCell}`}>
        {type === 'orders_sum_rub' ? item.prices.discount : type === 'advertising_costs' ? item.prices.price : ''}
      </td>
{/*       <td className={tableClasses.cell}>
      </td> */}
      {Array.from({ length: 2 }).map((_, index) => (
        <td key={`empty-${index}`} className={tableClasses.cell}></td>
      ))}
      <td className={`${tableClasses.cell} ${tableClasses.numbersCell}`}>
        {type === 'orders_sum_rub' ? item.ddr : ''}
      </td>
      {Array.from({ length: 2 }).map((_, index) => (
        <td key={`empty-${index}`} className={tableClasses.cell}></td>
      ))}
      {type === 'orders_sum_rub' ?
        <>
          <td className={`${tableClasses.cell} ${tableClasses.numbersCell}`}>
            {item.salesByWarehouse.elektrostal}
          </td>
          <td className={`${tableClasses.cell} ${tableClasses.numbersCell}`}>
            {item.salesByWarehouse.tula}
          </td>
          <td className={`${tableClasses.cell} ${tableClasses.numbersCell}`}>
            {item.salesByWarehouse.nevinnomyssk}
          </td>
          <td className={`${tableClasses.cell} ${tableClasses.numbersCell}`}>
            {item.salesByWarehouse.krasnodar}
          </td>
          <td className={`${tableClasses.cell} ${tableClasses.numbersCell}`}>
            {item.salesByWarehouse.kazan}
          </td>
        </> :
        Array.from({ length: 5 }).map((_, index) => (
          <td key={`empty-${index}`} className={tableClasses.cell}></td>
        ))}
    </tr>
  );
});
