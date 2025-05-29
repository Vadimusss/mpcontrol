import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import React, { useState, useMemo, useCallback, useEffect } from 'react';
import { TableHeader } from './Components/TableHeader';
import { ProductRow } from './Components/ProductRow';
import { SubRow } from './Components/SubRow';
import { NotesRow } from './Components/NotesRow';
import NotesModal from './Modals/NotesModal';
import { useApiClient } from './Hooks/useApiClient';
import { observer } from 'mobx-react-lite';
import notesStore from './Stores/NotesStore';
import { goodsStore } from './Stores/GoodsStore'
import { tableClasses } from './styles';

const getExpandedIds = (rows) =>
  Object.entries(rows)
    .filter(([_, isExpanded]) => isExpanded)
    .map(([id]) => id);

const getExpandedRows = (ids) =>
  ids.reduce((acc, id) => ({ ...acc, [id]: true }), {});

const generateDateHeaders = (days) => {
  const result = [];
  const currentDate = new Date();

  for (let i = 0; i < days; i++) {
    const date = new Date(currentDate);
    date.setDate(currentDate.getDate() - i);

    const dateString = date.toISOString().split("T")[0];

    result.push(dateString);
  }

  return result.reverse();
};

export default observer(function MainView({ shop, workSpace, goods: initialGoods, initialViewState }) {
  const apiClient = useApiClient();
  const workSpaceSettings = JSON.parse(workSpace.view_settings.settings);
  const viewId = workSpace.view_settings.view.id;

  const [viewState, setViewState] = useState({
    ...initialViewState,
    expandedRows: getExpandedRows(initialViewState.expandedRows || [])
  });
  const [allExpanded, setAllExpanded] = useState(false);
  
  useEffect(() => {
    goodsStore.setGoods(initialGoods);
  }, [initialGoods]);
  useEffect(() => {
    const allExpanded = Object.keys(viewState.expandedRows).length === goodsStore.goods.length;
    setAllExpanded(allExpanded);
  }, [viewState.expandedRows, goodsStore.goods.length]);

  const toggleItemSelection = (id) => {
    setViewState(prev => {
      const newState = {
        ...prev,
        selectedItems: prev.selectedItems.includes(id)
          ? prev.selectedItems.filter(item => item !== id)
          : [...prev.selectedItems, id]
      };
      saveViewState(newState);
      return newState;
    });
  };

  const toggleAllRows = () => {
    const newExpanded = !allExpanded;
    setAllExpanded(newExpanded);

    setViewState(prev => {
      const newState = {
        ...prev,
        expandedRows: newExpanded
          ? goodsStore.goods.reduce((acc, item) => ({ ...acc, [item.id]: true }), {})
          : {}
      };
      saveViewState(newState);
      return newState;
    });
  };

  const toggleRow = (id) => {
    setViewState(prev => {
      const newExpanded = { ...prev.expandedRows };
      if (newExpanded[id]) {
        delete newExpanded[id];
      } else {
        newExpanded[id] = true;
      }
      const newState = { ...prev, expandedRows: newExpanded };
      saveViewState(newState);
      return newState;
    });
  };

  const dates = generateDateHeaders(workSpaceSettings.days);

  const saveViewState = useCallback((state) => {
    const stateToSave = {
      ...state,
      expandedRows: getExpandedIds(state.expandedRows)
    };
    apiClient.post(`/${workSpace.id}/${viewId}`,
      { viewState: stateToSave }
    ).then(response => {
    }).catch(error => {
      console.error('Error saving state:', error);
    });
  }, [workSpace.id, viewId]);

  const toggleShowOnlySelected = () => {
    setViewState(prev => {
      const newState = {
        ...prev,
        showOnlySelected: !prev.showOnlySelected
      };
      saveViewState(newState);
      return newState;
    });
  };

  const handleOpenNotes = useCallback((date, goodId) => {
    notesStore.openModal({ date, goodId, viewId });
    notesStore.setRefreshCallback(() => {
      goodsStore.updateNoteExists(date, goodId, viewId);
    });
  }, [viewId]);

  const filteredGoods = useMemo(() => {
    return viewState.showOnlySelected
      ? goodsStore.goods.filter(item => viewState.selectedItems.includes(item.id))
      : goodsStore.goods;
  }, [viewState.showOnlySelected, viewState.selectedItems, goodsStore.goods]);

  return (
    <AuthenticatedLayout
      navigation={true}
      shopId={shop.id}
      header={
        <h2 className="text-xl font-semibold leading-tight text-gray-800">
          {shop.name}/Рабочая область {workSpace.name}
        </h2>
      }>
      <Head title={`Магазин ${shop.name}`} />

      <div>
        <table className={tableClasses.table}>
          <TableHeader
            shop={shop}
            workSpaceSettings={workSpaceSettings}
            dates={dates}
            allExpanded={allExpanded}
            showOnlySelected={viewState.showOnlySelected}
            onToggleAllRows={toggleAllRows}
            onToggleShowOnlySelected={toggleShowOnlySelected}
          />
          <tbody className={tableClasses.tbody}>
            {filteredGoods?.map((item) => (
              <React.Fragment key={`${item.id}-0`}>
                <ProductRow
                  item={item}
                  dates={dates}
                  viewState={viewState}
                  onToggleItemSelection={toggleItemSelection}
                  onToggleRow={toggleRow}
                />
                {viewState.expandedRows[item.id] && item.subRowsMetadata?.map((metadata, i) => (
                  <SubRow
                    key={`${item.id}-${i}`}
                    item={item}
                    metadata={metadata}
                    dates={dates}
                  />
                ))}
                {viewState.expandedRows[item.id] &&
                  <NotesRow
                    isNotesExists={item.isNotesExists || {}}
                    goodId={item.id}
                    dates={dates}
                    onOpenNotes={handleOpenNotes}
                  />
                }
              </React.Fragment>
            ))}
          </tbody>
        </table>
        <NotesModal maxWidth={'xl'} />
      </div>
    </AuthenticatedLayout>
  );
})
