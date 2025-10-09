import React, { useEffect } from 'react';
import { observer } from 'mobx-react-lite';
import { SubRow } from './SubRow';
import { NotesRow } from './NotesRow';
import { goodsStore } from '../Stores/GoodsStore';
import { viewStore } from '../Stores/ViewStore';
import { tableClasses } from '../styles';

export const SubRowsLoader = observer(({ goodId, dates, onOpenNotes }) => {
  const subRowsData = goodsStore.getSubRows(goodId);
  const isLoading = goodsStore.isLoadingSubRows(goodId);

  useEffect(() => {
    if (viewStore.expandedGoodId === goodId && !goodsStore.hasSubRows(goodId) && !isLoading) {
      goodsStore.loadSubRows(goodId);
    }
  }, [goodId, isLoading]);

  if (isLoading) {
    return (
      <tr className={tableClasses.row}>
        <td colSpan={dates.length + 18} className={`${tableClasses.cell} text-center py-4`}>
          <div className="flex justify-center items-center">
            <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
            <span className="ml-2 text-gray-500">Загрузка данных...</span>
          </div>
        </td>
      </tr>
    );
  }

  if (!subRowsData) {
    return null;
  }

  const { subRowsMetadata, salesData } = subRowsData;

  return (
    <>
      {subRowsMetadata?.slice(0, 8).map((metadata, i) => (
        <SubRow
          key={`${goodId}-${i}`}
          item={{ ...goodsStore.goods.find(g => g.id === goodId), salesData }}
          metadata={metadata}
          dates={dates}
        />
      ))}
      
      <NotesRow
        isNotesExists={goodsStore.goods.find(g => g.id === goodId)?.isNotesExists || {}}
        goodId={goodId}
        dates={dates}
        onOpenNotes={onOpenNotes}
      />
      
      {subRowsMetadata?.slice(8).map((metadata, i) => (
        <SubRow
          key={`${goodId}-${i + 8}`}
          item={{ ...goodsStore.goods.find(g => g.id === goodId), salesData }}
          metadata={metadata}
          dates={dates}
        />
      ))}
    </>
  );
});
