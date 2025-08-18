import { makeAutoObservable, runInAction } from 'mobx';
import notesStore from './NotesStore';
import { viewStore } from './ViewStore';

class GoodsStore {
  goods = [];
  articleSortDirection = null;

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
}

export const goodsStore = new GoodsStore();
