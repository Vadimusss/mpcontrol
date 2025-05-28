import React from 'react';
import { tableClasses } from '../styles';
import { PlusIcon } from '@heroicons/react/24/outline';

export const NotesRow = React.memo(({
  isNotesExists,
  goodId,
  dates,
  onOpenNotes
}) => {
  return (
    <tr className={tableClasses.row}>
      {Array.from({ length: 6 }).map((_, index) => (
        <th key={`empty-${index}`} className={tableClasses.cell}></th>
      ))}
      <td className={`${tableClasses.cell} text-gray-500`}>
        Заметки
      </td>
      {dates.map((date, i) => (
        <td key={`date-${i}`} 
          className={`${isNotesExists[date] ? tableClasses.cellBgGreen : ''}`}>
          <button
            onClick={() => onOpenNotes(date, goodId)}
            className="size-full flex place-content-center"
          >
            <PlusIcon className="w-3 h-3" />
          </button>
        </td>
      ))}
      {Array.from({ length: 13 }).map((_, index) => (
        <td key={`empty-${index}`} className={tableClasses.cell}></td>
      ))}
    </tr>
  );
});
