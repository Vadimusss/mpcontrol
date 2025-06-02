import { makeAutoObservable } from 'mobx';
import { goodsStore } from './GoodsStore';
import { apiClient } from '../Utils';

class ViewStore {
  expandedRows = {};
  selectedItems = [];
  showOnlySelected = false;
  apiClient = null;
  workSpaceId = null;
  viewId = null;

  constructor(initialState = {}, workSpaceId = null, viewId = null) {
    makeAutoObservable(this);
    this.workSpaceId = workSpaceId;
    this.viewId = viewId;
    this.setInitialState(initialState);
  }

  setInitialState(state = {}) {
    const { expandedRows = [], selectedItems = [], showOnlySelected = false } = state;
    this.expandedRows = this.getExpandedRows(expandedRows);
    this.selectedItems = selectedItems;
    this.showOnlySelected = showOnlySelected;
  }

  get allExpanded() {
    return Object.keys(this.expandedRows).length === goodsStore.goods.length;
  }

  toggleItemSelection(id) {
    this.selectedItems = this.selectedItems.includes(id)
      ? this.selectedItems.filter(item => item !== id)
      : [...this.selectedItems, id];
    this.saveState();
  }

  toggleAllRows() {
    if (this.allExpanded) {
      this.expandedRows = {};
    } else {
      this.expandedRows = goodsStore.goods.reduce((acc, item) => ({ ...acc, [item.id]: true }), {});
    }
    this.saveState();
  }

  toggleRow(id) {
    const newExpanded = { ...this.expandedRows };
    if (newExpanded[id]) {
      delete newExpanded[id];
    } else {
      newExpanded[id] = true;
    }
    this.expandedRows = newExpanded;
    this.saveState();
  }

  toggleShowOnlySelected() {
    this.showOnlySelected = !this.showOnlySelected;
    this.saveState();
  }

  saveState() {
    const stateToSave = {
      expandedRows: this.getExpandedIds(this.expandedRows),
      selectedItems: this.selectedItems,
      showOnlySelected: this.showOnlySelected
    };

    apiClient.post(`/${this.workSpaceId}/${this.viewId}`, { viewState: stateToSave })
      .catch(error => console.error('Error saving view state:', error));
  }

  getExpandedIds = (rows) =>
    Object.entries(rows)
      .filter(([_, isExpanded]) => isExpanded)
      .map(([id]) => id);

  getExpandedRows = (ids) =>
    ids.reduce((acc, id) => ({ ...acc, [id]: true }), {});
}

export const viewStore = new ViewStore();
