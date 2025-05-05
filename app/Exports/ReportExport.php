<?php

namespace App\Exports;

use App\Models\Shop;
use App\Models\GoodList;
use App\Models\Report;
use App\Models\SalesFunnel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class ReportExport implements FromCollection, WithHeadings, WithStrictNullComparison
{
    public function __construct(
        public Shop $shop,
        public GoodList $goodList,
        public string $begin,
        public string $end,
    )
    {
        $this->shop = $shop;
        $this->goodList = $goodList;
        $this->begin = $begin;
        $this->end = $end;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $goodListNmIds = $this->goodList->goods()->pluck('nm_id');

        $selectedData = $this->shop->salesFunnel()->select(
            'sales_funnels.vendor_code',
            'sales_funnels.nm_id',
            'imt_name',
            'date',
            'open_card_count',
            'add_to_cart_count',
            'orders_count',
            'orders_sum_rub',
            'advertising_costs',
            'price_with_disc',
            'finished_price',
            'aac_cpm',
            'aac_views',
            'aac_clicks',
            'aac_orders',
            'aac_sum',
            'auc_cpm',
            'auc_views',
            'auc_clicks',
            'auc_orders',
            'auc_sum')->whereBetween('date', [$this->begin, $this->end])->whereIn('sales_funnels.nm_id', $goodListNmIds)->get();

        return $selectedData;
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
            'aac_cpm',
            'aac_views',
            'aac_clicks',
            'aac_orders',
            'aac_sum',
            'auc_cpm',
            'auc_views',
            'auc_clicks',
            'auc_orders',
            'auc_sum',
        ];
    }
}
