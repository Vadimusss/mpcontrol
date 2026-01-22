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
import { numericFormatter } from './Utils';
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
            meta: {
                thClassName: 'sticky-column sticky-left',
                tdClassName: 'sticky-column sticky-left',
            },
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
            meta: {
                thClassName: 'sticky-column sticky-left',
                tdClassName: 'sticky-column sticky-left',
            },
            enableSorting: true,
        },
        {
            accessorKey: 'name',
            header: 'Название',
            meta: {
                thClassName: 'sticky-column sticky-left',
                tdClassName: 'sticky-column sticky-left',
            }
        },
        {
            accessorKey: 'variant',
            header: 'Вариант',
            meta: {
                thClassName: 'sticky-column sticky-left',
                tdClassName: 'sticky-column sticky-left',
            }
        },
        {
            accessorKey: 'wbArticle',
            header: 'Арт. WB',
            meta: {
                thClassName: 'sticky-column sticky-left',
                tdClassName: 'sticky-column sticky-left',
            },
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
        ...dates.map((date) => {
            const formattedDate = new Date(date).toLocaleDateString('ru-RU', {
                day: '2-digit',
                month: '2-digit'
            });

            return {
                id: `date-${date}`,
                header: formattedDate,
                accessorFn: (row) => row.orders_count?.[date] ?? '',
                cell: (info) => numericFormatter(info.getValue()),
                meta: {
                    tdClassName: 'bg-gray font-large font-bold',
                }
            }
        }),
        {
            accessorKey: 'totalsOrdersCount',
            header: '∑ мес.',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'bg-gray font-large font-bold',
                tdClassName: 'bg-gray font-large font-bold',
            }
        },
        {
            accessorKey: 'mainRowProfit',
            header: 'Приб.',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'bg-gray',
                tdClassName: 'bg-gray',
            }
        },
        {
            accessorKey: 'percent',
            header: '%',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'bg-gray',
                tdClassName: 'bg-gray',
            }
        },
        {
            accessorKey: 'prices.price',
            header: 'Цена',
            cell: (info) => numericFormatter(info.getValue())
        },
        {
            accessorKey: 'prices.discount',
            header: 'ск',
            cell: (info) => numericFormatter(info.getValue())
        },
        {
            accessorKey: 'prices.discountedPrice',
            header: 'Цена ск',
            cell: (info) => numericFormatter(info.getValue())
        },
        {
            accessorKey: 'prices.costWithTaxes',
            header: 'C/C',
            cell: (info) => numericFormatter(info.getValue())
        },
        {
            // accessorKey: 'prices.costWithTaxes',
            header: 'Приб',
            cell: (info) => numericFormatter(info.getValue())
        },
        {
            // accessorKey: 'prices.costWithTaxes',
            header: '%',
            cell: (info) => numericFormatter(info.getValue())
        },
        {
            accessorKey: 'days_of_stock',
            header: 'дней',
            cell: (info) => {
                const value = info.getValue();
                const bgClass = value <= 10 ? 'bg-red'
                    : value <= 20 ? 'bg-yellow'
                        : value <= 99 ? 'bg-green'
                            : 'bg-purple';

                return (
                    <div className={`full-bg ${bgClass}`}>
                        {numericFormatter(value)}
                    </div>
                );
            },
            meta: {
                thClassName: 'bg-gray',
                tdClassName: 'bg-gray',
            }
        },
        {
            accessorKey: 'stocks.totals',
            header: 'ВБ',
            cell: (info) => numericFormatter(info.getValue())
        },
        {
            // accessorKey: 'stocks.totals',
            header: 'ТР',
            cell: (info) => numericFormatter(info.getValue())
        },
        {
            // accessorKey: 'stocks.totals',
            header: 'ЦС',
            cell: (info) => numericFormatter(info.getValue())
        },
        {
            // accessorKey: 'stocks.totals',
            header: 'Зак',
            cell: (info) => numericFormatter(info.getValue())
        },
        {
            accessorKey: 'stocks.fbsTotals',
            header: 'FBS',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'font-large font-bold',
                tdClassName: 'font-large font-bold',
            }
        },
        {
            accessorKey: 'stocks.fboTotals',
            header: 'FBO',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'font-large font-bold',
                tdClassName: 'font-large font-bold',
            }
        },
        {
            accessorKey: 'stocks.elektrostal',
            header: 'Сталь',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.tula',
            header: 'Тула',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.koledino',
            header: 'Коледино',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.ryazan',
            header: 'Рязань',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.nevinnomyssk',
            header: 'Нмысск',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.krasnodar',
            header: 'Краснодар',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.kazan',
            header: 'Казань',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.kotovsk',
            header: 'Котовск',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.belyeStolby',
            header: 'Белые Столбы',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.podolsk4',
            header: 'Подольск 4',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.spbUtkinaZavod',
            header: 'СПб Уткина',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.podolsk',
            header: 'Подольск',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.ekbIspytatelej14g',
            header: 'Екат 14',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.novosibirsk',
            header: 'Новосибирск',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.voronezh',
            header: 'Воронеж',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.vladimir',
            header: 'Владимир',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.belayaDacha',
            header: 'Белая дача',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.samara',
            header: 'Самара',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.volgograd',
            header: 'Волгоград',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.ekbPerspektivnyj12',
            header: 'Екат 12',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.sarapul',
            header: 'Сарапул',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                thClassName: 'sticky-header-city',
            }
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
                        onTooltip={setTooltipData}
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
