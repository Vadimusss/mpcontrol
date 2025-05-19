import React from 'react';
import { tableClasses } from '../styles';

export const SubRow = React.memo(({ item, metadata, dates }) => {
  const { name, type } = metadata;

  return (
    <tr className={tableClasses.row}>
      {Array.from({ length: 6 }).map((_, index) => (
        <th key={`empty-${index}`} className={tableClasses.cell}></th>
      ))}
      <td className={`${tableClasses.cell} text-gray-500`}>
        {name}
      </td>
      {dates.map((date, i) => (
        <td key={`date-${i}`} className={`${tableClasses.cell}`}>
          {item.salesData.hasOwnProperty(date) ? item.salesData[date][type] : ''}
        </td>
      ))}
      <td className={tableClasses.cell}>
        {item.totals[type]}
      </td>
      <td className={tableClasses.cell}>
        {type === 'orders_sum_rub' ? item.prices.discount : type === 'advertising_costs' ? item.prices.price : ''}
      </td>
      <td className={tableClasses.cell}>
      </td>
      {Array.from({ length: 2 }).map((_, index) => (
        <td key={`empty-${index}`} className={tableClasses.cell}></td>
      ))}
      <td className={tableClasses.cell}>
        {type === 'orders_sum_rub' ? item.ddr : ''}
      </td>
      {Array.from({ length: 2 }).map((_, index) => (
        <td key={`empty-${index}`} className={tableClasses.cell}></td>
      ))}
      {type === 'orders_sum_rub' ?
        <>
          <td className={tableClasses.cell}>
            {item.salesByWarehouse.elektrostal}
          </td>
          <td className={tableClasses.cell}>
            {item.salesByWarehouse.tula}
          </td>
          <td className={tableClasses.cell}>
            {item.salesByWarehouse.nevinnomyssk}
          </td>
          <td className={tableClasses.cell}>
            {item.salesByWarehouse.krasnodar}
          </td>
          <td className={tableClasses.cell}>
            {item.salesByWarehouse.kazan}
          </td>
        </> :
        Array.from({ length: 5 }).map((_, index) => (
          <td key={`empty-${index}`} className={tableClasses.cell}></td>
        ))}
    </tr>
  );
});
