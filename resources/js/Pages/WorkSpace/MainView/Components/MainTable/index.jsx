import React, { useMemo, useState, useEffect, useCallback } from 'react';
import { observer } from 'mobx-react-lite';
import { viewStore } from '../../Stores/ViewStore';
import goodsStore from '../../Stores/GoodsStore';
import categorysTotalsStore from '../../Stores/CategorysTotalsStore';
import { Colgroup } from './Components/Colgroup';
import { TableHeader } from './Components/TableHeader';
import { TableBody } from './Components/TableBody';
import { ToolTip } from './Components/ToolTip';
import { GoodDetailsModal } from './Components/GoodDetailsModal';
import { CategorySelectionModal } from './Components/CategorySelectionModal';
import { SearchBar } from './Components/SearchBar';
import { useReactTable, getCoreRowModel, getSortedRowModel } from '@tanstack/react-table';
import { createColumns } from './columns';
import { getCategoryRows } from '../../utils';
import '../../styles.css';

export const MainTable = observer(({
    dates,
    shop,
    displayDays,
    workSpaceSettings,
    filteredGoods,
    tableContainerRef
}) => {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [selectedGood, setSelectedGood] = useState(null);
    const [tooltipData, setTooltipData] = useState(null);
    const [showSearchBar, setShowSearchBar] = useState(false);

    useEffect(() => {
        const handleKeyDown = (e) => {
            const isCtrlPressed = e.ctrlKey || e.metaKey;

            if (isCtrlPressed && e.keyCode === 70) {
                e.preventDefault();
                setShowSearchBar(true);
            }

            if (e.key === 'Escape' && showSearchBar) {
                e.preventDefault();
                setShowSearchBar(false);
                viewStore.clearSearch();
            }
        };

        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, [showSearchBar]);

    const handleOpenModal = useCallback((good) => {
        setSelectedGood(good);
        setIsModalOpen(true);
    }, []);

    const handleCloseModal = useCallback(() => {
        setIsModalOpen(false);
        setSelectedGood(null);
    }, []);

    const handleCloseSearch = () => {
        setShowSearchBar(false);
        viewStore.clearSearch();
    };

    const goodsWithSearchFilter = useMemo(() => {
        if (!viewStore.searchQuery.trim() || viewStore.searchResults.length === 0) {
            return filteredGoods ? [...filteredGoods] : [];
        }

        return filteredGoods.filter(good =>
            viewStore.searchResults.includes(good.id)
        );
    }, [filteredGoods, viewStore.searchQuery, viewStore.searchResults]);

    const categoryRows = useMemo(() => {
        return getCategoryRows(dates, categorysTotalsStore.categorysTotalsData, goodsStore.goods);
    }, [dates, categorysTotalsStore.categorysTotalsData, goodsStore.goods, viewStore.selectedCategories]);

    const columns = useMemo(() => {
        return createColumns(dates, displayDays, handleOpenModal);
    }, [dates, displayDays]);

    const table = useReactTable({
        data: goodsWithSearchFilter,
        columns,
        getCoreRowModel: getCoreRowModel(),
        manualSorting: true,
    });

    return (
        <>
            {showSearchBar && (
                <SearchBar onClose={handleCloseSearch} />
            )}

            <table className="sticky-table">
                <Colgroup dates={dates} />
                <TableHeader
                    shop={shop}
                    workSpaceSettings={workSpaceSettings}
                    table={table}
                    onTooltip={setTooltipData}
                />
                <TableBody
                    tableContainerRef={tableContainerRef}
                    table={table}
                    columns={columns}
                    onTooltip={setTooltipData}
                    categoryRows={categoryRows}
                    dates={dates}
                />
            </table>

            {tooltipData && <ToolTip tooltipData={tooltipData} />}
            {viewStore.showCategorySelectionModal && (
                <CategorySelectionModal categoriesData={categorysTotalsStore.categorysTotalsData} />
            )}
            <GoodDetailsModal
                isOpen={isModalOpen}
                onClose={handleCloseModal}
                good={selectedGood}
                shop={shop}
                dates={dates}
                workSpaceSettings={workSpaceSettings}
            />
        </>
    )
});
