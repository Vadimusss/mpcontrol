import { makeAutoObservable, runInAction } from 'mobx';
import notesStore from './NotesStore';
import { viewStore } from './ViewStore';
import { apiClient } from '../Utils';

class GoodsStore {
  goods = [];
  articleSortDirection = null;
  loadedSubRows = new Map();
  loadingSubRows = new Set();

  constructor() {
    makeAutoObservable(this);
  }

  setGoods(goods) {
    this.goods = goods;
    if (this.articleSortDirection) {
      this.sortByArticle();
    }
  }

  toggleArticleSort() {
    this.articleSortDirection = this.articleSortDirection === 'asc' ? 'desc' : 'asc';
    this.sortByArticle();
    viewStore.saveState();
  }

  sortByArticle() {
    this.goods.sort((a, b) => {
      return this.articleSortDirection === 'asc' 
        ? a.article.localeCompare(b.article)
        : b.article.localeCompare(a.article);
    });
  }

  async updateNoteExists(date, goodId, viewId) {
    const isNotesExists = await notesStore.isNotesExists({ date, goodId, viewId });
    runInAction(() => {
      const good = this.goods.find(g => g.id === goodId);
      if (good) {
        good.isNotesExists = {
          ...good.isNotesExists,
          [date]: isNotesExists
        };
      }
    });
  }

  async loadSubRows(goodId) {
    if (this.loadingSubRows.has(goodId) || this.loadedSubRows.has(goodId)) {
      return;
    }

    this.loadingSubRows.add(goodId);

    try {
      const response = await apiClient.get(`/workspaces/${viewStore.workSpaceId}/goods/${goodId}/subrows`);
      
      runInAction(() => {
        this.loadedSubRows.set(goodId, response.data);
        this.loadingSubRows.delete(goodId);
      });
    } catch (error) {
      runInAction(() => {
        console.error('Error loading subrows:', error);
        this.loadingSubRows.delete(goodId);
      });
    }
  }

  getSubRows(goodId) {
    return this.loadedSubRows.get(goodId);
  }

  hasSubRows(goodId) {
    return this.loadedSubRows.has(goodId);
  }

  isLoadingSubRows(goodId) {
    return this.loadingSubRows.has(goodId);
  }

  clearSubRows() {
    this.loadedSubRows.clear();
    this.loadingSubRows.clear();
  }
}

export const goodsStore = new GoodsStore();
