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
    console.log('Opening notes modal for:', { date, goodId, viewId });
    notesStore.openModal({ date, goodId, viewId });
  };

  const iconColor = hasNotes ? 'text-green-500' : 'text-gray-400';
  const title = hasNotes ? "Есть заметки" : "Добавить заметку";

  return (
    <div className="flex items-center justify-between">
      <button
        onClick={handleClick}
        className={`rounded hover:bg-gray-100 transition-colors ${iconColor}`}
        title={title}
      >
        <PlusIcon className="w-3 h-3" />
      </button>
      <span>{value}</span>
    </div>
  );
});

export default NoteIconCell;
