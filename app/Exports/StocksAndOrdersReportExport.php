<?php

namespace App\Exports;

use App\Models\Shop;
use App\Models\GoodList;
use App\Models\Report;
use App\Models\SalesFunnel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class StocksAndOrdersReportExport implements FromCollection, WithHeadings, WithColumnFormatting, WithStrictNullComparison
{
    public function __construct(
        public Shop $shop,
        public string $begin,
        public string $end,
    )
    {
        $this->shop = $shop;
        $this->begin = $begin;
        $this->end = $end;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $selectedData = $this->shop->stocksAndOrders()->select(
            'barcode',
            'supplier_article',
            'stocks_and_orders.nm_id',
            'warehouse_name',
            'date',
            'quantity',
            'orders_count')->whereBetween('date', [$this->begin, $this->end])->get() 
            ->map(function ($row) {
                $row['barcode'] = "'" . $row['barcode']; // Изменяем значение ключа 'status'
                return $row;
            });

        return $selectedData;
    }

    public function headings(): array
    {
        return [
            'barcode',
            'supplier_article',
            'nm_id',
            'warehouse_name',
            'date',
            'quantity',
            'orders_count',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT, // Форматируем столбец A как текст
        ];
    }
}
