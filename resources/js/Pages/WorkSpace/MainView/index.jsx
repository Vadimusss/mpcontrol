import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import React, { useMemo, useCallback, useEffect } from 'react';
import { TableHeader } from './Components/TableHeader';
import { ProductRow } from './Components/ProductRow';
import { SubRow } from './Components/SubRow';
import { NotesRow } from './Components/NotesRow';
import NotesModal from './Modals/NotesModal';
import { observer } from 'mobx-react-lite';
import notesStore from './Stores/NotesStore';
import { goodsStore } from './Stores/GoodsStore';
import { viewStore } from './Stores/ViewStore';
import { tableClasses } from './styles';

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
  const workSpaceSettings = JSON.parse(workSpace.view_settings.settings);
  const viewId = workSpace.view_settings.view.id;

  useEffect(() => {
    goodsStore.setGoods(initialGoods);
  }, [initialGoods]);

  useEffect(() => {
    viewStore.workSpaceId = workSpace.id;
    viewStore.viewId = viewId;
    viewStore.setInitialState(initialViewState);
  }, [initialViewState]);

  const dates = generateDateHeaders(workSpaceSettings.days);

  const handleOpenNotes = useCallback((date, goodId) => {
    notesStore.openModal({ date, goodId, viewId });
    notesStore.setRefreshCallback(() => {
      goodsStore.updateNoteExists(date, goodId, viewId);
    });
  }, [viewId]);

  const filteredGoods = useMemo(() => {
    return viewStore.showOnlySelected
      ? goodsStore.goods.filter(item => viewStore.selectedItems.includes(item.id))
      : goodsStore.goods;
  }, [viewStore.showOnlySelected, viewStore.selectedItems, goodsStore.goods]);

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

      <div className={tableClasses.tableContainer}>
        <table className={tableClasses.table}>
          <TableHeader
            shop={shop}
            workSpaceSettings={workSpaceSettings}
            dates={dates}
          />
          <tbody className={tableClasses.tbody}>
            {filteredGoods?.map((item) => (
              <React.Fragment key={`${item.id}-0`}>
                <ProductRow
                  item={item}
                  dates={dates}
                />
                {viewStore.expandedRows[item.id] && item.subRowsMetadata?.map((metadata, i) => (
                  <SubRow
                    key={`${item.id}-${i}`}
                    item={item}
                    metadata={metadata}
                    dates={dates}
                  />
                ))}
                {viewStore.expandedRows[item.id] &&
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
