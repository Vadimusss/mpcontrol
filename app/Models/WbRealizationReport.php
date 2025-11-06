<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WbRealizationReport extends Model
{
    protected $connection = 'ozon_api';
    protected $table = 'wb_realization_report';
    
    public static function getExpenseData($dateFrom, $nmIds = [])
    {
        $query = self::selectRaw("
            date_from, 
            nm_id,
            SUM(COALESCE(ppvz_sales_commission, 0) + COALESCE(ppvz_vw, 0) + COALESCE(ppvz_vw_nds, 0)) AS commission_total,
            SUM(COALESCE(delivery_rub, 0) + COALESCE(rebill_logistic_cost, 0) + COALESCE(ppvz_reward, 0)) AS logistics_total,
            SUM(COALESCE(storage_fee, 0)) AS storage_total,
            SUM(COALESCE(acquiring_fee, 0)) AS acquiring_total,
            SUM(COALESCE(deduction, 0) + COALESCE(acceptance, 0) + COALESCE(cashback_amount, 0) + COALESCE(cashback_discount, 0) + COALESCE(cashback_commission_change, 0)) AS other_total
        ")
        ->where('date_from', $dateFrom)
        ->where('doc_type_name', 'Продажа')
        ->where('supplier_oper_name', 'Продажа');

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
                            ]
                        ];
                    })
                    ->toArray();
    }
}
