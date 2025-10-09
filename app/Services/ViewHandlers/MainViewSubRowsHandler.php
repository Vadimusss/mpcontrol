<?php

namespace App\Services\ViewHandlers;

use App\Models\Good;
use App\Models\Shop;
use App\Models\WbAdvV2FullstatsProduct;
use Throwable;

class MainViewSubRowsHandler
{
    public function prepareSubRowsData(Good $good, Shop $shop, array $dates): array
    {
        $commission = $shop->settings['commission'] ?? null;
        $logistics = $shop->settings['logistics'] ?? null;
        
        $advDataByType = $this->getAdvDataWithCtrCpc($shop, $dates);
        $aacData = $advDataByType[8] ?? [];
        $aucData = $advDataByType[9] ?? [];

        $good->load([
            'nsi:good_id,name,variant,fg_1,cost_with_taxes',
            'sizes:good_id,price',
            'wbListGoodRow:good_id,discount',
            'salesFunnel' => function ($q) use ($dates) {
                $q->whereIn('date', $dates)
                  ->orderBy('date');
            },
            'wbNmReportDetailHistory' => function ($q) use ($dates) {
                $q->select('good_id', 'dt', 'add_to_cart_conversion', 'cart_to_order_conversion')
                  ->whereIn('dt', $dates);
            }
        ]);
        
        $conversionMap = [];
        foreach ($good->wbNmReportDetailHistory as $conversionData) {
            $conversionMap[$conversionData->dt] = [
                'add_to_cart_conversion' => $conversionData->add_to_cart_conversion ?? 0,
                'cart_to_order_conversion' => $conversionData->cart_to_order_conversion ?? 0
            ];
        }
        
        $salesData = [];
        
        foreach ($good->salesFunnel as $row) {
            $conversion = $conversionMap[$row->date] ?? [
                'add_to_cart_conversion' => 0,
                'cart_to_order_conversion' => 0
            ];
            
            $ordersProfit = $this->calculateProfit($row->orders_sum_rub, $row->orders_count, $row->advertising_costs, $good->nsi, $commission, $logistics);
            $buyoutsProfit = $this->calculateProfit($row->buyouts_sum_rub, $row->buyouts_count, $row->advertising_costs, $good->nsi, $commission, $logistics);
            
            $salesData[$row->date] = [
                'orders_count' => $row->orders_count === 0 ? '' : $row->orders_count,
                'advertising_costs' => $row->advertising_costs === 0 ? '' : round($row->advertising_costs / 1000),
                'orders_profit' => $ordersProfit,
                'price_with_disc' => $row->price_with_disc === 0 ? '' : round($row->price_with_disc),
                'spp' => $row->price_with_disc != 0 ? round($row->price_with_disc - $row->finished_price) : '',
                'finished_price' => $row->finished_price == 0 ? '' : round($row->finished_price),
                'orders_sum_rub' => $row->orders_sum_rub === 0 ? '' : round($row->orders_sum_rub / 1000),
                'buyouts_sum_rub' => $row->buyouts_sum_rub === 0 ? '' : round($row->buyouts_sum_rub / 1000),
                'isHighlighted' => $row->advertising_costs != 0 && $row->advertising_costs > 100,
                'buyouts_count' => $row->buyouts_count === 0 ? '' : $row->buyouts_count,
                'buyouts_profit' => $buyoutsProfit,
                'open_card_count' => $row->open_card_count === 0 ? '' : $row->open_card_count,
                'no_ad_clicks' => ($row->aac_clicks != 0 || $row->auc_clicks != 0) ? $row->open_card_count - ($row->aac_clicks + $row->auc_clicks) : '',
                'add_to_cart_count' => $row->add_to_cart_count === 0 ? '' : $row->add_to_cart_count,
                'add_to_cart_conversion' => $conversion['add_to_cart_conversion'] === 0 ? '' : $conversion['add_to_cart_conversion'],
                'cart_to_order_conversion' => $conversion['cart_to_order_conversion'] === 0 ? '' : $conversion['cart_to_order_conversion'],

                'aac_cpm' => $row->aac_cpm === 0 ? '' : $row->aac_cpm,
                'aac_views' => $row->aac_views === 0 ? '' : $row->aac_views,
                'aac_clicks' => $row->aac_clicks === 0 ? '' : $row->aac_clicks,
                'aac_sum' => $row->aac_sum === 0 ? '' : round($row->aac_sum),
                'aac_orders' => $row->aac_orders === 0 ? '' : $row->aac_orders,
                'aac_ctr' => $aacData[$row->date][$row->good_id]['ctr'] ?? '',
                'aac_cpc' => $aacData[$row->date][$row->good_id]['cpc'] ?? '',

                'auc_cpm' => $row->auc_cpm === 0 ? '' : $row->auc_cpm,
                'auc_views' => $row->auc_views === 0 ? '' : $row->auc_views,
                'auc_clicks' => $row->auc_clicks === 0 ? '' : $row->auc_clicks,
                'auc_sum' => $row->auc_sum === 0 ? '' : round($row->auc_sum),
                'auc_orders' => $row->auc_orders === 0 ? '' : $row->auc_orders,
                'auc_ctr' => $aucData[$row->date][$row->good_id]['ctr'] ?? '',
                'auc_cpc' => $aucData[$row->date][$row->good_id]['cpc'] ?? '',

                'ad_orders' => ($row->auc_orders != 0 || $row->aac_orders != 0) ? $row->auc_orders + $row->aac_orders : '',
                'no_ad_orders' => (($row->auc_orders != 0 || $row->aac_orders != 0) && $row->orders_count != 0) ?
                    $row->orders_count - ($row->auc_orders + $row->aac_orders) : '',
            ];
        }
        
        return [
            'goodId' => $good->id,
            'salesData' => $salesData,
            'subRowsMetadata' => [
                ['name' => 'Рекл', 'type' => 'advertising_costs'],
                ['name' => 'Приб', 'type' => 'orders_profit'],
                ['name' => 'Цена', 'type' => 'price_with_disc'],
                ['name' => 'СПП', 'type' => 'spp'],
                ['name' => 'Цена СПП', 'type' => 'finished_price'],
                ['name' => 'Заказы руб', 'type' => 'orders_sum_rub'],
                ['name' => 'Продажи руб', 'type' => 'buyouts_sum_rub'],
                ['name' => 'Продажи шт', 'type' => 'buyouts_count'],
                ['name' => 'Приб по прод', 'type' => 'buyouts_profit'],
                ['name' => 'Клики всего', 'type' => 'open_card_count'],
                ['name' => 'Клики не рекл', 'type' => 'no_ad_clicks'],
                ['name' => 'Корзины', 'type' => 'add_to_cart_count'],
                ['name' => 'Конв корз', 'type' => 'add_to_cart_conversion'],
                ['name' => 'Конв заказ', 'type' => 'cart_to_order_conversion'],
                ['name' => 'АРК CPM', 'type' => 'aac_cpm'],
                ['name' => 'АРК Показы', 'type' => 'aac_views'],
                ['name' => 'АРК Клики', 'type' => 'aac_clicks'],
                ['name' => 'АРК Затраты', 'type' => 'aac_sum'],
                ['name' => 'АРК Зак по рекл', 'type' => 'aac_orders'],
                ['name' => 'АРК CTR', 'type' => 'aac_ctr'],
                ['name' => 'АРК CPC', 'type' => 'aac_cpc'],
                ['name' => 'Аукцион CPM', 'type' => 'auc_cpm'],
                ['name' => 'Аукцион Показы', 'type' => 'auc_views'],
                ['name' => 'Аукцион Клики', 'type' => 'auc_clicks'],
                ['name' => 'Аукцион Затраты', 'type' => 'auc_sum'],
                ['name' => 'Аукцион Зак по рекл', 'type' => 'auc_orders'],
                ['name' => 'Аукцион CTR', 'type' => 'auc_ctr'],
                ['name' => 'Аукцион CPC', 'type' => 'auc_cpc'],
                ['name' => 'Заказы по рекл', 'type' => 'ad_orders'],
                ['name' => 'Заказы не по рекл', 'type' => 'no_ad_orders'],
            ]
        ];
    }

