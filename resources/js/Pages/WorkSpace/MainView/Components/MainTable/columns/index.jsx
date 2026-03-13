import { numericFormatter } from '../../../utils';
import { ArrowsUpDownIcon, BarsArrowUpIcon, BarsArrowDownIcon, EyeIcon } from '@heroicons/react/24/outline'
import { observer } from 'mobx-react-lite';
import goodsStore from '../../../Stores/GoodsStore';
import viewStore from '../../../Stores/ViewStore';
import NoteIconCell from '../Components/NoteIconCell';

const SelectCheckbox = observer(({ goodId }) => (
  <input
    type="checkbox"
    checked={viewStore.selectedItems.includes(goodId)}
    onChange={() => viewStore.toggleItemSelection(goodId)}
  />
));

const periodMap = {
    7: 'sevenDays',
    14: 'fourteenDays',
    30: 'thirtyDays'
};

export const createColumns = (dates, displayDays, handleOpenModal) => {
    const currentPeriod = periodMap[displayDays] || 'thirtyDays';

    const columns = [
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
                    <SelectCheckbox goodId={row.original.id} />
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
                isClassNameDynamic: false,
                thClassName: 'sticky-column sticky-left',
                tdClassName: 'sticky-column sticky-left',
            },
        },
        {
            accessorKey: 'article',
            header: () => (
                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        cursor: 'pointer',
                        gap: '4px',
                        paddingLeft: '2px',
                    }}
                    onClick={() => goodsStore.toggleSort('article')}
                >
                    Арт.
                    {{
                        asc: <BarsArrowUpIcon className="w-4 h-4" />,
                        desc: <BarsArrowDownIcon className="w-4 h-4" />,
                    }[viewStore.isSortedColumn('article')] ?? <ArrowsUpDownIcon className="w-4 h-4" />}
                </div>
            ),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-column sticky-left',
                tdClassName: 'sticky-column sticky-left',
            },
        },
        {
            accessorKey: 'name',
            header: 'Название',
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-column sticky-left',
                tdClassName: 'sticky-column sticky-left',
            }
        },
        {
            accessorKey: 'variant',
            header: 'Вариант',
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-column sticky-left',
                tdClassName: 'sticky-column sticky-left',
            }
        },
        {
            accessorKey: 'status',
            header: () => (
                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        cursor: 'pointer',
                        gap: '4px',
                        paddingLeft: '2px',
                    }}
                    onClick={() => goodsStore.toggleSort('status')}
                >
                    Статус
                    {{
                        asc: <BarsArrowUpIcon className="w-4 h-4" />,
                        desc: <BarsArrowDownIcon className="w-4 h-4" />,
                    }[viewStore.isSortedColumn('status')] ?? <ArrowsUpDownIcon className="w-4 h-4" />}
                </div>
            ),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-column sticky-left',
                tdClassName: 'sticky-column sticky-left',
            }
        },
        {
            accessorKey: 'wbArticle',
            header: 'Арт. WB',
            meta: {
                isClassNameDynamic: false,
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
                cell: (info) => {
                    const value = info.getValue();
                    const row = info.row.original;
                    const goodId = row.id;
                    return (
                        <NoteIconCell 
                            goodId={goodId} 
                            date={date} 
                            value={numericFormatter(value)} 
                        />
                    );
                },
                meta: {
                    isClassNameDynamic: true,
                    tdClassName: (row) => {
                        const isHighlighted = row.isHighlighted?.[date];
                        return isHighlighted
                            ? 'bg-yellow font-large font-bold orders-count-cell'
                            : 'bg-gray font-large font-bold orders-count-cell';
                    },
                }
            }
        }),
        {
            accessorKey: 'totalsOrdersCount',
            header: ({ column }) => (
                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        cursor: 'pointer',
                        gap: '2px'
                    }}
                    onClick={() => goodsStore.toggleSort('totalsOrdersCount')}
                >
                    ∑ мес.
                    {{
                        asc: <BarsArrowUpIcon className="w-4 h-4" />,
                        desc: <BarsArrowDownIcon className="w-4 h-4" />,
                    }[viewStore.isSortedColumn('totalsOrdersCount')] ?? <ArrowsUpDownIcon className="w-4 h-4" />}
                </div>
            ),
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'bg-gray font-large font-bold',
                tdClassName: 'bg-gray font-large font-bold',
            }
        },
        {
            accessorKey: 'mainRowProfit',
            header: 'Приб.',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'bg-gray',
                tdClassName: 'bg-gray',
            }
        },
        {
            accessorKey: 'drr30days',
            header: 'ДРР 30д',
            cell: (info) => numericFormatter(info.getValue(), 0, '%'),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'bg-gray',
            }
        },
        {
            accessorKey: 'drr7days',
            header: 'ДРР 7д',
            cell: (info) => numericFormatter(info.getValue(), 0, '%'),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'bg-gray',
            }
        },
        {
            accessorKey: 'avgDailyAdCost',
            header: 'Ср. РР/д',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'bg-gray',
            }
        },
        {
            id: 'adOrders',
            header: 'Рек зак',
            accessorFn: (row) => row.orderTotals[currentPeriod]?.adOrders || 0,
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'bg-gray',
            }
        },
        {
            id: 'noAdOrders',
            header: 'Орг зак',
            accessorFn: (row) => row.orderTotals[currentPeriod]?.noAdOrders || 0,
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'bg-gray',
            }
        },
        {
            id: 'assocOrdersFromOther',
            header: 'Зак с др РК',
            accessorFn: (row) => row.orderTotals[currentPeriod]?.assoc_orders_from_other || 0,
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'bg-gray',
            }
        },
        {
            id: 'assocOrdersFromThis',
            header: 'Зак др SKU',
            accessorFn: (row) => row.orderTotals[currentPeriod]?.assoc_orders_from_this || 0,
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'bg-gray',
            }
        },
        {
            accessorKey: 'percent',
            header: '%',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
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
            header: () => (
                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        cursor: 'pointer',
                        gap: '4px',
                        paddingLeft: '2px',
                    }}
                    onClick={() => goodsStore.toggleSort('days_of_stock')}
                >
                    дней
                    {{
                        asc: <BarsArrowUpIcon className="w-4 h-4" />,
                        desc: <BarsArrowDownIcon className="w-4 h-4" />,
                    }[viewStore.isSortedColumn('days_of_stock')] ?? <ArrowsUpDownIcon className="w-4 h-4" />}
                </div>
            ),
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
                isClassNameDynamic: false,
                thClassName: 'bg-gray',
                tdClassName: 'bg-gray',
            }
        },
        {
            accessorKey: 'stocks.totals',
            header: () => (
                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        cursor: 'pointer',
                        gap: '4px',
                        paddingLeft: '2px',
                    }}
                    onClick={() => goodsStore.toggleSort('stocks.totals')}
                >
                    ВБ
                    {{
                        asc: <BarsArrowUpIcon className="w-4 h-4" />,
                        desc: <BarsArrowDownIcon className="w-4 h-4" />,
                    }[viewStore.isSortedColumn('stocks.totals')] ?? <ArrowsUpDownIcon className="w-4 h-4" />}
                </div>
            ),
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
                isClassNameDynamic: false,
                thClassName: 'font-large font-bold',
                tdClassName: 'font-large font-bold',
            }
        },
        {
            accessorKey: 'stocks.fboTotals',
            header: 'FBO',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'font-large font-bold',
                tdClassName: 'font-large font-bold',
            }
        },
        {
            accessorKey: 'stocks.elektrostal',
            header: 'Сталь',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.tula',
            header: 'Тула',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.koledino',
            header: 'Коледино',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.ryazan',
            header: 'Рязань',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.nevinnomyssk',
            header: 'Нмысск',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.krasnodar',
            header: 'Краснодар',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.kazan',
            header: 'Казань',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.kotovsk',
            header: 'Котовск',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.belyeStolby',
            header: 'Белые Столбы',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.podolsk4',
            header: 'Подольск 4',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.spbUtkinaZavod',
            header: 'СПб Уткина',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.podolsk',
            header: 'Подольск',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.ekbIspytatelej14g',
            header: 'Екат 14',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.novosibirsk',
            header: 'Новосибирск',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.voronezh',
            header: 'Воронеж',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.vladimir',
            header: 'Владимир',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.belayaDacha',
            header: 'Белая дача',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.samara',
            header: 'Самара',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.volgograd',
            header: 'Волгоград',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.ekbPerspektivnyj12',
            header: 'Екат 12',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        },
        {
            accessorKey: 'stocks.sarapul',
            header: 'Сарапул',
            cell: (info) => numericFormatter(info.getValue()),
            meta: {
                isClassNameDynamic: false,
                thClassName: 'sticky-header-city',
            }
        }
    ];

    return columns;
};
