<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WbRealizationReport extends Model
{
    protected $table = 'wb_realization_reports';

    protected $fillable = [
        'cabinet',
        'inserted_at',
        'realizationreport_id',
        'date_from',
        'date_to',
        'create_dt',
        'currency_name',
        'suppliercontract_code',
        'rrd_id',
        'gi_id',
        'dlv_prc',
        'fix_tariff_date_from',
        'fix_tariff_date_to',
        'subject_name',
        'nm_id',
        'brand_name',
        'sa_name',
        'ts_name',
        'barcode',
        'doc_type_name',
        'quantity',
        'retail_price',
        'retail_amount',
        'sale_percent',
        'commission_percent',
        'office_name',
        'supplier_oper_name',
        'order_dt',
        'sale_dt',
        'rr_dt',
        'shk_id',
        'retail_price_withdisc_rub',
        'delivery_amount',
        'return_amount',
        'delivery_rub',
        'gi_box_type_name',
        'product_discount_for_report',
        'supplier_promo',
        'ppvz_spp_prc',
        'ppvz_kvw_prc_base',
        'ppvz_kvw_prc',
        'sup_rating_prc_up',
        'is_kgvp_v2',
        'ppvz_sales_commission',
        'ppvz_for_pay',
        'ppvz_reward',
        'acquiring_fee',
        'acquiring_percent',
        'payment_processing',
        'acquiring_bank',
        'ppvz_vw',
        'ppvz_vw_nds',
        'ppvz_office_name',
        'ppvz_office_id',
        'ppvz_supplier_id',
        'ppvz_supplier_name',
        'ppvz_inn',
        'declaration_number',
        'bonus_type_name',
        'sticker_id',
        'site_country',
        'srv_dbs',
        'penalty',
        'additional_payment',
        'rebill_logistic_cost',
        'rebill_logistic_org',
        'storage_fee',
        'deduction',
        'acceptance',
        'assembly_id',
        'kiz',
        'srid',
        'report_type',
        'is_legal_entity',
        'trbx_id',
        'installment_cofinancing_amount',
        'wibes_wb_discount_percent',
        'cashback_amount',
        'cashback_discount',
        'cashback_commission_change',
        'order_uid',
        'payment_schedule',
    ];

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

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'cabinet');
    }
}
