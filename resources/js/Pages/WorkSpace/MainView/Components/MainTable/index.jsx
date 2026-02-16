import React, { useMemo, useState, useEffect } from 'react';
import { observer } from 'mobx-react-lite';
import { viewStore } from '../../Stores/ViewStore';
import { Colgroup } from './Components/Colgroup';
import { TableHeader } from './Components/TableHeader';
import { TableBody } from './Components/TableBody';
import { ToolTip } from './Components/ToolTip';
import { GoodDetailsModal } from './Components/GoodDetailsModal';
import { SearchBar } from './Components/SearchBar';
import { useReactTable, getCoreRowModel, getSortedRowModel } from '@tanstack/react-table';
import { createColumns } from './columns'
import './styles.css';

export const MainTable = observer(({
    dates,
    shop,
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

    const handleOpenModal = (good) => {
        setSelectedGood(good);
        setIsModalOpen(true);
    };

    const handleCloseModal = () => {
        setIsModalOpen(false);
        setSelectedGood(null);
    };

    const handleCloseSearch = () => {
        setShowSearchBar(false);
        viewStore.clearSearch();
    };

    const goodsWithSearchFilter = useMemo(() => {
        if (!viewStore.searchQuery.trim() || viewStore.searchResults.length === 0) {
            return filteredGoods;
        }
        
        return filteredGoods.filter(good => 
            viewStore.searchResults.includes(good.id)
        );
    }, [filteredGoods, viewStore.searchQuery, viewStore.searchResults]);

    const columns = useMemo(() =>
        createColumns(viewStore, dates, handleOpenModal),
        [viewStore, dates]
    );

    const table = useReactTable({
        data: goodsWithSearchFilter,
        columns,
        getCoreRowModel: getCoreRowModel(),
        getSortedRowModel: getSortedRowModel(),
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
                    dates={dates}
                    table={table}
                    onTooltip={setTooltipData}
                />
                <TableBody
                    tableContainerRef={tableContainerRef}
                    table={table}
                    columns={columns}
                    onTooltip={setTooltipData}
                />
            </table>
            {tooltipData && <ToolTip tooltipData={tooltipData} />}
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