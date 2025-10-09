import { makeAutoObservable } from 'mobx';
import { goodsStore } from './GoodsStore';
import { apiClient } from '../Utils';

class ViewStore {
  expandedGoodId = null;
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
    const { 
      selectedItems = [], 
      showOnlySelected = false,
      sortField = 'article',
      sortDirection = 'asc'
    } = state;
  }

  get isExpanded() {
    return this.expandedGoodId !== null;
  }

  toggleItemSelection(id) {
    this.selectedItems = this.selectedItems.includes(id)
      ? this.selectedItems.filter(item => item !== id)
      : [...this.selectedItems, id];
    this.saveState();
  }

  toggleRow(id) {
    if (this.expandedGoodId === id) {
      this.expandedGoodId = null;
    } else {
      this.expandedGoodId = id;
    }
    this.saveState();
  }

  toggleShowOnlySelected() {
    this.showOnlySelected = !this.showOnlySelected;
    this.saveState();
  }

  saveState() {
    const stateToSave = {
      expandedRows: this.expandedGoodId ? [this.expandedGoodId] : [],
      selectedItems: this.selectedItems,
      showOnlySelected: this.showOnlySelected,
      sortField: 'article',
      sortDirection: goodsStore.articleSortDirection
    };

    apiClient.post(`/${this.workSpaceId}/${this.viewId}`, { viewState: stateToSave })
      .catch(error => console.error('Error saving view state:', error));
  }
}

export const viewStore = new ViewStore();
