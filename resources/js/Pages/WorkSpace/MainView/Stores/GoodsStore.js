import { makeAutoObservable, runInAction } from 'mobx';
import notesStore from './NotesStore';
import viewStore from './ViewStore';
import { apiClient } from '../utils';

class GoodsStore {
  static sortConfig = {
    'article': 'string',
    'status': 'string',
    'stocks.totals': 'number',
    'totalsOrdersCount': 'number',
    'days_of_stock': 'number'
  }

  goods = [];
  goodDetails = null;
  isLoadingGoodDetails = false;

  constructor() {
    makeAutoObservable(this);
  }

  setGoods(goods) {
    this.goods = goods;
  }

  toggleSort(columnKey) {
    viewStore.sortDirection = viewStore.sortDirection === 'asc' ? 'desc' : 'asc';
    viewStore.sortedColumn = columnKey;

    const sortType = GoodsStore.sortConfig[columnKey] || 'number';
    this.genericSort(columnKey, sortType);
  }

  genericSort(path, sortType) {
    const sortedGoods = [...this.goods].sort((a, b) => {
      const valueA = this.getNestedValue(a, path);
      const valueB = this.getNestedValue(b, path);

      if (sortType === 'string') {
        const strA = String(valueA || '');
        const strB = String(valueB || '');
        return viewStore.sortDirection === 'asc'
          ? strA.localeCompare(strB)
          : strB.localeCompare(strA);
      } else {
        const numA = parseFloat(valueA) || 0;
        const numB = parseFloat(valueB) || 0;
        return viewStore.sortDirection === 'asc'
          ? numA - numB
          : numB - numA;
      }
    });

    this.goods = sortedGoods;
  }

  getNestedValue(obj, path) {
    return path.split('.').reduce((current, key) => {
      return current && current[key] !== undefined ? current[key] : 0;
    }, obj);
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
