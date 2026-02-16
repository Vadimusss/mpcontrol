import React, { useMemo, useState } from 'react';
import { observer } from 'mobx-react-lite';
import { viewStore } from '../../Stores/ViewStore';
import { Colgroup } from './Components/Colgroup';
import { TableHeader } from './Components/TableHeader';
import { TableBody } from './Components/TableBody';
import { ToolTip } from './Components/ToolTip';
import { GoodDetailsModal } from './Components/GoodDetailsModal';
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

    const handleOpenModal = (good) => {
        setSelectedGood(good);
        setIsModalOpen(true);
    };

    const handleCloseModal = () => {
        setIsModalOpen(false);
        setSelectedGood(null);
    };

    const columns = useMemo(() =>
        createColumns(viewStore, dates, handleOpenModal),
        [viewStore, dates]
    );

    const table = useReactTable({
        data: filteredGoods,
        columns,
        getCoreRowModel: getCoreRowModel(),
        getSortedRowModel: getSortedRowModel(),
    });

    return (
        <>
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