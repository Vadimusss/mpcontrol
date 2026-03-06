import { makeAutoObservable, runInAction } from 'mobx';
import { goodsStore } from './GoodsStore';
import { apiClient } from '../utils';

class ViewStore {
  selectedItems = [];
  showOnlySelected = false;
  daysDisplay = null;
  goodDetailsDaysDisplay = null;
  apiClient = null;
  workSpaceId = null;
  viewId = null;
  sortDirection = null;
  sortedColumn = null;
  searchQuery = '';
  searchResults = [];
  isSearchActive = false;
  searchTimeout = null;

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
      daysDisplay = null,
      goodDetailsDaysDisplay = null,
      sortedColumn = null,
      sortDirection = 'asc'
    } = state;

    this.selectedItems = selectedItems;
    this.showOnlySelected = showOnlySelected;
    this.daysDisplay = daysDisplay;
    this.goodDetailsDaysDisplay = goodDetailsDaysDisplay;
    this.sortedColumn = sortedColumn;
    this.sortDirection = sortDirection;
    this.searchQuery = '';
    this.searchResults = [];
    this.isSearchActive = false;
  }

  get isExpanded() {
    return this.expandedGoodId !== null;
  }

  isSortedColumn(columnKey) {
    return columnKey === this.sortedColumn ? this.sortDirection : null;
  }

  toggleItemSelection(id) {
    this.selectedItems = this.selectedItems.includes(id)
      ? this.selectedItems.filter(item => item !== id)
      : [...this.selectedItems, id];
    this.debouncedSaveState();
  }

  toggleShowOnlySelected() {
    this.showOnlySelected = !this.showOnlySelected;
    this.debouncedSaveState();
  }

  setDaysDisplay(days) {
    this.daysDisplay = days;
    this.debouncedSaveState();
  }

  setGoodDetailsDaysDisplay(days) {
    this.goodDetailsDaysDisplay = days;
    this.debouncedSaveState();
  }

  setSearchQuery(query) {
    this.searchQuery = query;
    
    if (this.searchTimeout) {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = null;
    }
    
    if (!query.trim()) {
      this.clearSearch();
      return;
    }

    this.performSearch();
  }

  performSearch() {
    const query = this.searchQuery.toLowerCase().trim();
    
    if (!query) {
      this.searchResults = [];
      this.isSearchActive = false;
      return;
    }

    const results = [];
    const goods = goodsStore.goods;

    for (let i = 0; i < goods.length; i++) {
      const good = goods[i];
      
      if ((good.article && String(good.article).toLowerCase().includes(query)) ||
          (good.name && String(good.name).toLowerCase().includes(query)) ||
          (good.variant && String(good.variant).toLowerCase().includes(query)) ||
          (good.status && String(good.status).toLowerCase().includes(query)) ||
          (good.wbArticle && String(good.wbArticle).toLowerCase().includes(query))) {
        results.push(good.id);
      }
    }

    this.searchResults = results;
    this.isSearchActive = results.length > 0;
  }

  clearSearch() {
    if (this.searchTimeout) {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = null;
    }
    
    runInAction(() => {
      this.searchQuery = '';
      this.searchResults = [];
      this.isSearchActive = false;
    });
  }

  saveState() {
    const stateToSave = {
      selectedItems: this.selectedItems,
      showOnlySelected: this.showOnlySelected,
      daysDisplay: this.daysDisplay,
      goodDetailsDaysDisplay: this.goodDetailsDaysDisplay,
    };

    apiClient.post(`/${this.workSpaceId}/${this.viewId}`, { viewState: stateToSave })
      .catch(error => console.error('Error saving view state:', error));
  }

  saveTimeout = null;
  debouncedSaveState() {
    if (this.saveTimeout) {
      clearTimeout(this.saveTimeout);
    }
    
    this.saveTimeout = setTimeout(() => {
      this.saveState();
    }, 500);
  }
}

export const viewStore = new ViewStore();
export default viewStore;
