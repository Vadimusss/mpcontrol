<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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
            return self::processInBatches($query, $nmIds);
        } else {
            return self::processWithChunks($query);
        }
    }
    
    protected static function processWithChunks($query, $chunkSize = 500)
    {
        $results = [];
        
        $query->groupBy('date_from', 'nm_id')
              ->orderBy('nm_id')
              ->chunk($chunkSize, function ($items) use (&$results) {
                  foreach ($items as $item) {
                      $results[$item->nm_id] = [
                          'commission_total' => (float)$item->commission_total,
                          'logistics_total' => (float)$item->logistics_total,
                          'storage_total' => (float)$item->storage_total,
                          'acquiring_total' => (float)$item->acquiring_total,
                          'other_total' => (float)$item->other_total,
                          'op_after_spp' => (float)$item->op_after_spp,
                      ];
                  }
              });
        
        return $results;
    }
    
    protected static function processInBatches($query, $nmIds, $batchSize = 500)
    {
        $results = [];
        
        $batches = array_chunk($nmIds, $batchSize);
        
        foreach ($batches as $batchNmIds) {
            $batchQuery = clone $query;
            $batchResults = $batchQuery->whereIn('nm_id', $batchNmIds)
                                      ->groupBy('date_from', 'nm_id')
                                      ->get();
            
            foreach ($batchResults as $item) {
                $results[$item->nm_id] = [
                    'commission_total' => (float)$item->commission_total,
                    'logistics_total' => (float)$item->logistics_total,
                    'storage_total' => (float)$item->storage_total,
                    'acquiring_total' => (float)$item->acquiring_total,
                    'other_total' => (float)$item->other_total,
                    'op_after_spp' => (float)$item->op_after_spp,
                ];
            }
            
            if (count($batches) > 1) {
                usleep(100000);
            }
        }
        
        return $results;
    }
}