import { makeAutoObservable, runInAction } from 'mobx';
import notesStore from './NotesStore';

class GoodsStore {
  goods = [];

  constructor() {
    makeAutoObservable(this);
  }

  setGoods(goods) {
    this.goods = goods;
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
