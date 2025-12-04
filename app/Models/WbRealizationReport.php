<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WbRealizationReport extends Model
{
    protected $connection = 'ozon_api';
    protected $table = 'wb_realization_report';

    public static function getExpenseData($orderDate, $shopId, $nmIds = [])
    {
        $startDate = date('Y-m-d', strtotime($orderDate . ' -1 day')) . ' 21:00:00';
        $endDate = $orderDate . ' 21:00:00';

        $query = self::selectRaw("
            nm_id,
            SUM(CASE WHEN doc_type_name = 'Продажа' AND supplier_oper_name = 'Продажа' THEN retail_price ELSE 0 END) 
                - SUM(CASE WHEN doc_type_name = 'Возврат' THEN retail_price ELSE 0 END) AS retail_price,
            SUM(CASE WHEN doc_type_name = 'Продажа' AND supplier_oper_name = 'Продажа' THEN retail_amount ELSE 0 END) 
                - SUM(CASE WHEN doc_type_name = 'Возврат' THEN retail_amount ELSE 0 END) AS retail_amount,
            SUM(CASE WHEN doc_type_name = 'Продажа' AND supplier_oper_name = 'Продажа' THEN ppvz_for_pay ELSE 0 END) 
                - SUM(CASE WHEN doc_type_name = 'Возврат' THEN ppvz_for_pay ELSE 0 END) AS ppvz_for_pay,
            SUM(COALESCE(delivery_rub, 0)) AS logistics_total,
            SUM(COALESCE(storage_fee, 0)) AS storage_total,
            SUM(COALESCE(acquiring_fee, 0)) AS acquiring_total,
            SUM(COALESCE(penalty, 0)) AS penalty_total,
            SUM(CASE WHEN supplier_oper_name = 'Удержание' AND bonus_type_name <> 'Оказание услуг «WB Продвижение»'
                THEN COALESCE(deduction, 0) ELSE 0 END) AS other_total
        ")
            ->where('cabinet', $shopId)
            ->where('order_dt', '>=', $startDate)
            ->where('order_dt', '<', $endDate)
            ->whereIn('nm_id', $nmIds)
            ->groupBy('nm_id');

        $result = $query->get()
            ->mapWithKeys(function ($item) {
                $wb_commission = ((float)$item->retail_price - (float)$item->ppvz_for_pay) - ((float)$item->retail_price - (float)$item->retail_amount);
                $commission_total = (float)$item->logistics_total + (float)$item->storage_total + (float)$item->penalty_total + (float)$item->other_total;
                return [
                    $item->nm_id => [
                        'ppvz_for_pay' => (float)$item->ppvz_for_pay,
                        'wb_commission' => $wb_commission,
                        'logistics_total' => (float)$item->logistics_total,
                        'storage_total' => (float)$item->storage_total,
                        'acquiring_total' => (float)$item->acquiring_total,
                        'penalty_total' => (float)$item->penalty_total,
                        'other_total' => (float)$item->other_total,
                        'commission_total' => $commission_total
                    ]
                ];
            })
            ->toArray();

        $defaultData = [
            'ppvz_for_pay' => 0,
            'wb_commission' => 0,
            'logistics_total' => 0,
            'storage_total' => 0,
            'acquiring_total' => 0,
            'penalty_total' => 0,
            'other_total' => 0,
            'commission_total' => 0
        ];

        foreach ($nmIds as $nmId) {
            if (!array_key_exists($nmId, $result)) {
                $result[$nmId] = $defaultData;
            }
        }

        return $result;
    }
}
