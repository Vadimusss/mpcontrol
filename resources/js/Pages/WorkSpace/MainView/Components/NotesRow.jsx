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
      <td className={`${tableClasses.notesFixedCell} ${columnPropertys.control}`}></td>
      <td className={`${tableClasses.notesFixedCell} ${columnPropertys.article}`}></td>
      <td className={`${tableClasses.notesFixedCell} ${columnPropertys.name}`}></td>
      <td className={`${tableClasses.notesFixedCell} ${columnPropertys.variant}`}></td>
      <td className={`${tableClasses.notesFixedCell} ${columnPropertys.wbArticle}`}></td>
      <td className={`${tableClasses.notesFixedCell} ${columnPropertys.empty} text-gray-500`}>
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
