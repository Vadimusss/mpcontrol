import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import React, { useMemo, useRef, useEffect, useState } from 'react';
import { observer } from 'mobx-react-lite';
import goodsStore from './Stores/GoodsStore';
import { viewStore } from './Stores/ViewStore';
import { TableHeader } from './Components/TableHeader';
import { TableBody } from './Components/TableBody';
import { Colgroup } from './Components/Colgroup';
import { GoodDetailsModal } from './Modals/GoodDetailsModal';
import { useReactTable, getCoreRowModel, getSortedRowModel } from '@tanstack/react-table';
import { ArrowsUpDownIcon, BarsArrowUpIcon, BarsArrowDownIcon } from '@heroicons/react/24/solid';
import { EyeIcon } from '@heroicons/react/24/outline';
import './styles.css';

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

export default observer(function VirtualizedMainView({ shop, workSpace, goods: initialGoods, initialViewState }) {
    const workSpaceSettings = JSON.parse(workSpace.view_settings.settings);
    const viewId = workSpace.view_settings.view.id;
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [selectedGood, setSelectedGood] = useState(null);
    const [tooltipData, setTooltipData] = useState(null);

    useEffect(() => {
        goodsStore.setGoods(initialGoods);
    }, [initialGoods]);

    useEffect(() => {
        viewStore.workSpaceId = workSpace.id;
        viewStore.viewId = viewId;
        viewStore.setInitialState(initialViewState);
    }, [initialViewState]);

    const dates = generateDateHeaders(workSpaceSettings.days);
    const tableContainerRef = useRef(null);

    const handleOpenModal = (good) => {
        setSelectedGood(good);
        setIsModalOpen(true);
    };

    const handleCloseModal = () => {
        setIsModalOpen(false);
        setSelectedGood(null);
    };

    const filteredGoods = useMemo(() => {
        return viewStore.showOnlySelected
            ? goodsStore.goods.filter(item => viewStore.selectedItems.includes(item.id))
            : goodsStore.goods;
    }, [viewStore.showOnlySelected, viewStore.selectedItems, goodsStore.goods]);

    const columns = useMemo(() => [
        {
            id: 'select',
            header: () => (
                <input
                    type="checkbox"
                    checked={viewStore.showOnlySelected}
                    onChange={() => viewStore.toggleShowOnlySelected()}
                    title="Показать только выбранные"
                />
            ),
            cell: ({ row }) => (
                <div className="flex items-center gap-2">
                    <input
                        type="checkbox"
                        checked={viewStore.selectedItems.includes(row.original.id)}
                        onChange={() => viewStore.toggleItemSelection(row.original.id)}
                    />
                    <button
                        onClick={() => handleOpenModal(row.original)}
                        className="text-gray-400 hover:text-blue-600 transition-colors"
                        title="Показать детальную информацию"
                    >
                        <EyeIcon className="w-4 h-4" />
                    </button>
                </div>
            ),
            sticky: 'left',
        },
        {
            accessorKey: 'article',
            header: ({ column }) => (
                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        cursor: 'pointer',
                        gap: '4px'
                    }}
                    onClick={column.getToggleSortingHandler()}
                >
                    Арт.
                    {{
                        asc: <BarsArrowUpIcon className="w-4 h-4" />,
                        desc: <BarsArrowDownIcon className="w-4 h-4" />,
                    }[column.getIsSorted()] ?? <ArrowsUpDownIcon className="w-4 h-4" />}
                </div>
            ),
            sticky: 'left',
            enableSorting: true,
        },
        {
            accessorKey: 'name',
            header: 'Название',
            sticky: 'left',
        },
        {
            accessorKey: 'variant',
            header: 'Вариант',
            sticky: 'left',
        },
        {
            accessorKey: 'wbArticle',
            header: 'Арт. WB',
            sticky: 'left',
            cell: (info) => {
                const article = info.getValue();
                return (
                    <a
                        href={`https://www.wildberries.ru/catalog/${article}/detail.aspx`}
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        {article}
                    </a>
                );
            },
        },
        /*         {
                    accessorKey: 'mainRowMetadata',
                    header: '',
                    sticky: 'left',
                }, */
        ...dates.map((date) => {
            const formattedDate = new Date(date).toLocaleDateString('ru-RU', {
                day: '2-digit',
                month: '2-digit'
            });

            return {
                id: `date-${date}`,
                header: formattedDate,
                accessorFn: (row) => row.orders_count?.[date] ?? '',
            }
        }),
        {
            accessorKey: 'totalsOrdersCount',
            header: '∑ мес.',
        },
        {
            accessorKey: 'prices.discountedPrice',
            header: 'Цена',
        },
        {
            accessorKey: 'prices.costWithTaxes',
            header: 'C/C',
        },
        {
            accessorKey: 'mainRowProfit',
            header: 'Приб.',
        },
        {
            accessorKey: 'percent',
            header: '%',
        },
        {
            accessorKey: 'stocks.totals',
            header: 'шт.',
        },
        {
            accessorKey: 'days_of_stock',
            header: 'дней',
        },
        {
            accessorKey: 'stocks.elektrostal',
            header: 'Сталь',
        },
        {
            accessorKey: 'stocks.tula',
            header: 'Тула',
        },
        {
            accessorKey: 'stocks.nevinnomyssk',
            header: 'Нмысск',
        },
        {
            accessorKey: 'stocks.krasnodar',
            header: 'Красн',
        },
        {
            accessorKey: 'stocks.kazan',
            header: 'Казань',
        }
    ], [filteredGoods, viewStore]);

    const table = useReactTable({
        data: filteredGoods,
        columns,
        getCoreRowModel: getCoreRowModel(),
        getSortedRowModel: getSortedRowModel(),
    });

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
                <table className="sticky-table">
                    <Colgroup dates={dates} />
                    <TableHeader
                        shop={shop}
                        workSpaceSettings={workSpaceSettings}
                        dates={dates}
                        table={table}
                    />
                    <TableBody
                        tableContainerRef={tableContainerRef}
                        table={table}
                        columns={columns}
                        onTooltip={setTooltipData}
                    />
                </table>
                {tooltipData && (
                    <div className="global-tooltip" style={{
                        position: 'fixed',
                        left: `${tooltipData.x}px`,
                        top: `${tooltipData.y}px`,
                        zIndex: 99999,
                        background: '#ffffff',
                        color: '#000000',
                        padding: '6px 10px',
                        borderRadius: '4px',
                        width: 'fit-content',
                        whiteSpace: 'normal',
                        fontSize: '14px',
                        lineHeight: '1.4',
                        pointerEvents: 'none',
                        boxShadow: '0 4px 12px rgba(0,0,0,0.25)',
                        border: '1px solid #d9d9d9',
                    }}>
                        {tooltipData.text}
                    </div>
                )}
            </div>

            <GoodDetailsModal
                isOpen={isModalOpen}
                onClose={handleCloseModal}
                good={selectedGood}
                shop={shop}
                dates={dates}
                workSpaceSettings={workSpaceSettings}
            />
        </AuthenticatedLayout>
    );
});
