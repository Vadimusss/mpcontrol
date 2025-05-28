import { makeAutoObservable, runInAction, observable } from 'mobx';
import axios from 'axios';
import Cookies from 'js-cookie';

const apiClient = axios.create({
  baseURL: '/api',
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'X-XSRF-TOKEN': Cookies.get('XSRF-TOKEN'),
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  withCredentials: true
});

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
