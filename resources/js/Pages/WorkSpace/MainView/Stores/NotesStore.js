import { makeAutoObservable, runInAction, observable } from 'mobx';
import { apiClient } from '../utils';

class NotesStore {
  isOpen = false;
  currentNoteKey = null;
  notes = [];
  isLoading = false;
  error = null;
  notesExistenceMap = new Map();

  constructor() {
    makeAutoObservable(this);
  }

  async fetchNotes(noteKey) {
    this.isLoading = true;
    this.error = null;

    try {
      const { data } = await apiClient.get('/notes', {
        params: noteKey,
      });
      runInAction(() => {
        this.notes = data;
      });
    } catch (error) {
      runInAction(() => {
        this.error = error.response?.data?.message || 'Failed to fetch notes';
      });
    } finally {
      runInAction(() => {
        this.isLoading = false;
      });
    }
  }

  async createNote(text) {
    try {
      await apiClient.post('/notes', {
        ...this.currentNoteKey,
        text
      });
      await this.fetchNotes(this.currentNoteKey);
      return { success: true };
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to create note'
      };
    }
  }

  async updateNote(id, text) {
    try {
      await apiClient.put(`/notes/${id}`, { text });
      await this.fetchNotes(this.currentNoteKey);
      return { success: true };
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to update note'
      };
    }
  }

  async deleteNote(id) {
    try {
      await apiClient.delete(`/notes/${id}`);
      await this.fetchNotes(this.currentNoteKey);
      return { success: true };
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to delete note'
      };
    }
  }

  async isNotesExists(noteKey) {
    try {
      const { data } = await apiClient.get('/notes/isNotesExists', {
        params: noteKey,
      });
      return data !== '';
    } catch (error) {
      return false;
    }
  };

  async fetchAllNotes(viewId, shopId = null) {
    try {
      const { data } = await apiClient.get('/notes/all', {
        params: { view_id: viewId, shop_id: shopId }
      });
      
      runInAction(() => {
        this.notesExistenceMap.clear();
        
        data.forEach(note => {
          const key = `${note.good_id}-${note.date}`;
          this.notesExistenceMap.set(key, true);
        });
      });
      
      return { success: true };
    } catch (error) {
      return { success: false, error: error.message };
    }
  }

  hasNotes(goodId, date) {
    const key = `${goodId}-${date}`;
    return this.notesExistenceMap.get(key) || false;
  }

   setNotesExistence(goodId, date, exists) {
    const key = `${goodId}-${date}`;
    runInAction(() => {
      this.notesExistenceMap.set(key, exists);
    });
  }

  handleNoteUpdated(goodId, date, exists) {
    this.setNotesExistence(goodId, date, exists);
  }

  openModal(noteKey) {
    this.isOpen = true;
    this.currentNoteKey = noteKey;
    this.fetchNotes(noteKey);
  }

  closeModal() {
    this.isOpen = false;
    this.currentNoteKey = null;
    this.notes = [];
    this.error = null;

    if (this.refreshCallback) {
      this.refreshCallback();
      this.refreshCallback = null;
    }
  }

  setRefreshCallback(callback) {
    this.refreshCallback = callback;
  }
}

const notesStore = new NotesStore();
export default notesStore;
