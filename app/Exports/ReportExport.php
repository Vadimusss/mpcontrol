<?php

namespace App\Exports;

use App\Models\Report;
use App\Models\SalesFunnel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReportExport implements FromCollection, WithHeadings
{
    public function __construct(
        // public Report $report,
        public string $begin,
        public string $end,
    )
    {
        // $this->report = $report;
        $this->begin = $begin;
        $this->end = $end;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return SalesFunnel::select(
            'vendor_code',
            'nm_id',
            'imt_name',
            'date',
            'open_card_count',
            'add_to_cart_count',
            'orders_count',
            'orders_sum_rub',
            'advertising_costs',
            'price_with_disc',
            'finished_price')->
            whereBetween('date', [$this->begin, $this->end])->get();
    }

    public function headings(): array
    {
        return [
            'vendor_code',
            'nm_id',
            'imt_name',
            'date',
            'open_card_count',
            'add_to_cart_count',
            'orders_count',
            'orders_sum_rub',
            'advertising_costs',
            'price_with_disc',
            'finished_price',
        ];
    }
}
