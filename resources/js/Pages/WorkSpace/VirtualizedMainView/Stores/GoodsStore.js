import { makeAutoObservable, runInAction } from 'mobx';
import notesStore from './NotesStore';
import { viewStore } from './ViewStore';
import { apiClient } from '../Utils';

class GoodsStore {
  goods = [];
  articleSortDirection = null;
  goodDetails = null;
  isLoadingGoodDetails = false;

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
      if (this.goodDetails) {
        if (!this.goodDetails.notesData) {
          this.goodDetails.notesData = {};
        }
        this.goodDetails.notesData[date] = isNotesExists;
      }
    });
  }

  async loadGoodDetails(shopId, goodId, dates) {
    runInAction(() => {
      this.isLoadingGoodDetails = true;
    });

    try {
      const response = await apiClient.get(`shops/${shopId}/goods/${goodId}/details`, {
        params: { 'dates[]': dates },
      });
      runInAction(() => {
        this.goodDetails = response.data;
      });
    } catch (error) {
      console.error('Error loading good details:', error);
      runInAction(() => {
        this.goodDetails = null;
      });
    } finally {
      runInAction(() => {
        this.isLoadingGoodDetails = false;
      });
    }
  }

  clearGoodDetails() {
    runInAction(() => {
      this.goodDetails = null;
      this.isLoadingGoodDetails = false;
    });
  }
}

export const goodsStore = new GoodsStore();
export default goodsStore;
