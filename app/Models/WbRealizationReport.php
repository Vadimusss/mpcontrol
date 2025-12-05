<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WbRealizationReport extends Model
{
    protected $connection = 'ozon_api';
    protected $table = 'wb_realization_report';
    
    public static function getExpenseData($dateFrom, $shopId, $nmIds = [])
    {
        $query = self::selectRaw("
            date_from, 
            nm_id,
             (
                (
                    (
                        SUM(CASE WHEN doc_type_name = 'Продажа' AND supplier_oper_name = 'Продажа' THEN retail_price ELSE 0 END) 
                        - SUM(CASE WHEN doc_type_name = 'Возврат' THEN retail_price ELSE 0 END)
                    ) - (
                        SUM(CASE WHEN doc_type_name = 'Продажа' AND supplier_oper_name = 'Продажа' THEN ppvz_for_pay ELSE 0 END) 
                        - SUM(CASE WHEN doc_type_name = 'Возврат' THEN ppvz_for_pay ELSE 0 END)
                    )
                ) - (
                    (
                        SUM(CASE WHEN doc_type_name = 'Продажа' AND supplier_oper_name = 'Продажа' THEN retail_price ELSE 0 END) 
                        - SUM(CASE WHEN doc_type_name = 'Возврат' THEN retail_price ELSE 0 END)
                    ) - (
                         SUM(CASE WHEN doc_type_name = 'Продажа' AND supplier_oper_name = 'Продажа' THEN retail_amount ELSE 0 END) 
                        - SUM(CASE WHEN doc_type_name = 'Возврат' THEN retail_amount ELSE 0 END)
                    )
                )
            ) + SUM(COALESCE(delivery_rub, 0)) + SUM(COALESCE(storage_fee, 0)) + SUM(COALESCE(penalty, 0)) + SUM(CASE WHEN supplier_oper_name = 'Удержание' AND bonus_type_name NOT LIKE 'Списание за отзыв%%' AND bonus_type_name <> 'Оказание услуг «WB Продвижение»' THEN COALESCE(deduction, 0) ELSE 0 END) AS commission_total,
            SUM(COALESCE(delivery_rub, 0)) AS logistics_total,
            SUM(COALESCE(storage_fee, 0)) AS storage_total,
            SUM(COALESCE(acquiring_fee, 0)) AS acquiring_total,
            SUM(COALESCE(penalty, 0)) + SUM(CASE WHEN supplier_oper_name = 'Удержание' AND bonus_type_name NOT LIKE 'Списание за отзыв%%' AND bonus_type_name <> 'Оказание услуг «WB Продвижение»' THEN COALESCE(deduction, 0) ELSE 0 END) + SUM(CASE WHEN supplier_oper_name = 'Удержание' AND bonus_type_name LIKE 'Списание за отзыв%%' THEN COALESCE(deduction, 0) ELSE 0 END) AS other_total,
            SUM(CASE WHEN doc_type_name = 'Продажа' AND supplier_oper_name = 'Продажа' THEN retail_amount ELSE 0 END) 
            - SUM(CASE WHEN doc_type_name = 'Возврат' THEN retail_amount ELSE 0 END) AS op_after_spp
        ")
        ->where('cabinet', $shopId)
        ->where('date_from', $dateFrom);

        if (!empty($nmIds)) {
            $query->whereIn('nm_id', $nmIds);
        }
        
        return $query->groupBy('date_from', 'nm_id')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [
                            $item->nm_id => [
                                'commission_total' => (float)$item->commission_total,
                                'logistics_total' => (float)$item->logistics_total,
                                'storage_total' => (float)$item->storage_total,
                                'acquiring_total' => (float)$item->acquiring_total,
                                'other_total' => (float)$item->other_total,
                                'op_after_spp' => (float)$item->op_after_spp,
                            ]
                        ];
                    })
                    ->toArray();
    }
}