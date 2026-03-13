import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import React, { useMemo, useRef, useEffect, useState } from 'react';
import { observer } from 'mobx-react-lite';
import goodsStore from './Stores/GoodsStore';
import { viewStore } from './Stores/ViewStore';
import notesStore from './Stores/NotesStore';
import categorysTotalsStore from './Stores/CategorysTotalsStore';
import NotesModal from './Components/GoodDetailsTable/Components/NotesModal';
import echo from '@/echo';
import { MainTable } from './Components/MainTable';
import { generateDateHeaders, calculateCategorysTotalsFromGoods } from './utils';
import './styles.css';

export default observer(function VirtualizedMainView({ shop, workSpace, goods: initialGoods, initialViewState }) {
    const workSpaceSettings = JSON.parse(workSpace.view_settings.settings);
    const viewId = workSpace.view_settings.view.id;

    useEffect(() => {
        goodsStore.setGoods(initialGoods);
    }, [initialGoods]);

    useEffect(() => {
        viewStore.setInitialState(initialViewState, workSpace.id, viewId);
    }, [initialViewState]);

    useEffect(() => {
        const calculatedTotals = calculateCategorysTotalsFromGoods(goodsStore.goods);
        categorysTotalsStore.setCategorysTotals(calculatedTotals);
    }, [goodsStore.goods]);

    useEffect(() => {
        const loadNotes = async () => {
            await notesStore.fetchAllNotes(viewId, shop.id);
        };
        loadNotes();
    }, [viewId, shop.id]);

    useEffect(() => {
        const channelName = `shop.${shop.id}.notes`;

        echo.channel(channelName)
            .listen('NoteUpdated', (data) => {
                notesStore.handleNoteUpdated(data.goodId, data.date, data.exists);
            })
            .error((err) => {
                console.error('Ошибка вещания:', err);
            });;

        return () => {
            echo.leave(channelName);
        };
    }, [shop.id]);

    const displayDays = viewStore.daysDisplay || workSpaceSettings.days;
    const dates = generateDateHeaders(displayDays);
    const tableContainerRef = useRef(null);

    const filteredGoods = useMemo(() => {
        let result = goodsStore.goods;

        if (viewStore.showOnlySelected) {
            result = result.filter(item => viewStore.selectedItems.includes(item.id));
        }

        if (viewStore.searchQuery && viewStore.searchResults.length > 0) {
            result = result.filter(item => viewStore.searchResults.includes(item.id));
        }

        return result;
    }, [
        viewStore.showOnlySelected,
        viewStore.selectedItems,
        viewStore.searchQuery,
        viewStore.searchResults,
        goodsStore.goods,
        viewStore.sortedColumn,
        viewStore.sortDirection
    ]);

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
            <div ref={tableContainerRef} className={`table-container`}>
                <MainTable
                    shop={shop}
                    dates={dates}
                    displayDays={displayDays}
                    workSpaceSettings={workSpaceSettings}
                    filteredGoods={filteredGoods}
                    tableContainerRef={tableContainerRef}
                />
            </div>
            <NotesModal />
        </AuthenticatedLayout>
    );
});
