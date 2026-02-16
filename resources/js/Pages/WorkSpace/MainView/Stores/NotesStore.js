import { makeAutoObservable, runInAction } from 'mobx';
import { apiClient } from '../utils';

class NotesStore {
  isOpen = false;
  currentNoteKey = null;
  notes = [];
  isLoading = false;
  error = null;

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
      console.error('Failed isNotesExists method:', error);
      return false;
    }
  };

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
