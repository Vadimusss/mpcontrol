import React from 'react';
import { numericFormatter } from '../../../utils';

const CategoryTableRow = ({ 
  row, 
  dates, 
  categoryNameColSpan,
  metricNameColSpan,
  totalColSpan,
  columnsLength,
}) => {
  const { category, metric, metricKey, dates: dateData, total, isFirstMetric } = row;
  
  return (
    <tr className="table-row category-row">
      <td 
        className="sticky-column sticky-left category-name-cell"
        colSpan={categoryNameColSpan}
      >
        {isFirstMetric ? category : ''}
      </td>
      <td 
        className="metric-name-cell sticky-column sticky-left"
        colSpan={metricNameColSpan}
      >
        {metric}
      </td>
      
      {dates.map((date) => {
        const value = dateData[date] || 0;
        const formattedValue = metricKey === 'drr' ?
          numericFormatter(value * 100, 1) : numericFormatter(value / 1000, 0);

        return (
          <td 
            key={`${row.id}-${date}`}
            className="category-value-cell"
          >
            {formattedValue}
          </td>
        );
      })}

      <td 
        className="total-cell"
        colSpan={totalColSpan}
      >
        {metricKey === 'drr' ? 
          numericFormatter(total * 100, 1) : 
          numericFormatter(total)}
      </td>
      <td colSpan={columnsLength - dates.length - categoryNameColSpan - metricNameColSpan - totalColSpan}></td>
    </tr>
  );
};

export default CategoryTableRow;