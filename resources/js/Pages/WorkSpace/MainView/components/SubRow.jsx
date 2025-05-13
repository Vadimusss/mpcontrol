import React from 'react';
import { tableClasses } from '../styles';

export const SubRow = React.memo(({ item, metadata, dates }) => {
  const { name, type } = metadata;

  return (
    <tr className={tableClasses.cell}>
      {Array.from({ length: 6 }).map((_, index) => (
        <th key={`empty-${index}`} className={tableClasses.cell}></th>
      ))}
      <td className={`${tableClasses.cell} text-gray-500`}>
        {name}
      </td>
      {dates.map((date, i) => (
        <td key={`date-${i}`} className={tableClasses.cell}>
          {item.salesData.hasOwnProperty(date) ? item.salesData[date][type] : ''}
        </td>
      ))}
      <td className={tableClasses.cell}>
        {item.totals[type]}
      </td>
      <td className={tableClasses.cell}>
        {type === 'orders_count' ? item.prices.discount : type === 'sum_price' ? item.prices.price : ''}
      </td>
      <td className={tableClasses.cell}>
        {type === 'orders_count' ? '?' : ''}
      </td>
      {Array.from({ length: 2 }).map((_, index) => (
        <td key={`empty-${index}`} className={tableClasses.cell}></td>
      ))}
      <td className={tableClasses.cell}>
        {type === 'orders_count' ? item.ddr : ''}
      </td>
      {Array.from({ length: 7 }).map((_, index) => (
        <td key={`empty-${index}`} className={tableClasses.cell}></td>
      ))}
    </tr>
  );
});
