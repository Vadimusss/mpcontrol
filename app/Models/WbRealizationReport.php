<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

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
            DATE(order_dt) as date_from,
            nm_id,
            -- Количество заказов (продажи с quantity=1)
            SUM(CASE 
                WHEN LOWER(doc_type_name) NOT LIKE '%возврат%' 
                AND quantity = 1 
                THEN 1 
                ELSE 0 
            END) as orders_count,
            -- Объем продаж после СПП (retail_amount с учетом знака)
            ROUND(SUM(CASE 
                WHEN LOWER(doc_type_name) NOT LIKE '%возврат%' 
                THEN retail_amount 
                ELSE -retail_amount 
            END), 2) as op_after_spp,
            -- Логистика (delivery_rub с учетом знака)
            ROUND(SUM(CASE 
                WHEN LOWER(doc_type_name) NOT LIKE '%возврат%' 
                THEN COALESCE(delivery_rub, 0)
                ELSE -COALESCE(delivery_rub, 0)
            END), 2) as logistics_total,
            -- Сумма к перечислению продавцу (ppvz_for_pay с учетом знака)
            ROUND(SUM(CASE 
                WHEN LOWER(doc_type_name) NOT LIKE '%возврат%' 
                THEN ppvz_for_pay
                ELSE -ppvz_for_pay
            END), 2) as amount_to_transfer
        ")
            ->where('cabinet', $shopId)
            ->whereDate('order_dt', '=', $dateFrom)
            ->where('quantity', '!=', 2);

        if (!empty($nmIds)) {
            $query->whereIn('nm_id', $nmIds);
        }

        return $query->groupBy(DB::raw('DATE(order_dt)'), 'nm_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->nm_id => [
                        'commission_total' => 0,
                        'logistics_total' => (float) $item->logistics_total,
                        'storage_total' => 0,
                        'acquiring_total' => 0,
                        'other_total' => 0,
                        'op_after_spp' => (float) $item->op_after_spp,
                        'orders_count' => (int) $item->orders_count,
                        'amount_to_transfer' => (float)$item->amount_to_transfer,
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
