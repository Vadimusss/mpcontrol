import { makeAutoObservable } from 'mobx';

class CategorysTotalsStore {
  categorysTotalsData = {};

  constructor() {
    makeAutoObservable(this);
  }

  setCategorysTotals(data) {
    this.categorysTotalsData = data;
  }

  getCategorys() {
    return Object.keys(this.categorysTotalsData);
  }

  getCategoryData(categoryName) {
    return this.categorysTotalsData[categoryName];
  }
}

export const categorysTotalsStore = new CategorysTotalsStore();
export default categorysTotalsStore;