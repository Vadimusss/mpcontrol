import { observer } from 'mobx-react-lite';
import { useState } from 'react';
import { usePage } from '@inertiajs/react';
import Modal from '@/Components/Modal';
import { PencilIcon, TrashIcon } from '@heroicons/react/20/solid';
import notesStore from '../Stores/NotesStore';
import PrimaryButton from '@/Components/PrimaryButton';

const NotesModal = observer(() => {
  const { auth } = usePage().props;
  const [noteText, setNoteText] = useState('');
  const [editingNoteId, setEditingNoteId] = useState(null);

  const handleSubmit = async () => {
    if (editingNoteId) {
      const { success, error } = await notesStore.updateNote(editingNoteId, noteText);
      if (success) {
        setEditingNoteId(null);
        setNoteText('');
      } else if (error) {
        console.error('Error updating note:', error);
      }
    } else {
      const { success, error } = await notesStore.createNote(noteText);
      if (success) {
        setNoteText('');
      } else if (error) {
        console.error('Error creating note:', error);
      }
    }
  };

  if (!notesStore.isOpen) return null;

  const { date, goodId, viewId } = notesStore.currentNoteKey || {};

  return (
    <Modal show={notesStore.isOpen} onClose={() => notesStore.closeModal()} maxWidth="xl">
      <div className="bg-white rounded-lg p-6">
        <div className="flex justify-between items-center mb-4">
          <div>
            <h2 className="text-xl font-bold">{date}</h2>
          </div>
          <button
            onClick={() => notesStore.closeModal()}
            className="text-gray-500 hover:text-gray-700"
          >
            &times;
          </button>
        </div>

        {notesStore.error && (
          <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {notesStore.error}
          </div>
        )}

        <div className="space-y-3 mb-4 max-h-60 overflow-y-auto">
          {notesStore.isLoading ? (
            <div>Загружаем заметки...</div>
          ) : notesStore.notes.length === 0 ? (
            <div>Пока заметок нет</div>
          ) : (
            notesStore.notes.map(note => (
              <div key={note.id} className="border-b pb-2">
                <div className="text-sm text-gray-500">
                  <div>{note.creator?.email || 'Unknown'}</div>
                </div>
                <div className="flex justify-between items-center">
                  <p>{note.text}</p>
                  {note.user_id === auth.user.id && (
                    <div className="flex items-center space-x-2">
                      <button
                        onClick={() => {
                          setEditingNoteId(note.id);
                          setNoteText(note.text);
                        }}
                        className="text-gray-600 hover:text-gray-800 p-1"
                        title="Edit"
                      >
                        <PencilIcon className="h-4 w-4" />
                      </button>
                      <button
                        onClick={async () => {
                          const { error } = await notesStore.deleteNote(note.id);
                          if (error) console.error('Error deleting note:', error);
                        }}
                        className="text-gray-600 hover:text-gray-800 p-1"
                        title="Delete"
                      >
                        <TrashIcon className="h-4 w-4" />
                      </button>
                    </div>
                  )}
                </div>
              </div>
            ))
          )}
        </div>

        <div className="mt-4">
          <textarea
            value={noteText}
            onChange={(e) => setNoteText(e.target.value)}
            placeholder={editingNoteId ? "Редактировать..." : "Текст"}
            className="w-full border rounded px-3 py-2 mb-2"
            rows={3}
          />
          <div className="flex">
            <PrimaryButton
              onClick={handleSubmit}
              disabled={!noteText.trim() || notesStore.isLoading}
              className="mt-4 max-w-fit mr-2"
            >
              {notesStore.isLoading ? 'Processing...' : editingNoteId ? 'Обновить заметку' : 'Новая заметка'}
            </PrimaryButton>
            {editingNoteId && (
              <PrimaryButton
                onClick={() => {
                  setEditingNoteId(null);
                  setNoteText('');
                }}
                className="mt-4 max-w-fit"
              >
                Отмена
              </PrimaryButton>
            )}
          </div>
        </div>
      </div>
    </Modal>
  );
});

export default NotesModal;
