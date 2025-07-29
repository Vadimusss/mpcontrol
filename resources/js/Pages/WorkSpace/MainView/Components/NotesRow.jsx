import React from 'react';
import { tableClasses, columnPropertys } from '../styles';
import { PlusIcon } from '@heroicons/react/24/outline';

export const NotesRow = React.memo(({
  isNotesExists,
  goodId,
  dates,
  onOpenNotes
}) => {
  return (
    <tr className={tableClasses.row}>
      <td className={`${tableClasses.fixedCell} ${columnPropertys.control}`}></td>
      <td className={`${tableClasses.fixedCell} ${columnPropertys.article}`}></td>
      <td className={`${tableClasses.fixedCell} ${columnPropertys.name}`}></td>
      <td className={`${tableClasses.fixedCell} ${columnPropertys.variant}`}></td>
      <td className={`${tableClasses.fixedCell} ${columnPropertys.wbArticle}`}></td>
      <td className={`${tableClasses.fixedCell} ${columnPropertys.empty} text-gray-500`}>
        Заметки
      </td>
      {dates.map((date, i) => (
        <td key={`date-${i}`}
          className={`${tableClasses.notesCell} ${isNotesExists[date] ? tableClasses.cellBgGreen : ''}`}>
          <button
            onClick={() => onOpenNotes(date, goodId)}
            className="size-full flex place-content-center"
          >
            <PlusIcon className="w-3 h-3" />
          </button>
        </td>
      ))}
      {Array.from({ length: 12 }).map((_, index) => (
        <td key={`empty-${index}`} className={tableClasses.cell}></td>
      ))}
    </tr>
  );
});
