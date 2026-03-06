import React from 'react';
import { observer } from 'mobx-react-lite';
import { PlusIcon } from '@heroicons/react/24/outline';
import notesStore from '../../../Stores/NotesStore';
import { viewStore } from '../../../Stores/ViewStore';

const NoteIconCell = observer(({ goodId, date, value }) => {
  const hasNotes = notesStore.hasNotes(goodId, date);
  const viewId = viewStore.viewId || 2;

  const handleClick = (e) => {
    e.stopPropagation();
    notesStore.openModal({ date, goodId, viewId });
  };

  const iconColor = hasNotes ? 'text-green-500 hover:text-green-700' : 'text-gray-300 hover:text-gray-400';
  const title = hasNotes ? "Есть заметки" : "Добавить заметку";

  return (
    <div className="flex items-center justify-between">
      <button
        onClick={handleClick}
        className={`bg-transparent rounded transition-colors ${iconColor}`}
        title={title}
      >
        <PlusIcon className="w-4 h-4" />
      </button>
      <span>{value}</span>
    </div>
  );
});

export default NoteIconCell;
