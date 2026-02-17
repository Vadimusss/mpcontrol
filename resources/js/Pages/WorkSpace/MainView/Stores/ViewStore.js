import { makeAutoObservable } from 'mobx';
import { goodsStore } from './GoodsStore';
import { apiClient } from '../utils';

class ViewStore {
  selectedItems = [];
  showOnlySelected = false;
  apiClient = null;
  workSpaceId = null;
  viewId = null;
  sortDirection = null;
  sortedColumn = null;
  searchQuery = '';
  searchResults = [];
  isSearchActive = false;

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
      sortedColumn = null,
      sortDirection = 'asc'
    } = state;

    this.selectedItems = selectedItems;
    this.showOnlySelected = showOnlySelected;
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
    this.saveState();
  }

  toggleShowOnlySelected() {
    this.showOnlySelected = !this.showOnlySelected;
    this.saveState();
  }

  setSearchQuery(query) {
    this.searchQuery = query;
    this.performSearch();
  }

  performSearch() {
    if (!this.searchQuery.trim()) {
      this.searchResults = [];
      this.isSearchActive = false;
      return;
    }

    const query = this.searchQuery.toLowerCase().trim();
    const results = [];

    goodsStore.goods.forEach((good, index) => {
      const searchFields = [
        good.article?.toString().toLowerCase() || '',
        good.name?.toString().toLowerCase() || '',
        good.variant?.toString().toLowerCase() || '',
        good.wbArticle?.toString().toLowerCase() || ''
      ];

      const found = searchFields.some(field => field.includes(query));
      if (found) {
        results.push(good.id);
      }
    });

    this.searchResults = results;
    this.isSearchActive = results.length > 0;
  }

  clearSearch() {
    this.searchQuery = '';
    this.searchResults = [];
    this.isSearchActive = false;
  }

  saveState() {
    const stateToSave = {
      selectedItems: this.selectedItems,
      showOnlySelected: this.showOnlySelected,
      sortedColumn: this.sortedColumn,
      sortDirection: this.sortDirection
    };

    apiClient.post(`/${this.workSpaceId}/${this.viewId}`, { viewState: stateToSave })
      .catch(error => console.error('Error saving view state:', error));
  }
}

export const viewStore = new ViewStore();
export default viewStore;