    private function getAdvDataWithCtrCpc(Shop $shop, array $dates): array
    {
        $result = WbAdvV2FullstatsProduct::whereIn('wb_adv_fs_products.date', $dates)
            ->join('wb_adv_fs_apps', 'wb_adv_fs_products.wb_adv_fs_app_id', '=', 'wb_adv_fs_apps.id')
            ->join('wb_adv_fs_days', 'wb_adv_fs_apps.wb_adv_fs_day_id', '=', 'wb_adv_fs_days.id')
            ->join('wb_adv_v2_fullstats_wb_adverts', 'wb_adv_fs_days.wb_adv_v2_fullstats_wb_advert_id', '=', 'wb_adv_v2_fullstats_wb_adverts.id')
            ->join('wb_adv_v1_promotion_counts', 'wb_adv_v2_fullstats_wb_adverts.advert_id', '=', 'wb_adv_v1_promotion_counts.advert_id')
            ->where('wb_adv_v1_promotion_counts.shop_id', $shop->id)
            ->whereIn('wb_adv_v1_promotion_counts.type', [8, 9])
            ->select(
                'wb_adv_fs_products.good_id',
                'wb_adv_fs_products.date',
                'wb_adv_v1_promotion_counts.type',
                'wb_adv_v2_fullstats_wb_adverts.ctr',
                'wb_adv_v2_fullstats_wb_adverts.cpc'
            )
            ->get()
            ->groupBy(['type', 'date', 'good_id'])
            ->map(function ($typeGroup) {
                return $typeGroup->map(function ($dateGroup) {
                    return $dateGroup->map(function ($items) {
                        return [
                            'ctr' => $items->first()->ctr,
                            'cpc' => $items->first()->cpc,
                        ];
                    });
                });
            });
        
        return $result->toArray();
    }

    private function calculateProfit($sum, $count, $advertising_costs, $nsi, $commission, $logistics): string
    {
        try {
            $costWithTaxes = $nsi->cost_with_taxes ?? null;

            if (
                $sum === null || $commission === null ||
                $logistics === null || $advertising_costs === null ||
                $costWithTaxes === null
            ) {
                return '?';
            }

            $commissionPercent = $commission / 100;

            $profit = $sum
                - ($sum * $commissionPercent)
                - ($count * $logistics)
                - $advertising_costs
                - ($count * $costWithTaxes);

            return round($profit, 1) == 0 ? '' : round($profit);
        } catch (Throwable $e) {
            return '-';
        }
    }
}
